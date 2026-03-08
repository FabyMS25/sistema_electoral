<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Institution;
use App\Models\VotingTable;
use App\Models\UserAssignment;
use App\Models\ElectionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function requirePermission(string $permission): void
    {
        if (! Auth::user()->allPermissions->contains('name', $permission)) {
            abort(403, "Acceso denegado: se requiere el permiso '{$permission}'.");
        }
    }
    private function resolveAvatar(array $roleNames, string $gender = 'm'): string
    {
        $suffix = ($gender === 'w') ? 'w' : 'm';
        if (empty($roleNames)) {
            return "avatar-op-{$suffix}.png";
        }
        $tier = 'op';
        foreach ($roleNames as $name) {
            $nameLower = strtolower($name);
            if (str_contains($nameLower, 'admin') || str_contains($nameLower, 'superadmin')) {
                $tier = 'admin';
                break;
            }
            if (in_array($tier, ['op', 'delegado']) &&
                (str_contains($nameLower, 'coordinador') ||
                 str_contains($nameLower, 'manager')     ||
                 str_contains($nameLower, 'fiscal')      ||
                 str_contains($nameLower, 'notario'))) {
                $tier = 'manager';
            }
            if ($tier === 'op' &&
                (str_contains($nameLower, 'delegado')   ||
                 str_contains($nameLower, 'presidente') ||
                 str_contains($nameLower, 'secretario') ||
                 str_contains($nameLower, 'vocal'))) {
                $tier = 'delegado';
            }
        }
        $avatarTier = ($tier === 'delegado') ? 'manager' : $tier;
        return "avatar-{$avatarTier}-{$suffix}.png";
    }
    public function checkEmail(Request $request)
    {
        $exists = User::where('email', $request->email)
            ->when($request->filled('user_id'), fn ($q) => $q->where('id', '!=', $request->user_id))
            ->exists();
        return response()->json(['exists' => $exists]);
    }

    public function index(Request $request)
    {
        $this->requirePermission('view_users');
        $query = User::with([
            'roles',
            'createdBy',
            'assignments' => fn ($q) => $q->where('status', 'activo')->with(['institution', 'votingTable']),
        ]);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('name',      'ilike', "%{$search}%")
                ->orWhere('last_name', 'ilike', "%{$search}%")
                ->orWhere('email',   'ilike', "%{$search}%")
                ->orWhere('id_card', 'ilike', "%{$search}%")
            );
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        if ($request->filled('delegate_type')) {
            $query->whereHas('assignments', fn ($q) => $q
                ->where('delegate_type', $request->delegate_type)
                ->where('status', 'activo')
            );
        }
        $allowedSorts = ['name', 'email', 'created_at', 'last_login_at', 'is_active'];
        $sort  = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'created_at';
        $order = $request->get('order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $order);
        $users = $query->paginate(15)->withQueryString();
        $stats = [
            'total'        => User::count(),
            'active'       => User::where('is_active', true)->count(),
            'inactive'     => User::where('is_active', false)->count(),
            'online_today' => User::whereDate('last_login_at', today())->count(),
            'delegates'    => UserAssignment::where('status', 'activo')->count(),
        ];
        $roles         = Role::all();
        $delegateTypes = UserAssignment::getDelegateTypes();
        return view('users.index', compact('users', 'stats', 'roles', 'delegateTypes'));
    }

    public function create()
    {
        $this->requirePermission('create_users');
        $roles         = Role::with('permissions')->get();
        $permissions   = Permission::all()->groupBy('group');
        $electionTypes = ElectionType::where('active', true)->get();
        return view('users.create', compact('roles', 'permissions', 'electionTypes'));
    }

    public function store(Request $request)
    {
        $this->requirePermission('create_users');
        $request->validate([
            'name'          => 'required|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'id_card'       => 'nullable|string|unique:users,id_card',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'password'      => 'required|string|min:8|confirmed',
            'gender'        => 'nullable|in:m,w',
            'roles'         => 'nullable|array',
            'roles.*'       => 'exists:roles,id',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        DB::transaction(function () use ($request) {
            $roleNames = [];
            if ($request->filled('roles')) {
                $roleNames = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
            }
            $avatar = $this->resolveAvatar($roleNames, $request->input('gender', 'm'));
            $user = User::create([
                'name'       => $request->name,
                'last_name'  => $request->last_name,
                'id_card'    => $request->id_card,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'address'    => $request->address,
                'password'   => Hash::make($request->password),
                'is_active'  => true,
                'avatar'     => $avatar,
                'created_by' => Auth::id(),
            ]);
            if ($request->filled('roles')) {
                $pivotData = [];
                foreach ($request->roles as $roleId) {
                    $pivotData[$roleId] = [
                        'scope'      => 'global',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $user->roles()->attach($pivotData);
            }
            if ($request->filled('permissions')) {
                $user->permissions()->attach($request->permissions);
            }
        });
        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user)
    {
        $this->requirePermission('view_users');
        $user->load([
            'roles' => fn ($q) => $q->withPivot(['scope', 'institution_id', 'voting_table_id', 'election_type_id']),
            'permissions',
            'createdBy',
            'updatedBy',
            'assignments' => fn ($q) => $q
                ->with(['institution', 'votingTable', 'assignedBy'])
                ->orderByDesc('created_at'),
        ]);

        $activeElection = ElectionType::where('active', true)->first();

        return view('users.show', compact('user', 'activeElection'));
    }

    public function edit(User $user)
    {
        $this->requirePermission('edit_users');
        $roles         = Role::with('permissions')->get();
        $permissions   = Permission::all()->groupBy('group');
        $electionTypes = ElectionType::where('active', true)->get();
        $userRoles = $user->roles->mapWithKeys(fn ($role) => [
            $role->id => [
                'scope'            => $role->pivot->scope,
                'institution_id'   => $role->pivot->institution_id,
                'voting_table_id'  => $role->pivot->voting_table_id,
                'election_type_id' => $role->pivot->election_type_id,
            ],
        ])->toArray();
        $userPermissions = $user->permissions->pluck('id')->toArray();
        return view('users.edit', compact(
            'user', 'roles', 'permissions', 'electionTypes',
            'userRoles', 'userPermissions'
        ));
    }

    public function update(Request $request, User $user)
    {
        $this->requirePermission('edit_users');
        $request->validate([
            'name'          => 'required|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'id_card'       => 'nullable|string|unique:users,id_card,'.$user->id,
            'email'         => 'required|email|unique:users,email,'.$user->id,
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'password'      => 'nullable|string|min:8|confirmed',
            'is_active'     => 'boolean',
            'gender'        => 'nullable|in:m,w',
            'roles'         => 'nullable|array',
            'roles.*'       => 'exists:roles,id',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        DB::transaction(function () use ($request, $user) {
            $roleNames = [];
            if ($request->filled('roles')) {
                $roleNames = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
            }
            $avatar = $this->resolveAvatar($roleNames, $request->input('gender', 'm'));
            $data = [
                'name'       => $request->name,
                'last_name'  => $request->last_name,
                'id_card'    => $request->id_card,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'address'    => $request->address,
                'is_active'  => $request->boolean('is_active', true),
                'avatar'     => $avatar,
                'updated_by' => Auth::id(),
            ];
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
            $user->update($data);
            if ($request->has('roles')) {
                $pivotData = [];
                foreach ($request->input('roles', []) as $roleId) {
                    $existing = $user->roles()->where('role_id', $roleId)
                                     ->withPivot(['scope', 'institution_id', 'voting_table_id', 'election_type_id'])
                                     ->first();
                    $pivotData[$roleId] = [
                        'scope'            => $existing?->pivot?->scope            ?? 'global',
                        'institution_id'   => $existing?->pivot?->institution_id   ?? null,
                        'voting_table_id'  => $existing?->pivot?->voting_table_id  ?? null,
                        'election_type_id' => $existing?->pivot?->election_type_id ?? null,
                        'updated_at'       => now(),
                    ];
                }
                $user->roles()->sync($pivotData);
            }
            if ($request->has('permissions')) {
                $user->permissions()->sync($request->input('permissions', []));
            }
        });
        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        $this->requirePermission('delete_users');
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes desactivarte a ti mismo.');
        }
        $user->update(['is_active' => false, 'updated_by' => Auth::id()]);
        return redirect()->route('users.index')
            ->with('success', 'Usuario desactivado exitosamente.');
    }

    public function activate(User $user)
    {
        $this->requirePermission('activate_users');
        $user->update(['is_active' => true, 'updated_by' => Auth::id()]);
        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario activado exitosamente.');
    }

    public function assignRolesForm(User $user)
    {
        $this->requirePermission('assign_roles');

        $roles         = Role::with('permissions')->get();
        $institutions  = Institution::where('status', 'activo')
            ->with('municipality:id,name')
            ->orderBy('name')
            ->get();
        $votingTables  = VotingTable::with('institution:id,name')
            ->orderBy('institution_id')
            ->orderBy('number')
            ->get();
        $currentRoles = $user->roles()
            ->withPivot(['scope', 'institution_id', 'voting_table_id', 'election_type_id'])
            ->get();
        $permissions     = Permission::all()->groupBy('group');
        $userPermissions = $user->permissions()->pluck('permissions.id')->toArray();
        $electionTypes   = ElectionType::where('active', true)->get();
        $user->load([
            'assignments' => fn ($q) => $q
                ->where('status', 'activo')
                ->with(['institution', 'votingTable.institution']),
        ]);
        return view('users.assign-roles', compact(
            'user', 'roles', 'institutions', 'votingTables',
            'currentRoles', 'permissions', 'userPermissions', 'electionTypes'
        ));
    }

    public function assignRoles(Request $request, User $user)
    {
        $this->requirePermission('assign_roles');

        $request->validate([
            'roles'                        => 'nullable|array',
            'roles.*.role_id'              => 'required|exists:roles,id',
            'roles.*.scope'                => 'required|in:global,recinto,mesa',
            'roles.*.institution_id'       => 'required_if:roles.*.scope,recinto|nullable|exists:institutions,id',
            'roles.*.voting_table_id'      => 'required_if:roles.*.scope,mesa|nullable|exists:voting_tables,id',
            'roles.*.election_type_id'     => 'nullable|exists:election_types,id',
        ]);

        DB::transaction(function () use ($request, $user) {
            $user->roles()->detach();

            foreach ($request->input('roles', []) as $role) {
                $scope         = $role['scope'];
                $institutionId = $scope === 'recinto' ? ($role['institution_id']  ?? null) : null;
                $votingTableId = $scope === 'mesa'    ? ($role['voting_table_id'] ?? null) : null;
                $user->roles()->attach($role['role_id'], [
                    'scope'            => $scope,
                    'institution_id'   => $institutionId,
                    'voting_table_id'  => $votingTableId,
                    'election_type_id' => $role['election_type_id'] ?? null,
                    'scope_settings'   => json_encode(['assigned_at' => now()->toDateTimeString()]),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        });

        return redirect()->route('users.assign-roles.form', $user)
            ->with('success', 'Roles y delegaciones asignados exitosamente.');
    }

    public function permissionsForm(User $user)
    {
        $this->requirePermission('assign_permissions');
        $permissions        = Permission::all()->groupBy('group');
        $currentPermissions = $user->permissions()
            ->withPivot(['scope', 'scope_id', 'scope_type'])
            ->get();
        return view('users.permissions', compact('user', 'permissions', 'currentPermissions'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        $this->requirePermission('assign_permissions');
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $user->permissions()->sync($request->input('permissions', []));
        return redirect()->route('users.show', $user)
            ->with('success', 'Permisos actualizados exitosamente.');
    }

    public function assignInstitutionForm(User $user)
    {
        return redirect()->route('users.assign-roles.form', $user);
    }

    public function assignInstitution(Request $request, User $user)
    {
        $this->requirePermission('assign_delegates');
        $request->validate([
            'institution_id'    => 'required|exists:institutions,id',
            'delegate_type'     => 'required|in:delegado_general,tecnico,observador',
            'assignment_date'   => 'nullable|date',
            'expiration_date'   => 'nullable|date|after:assignment_date',
            'credential_number' => 'nullable|string|max:50',
            'observations'      => 'nullable|string|max:500',
        ]);
        $exists = UserAssignment::where('user_id', $user->id)
            ->where('institution_id', $request->institution_id)
            ->whereNull('voting_table_id')
            ->where('status', 'activo')
            ->exists();

        if ($exists) {
            return back()->with('error', 'El usuario ya tiene una asignación activa en este recinto.');
        }
        UserAssignment::create([
            'user_id'           => $user->id,
            'institution_id'    => $request->institution_id,
            'delegate_type'     => $request->delegate_type,
            'assignment_date'   => $request->assignment_date ?? now(),
            'expiration_date'   => $request->expiration_date,
            'credential_number' => $request->credential_number,
            'status'            => 'activo',
            'assigned_by'       => Auth::id(),
            'observations'      => $request->observations,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Asignación de recinto guardada exitosamente.');
    }

    public function assignTableForm(User $user)
    {
        return redirect()->route('users.assign-roles.form', $user);
    }

    public function assignTable(Request $request, User $user)
    {
        $this->requirePermission('assign_delegates');
        $request->validate([
            'voting_table_id'   => 'required|exists:voting_tables,id',
            'institution_id'    => 'required|exists:institutions,id',
            'delegate_type'     => 'required|in:delegado_mesa,presidente,secretario,vocal',
            'assignment_date'   => 'nullable|date',
            'expiration_date'   => 'nullable|date|after:assignment_date',
            'credential_number' => 'nullable|string|max:50',
            'observations'      => 'nullable|string|max:500',
        ]);
        $table = VotingTable::findOrFail($request->voting_table_id);
        if ((int) $table->institution_id !== (int) $request->institution_id) {
            return back()->with('error', 'La mesa seleccionada no pertenece al recinto indicado.');
        }
        $conflict = UserAssignment::where('voting_table_id', $request->voting_table_id)
            ->where('status', 'activo')
            ->where('user_id', '!=', $user->id)
            ->exists();
        if ($conflict) {
            return back()->with('error', 'Esta mesa ya tiene otro delegado asignado.');
        }
        $existing = UserAssignment::where('user_id', $user->id)
            ->where('voting_table_id', $request->voting_table_id)
            ->where('status', 'activo')
            ->first();
        if ($existing) {
            $existing->update([
                'delegate_type'     => $request->delegate_type,
                'expiration_date'   => $request->expiration_date,
                'credential_number' => $request->credential_number,
                'observations'      => $request->observations,
                'assigned_by'       => Auth::id(),
            ]);
            $message = 'Asignación de mesa actualizada exitosamente.';
        } else {
            UserAssignment::create([
                'user_id'           => $user->id,
                'institution_id'    => $table->institution_id,
                'voting_table_id'   => $request->voting_table_id,
                'delegate_type'     => $request->delegate_type,
                'assignment_date'   => $request->assignment_date ?? now(),
                'expiration_date'   => $request->expiration_date,
                'credential_number' => $request->credential_number,
                'status'            => 'activo',
                'assigned_by'       => Auth::id(),
                'observations'      => $request->observations,
            ]);
            $message = 'Asignación de mesa guardada exitosamente.';
        }

        return redirect()->route('users.show', $user)
            ->with('success', $message);
    }

    public function removeAssignment(User $user, UserAssignment $assignment)
    {
        $this->requirePermission('assign_delegates');

        if ($assignment->user_id !== $user->id) {
            return back()->with('error', 'La asignación no pertenece a este usuario.');
        }

        $assignment->update([
            'status'          => UserAssignment::STATUS_FINALIZADO,
            'expiration_date' => now(),
        ]);

        return back()->with('success', 'Asignación removida exitosamente.');
    }

    private const IMPORT_HEADERS = [
        'nombre', 'apellido', 'ci', 'email', 'telefono',
        'rol',            // display_name or name of Role (NOT id)
        'tipo_delegado',  // e.g. delegado_general, presidente, observador
        'recinto_codigo', // Institution.code  (optional)
        'mesa_codigo',    // VotingTable.oep_code or internal_code (optional)
        'tipo_eleccion',  // ElectionType.name or short_name (optional)
        'contrasena',     // plain password (min 8); auto-generated if empty
    ];
    public function importForm()
    {
        $this->requirePermission('create_users');
        $roles         = Role::all();
        $delegateTypes = UserAssignment::getDelegateTypes();
        $institutions  = Institution::where('status', 'activo')->orderBy('name')->get();
        $electionTypes = ElectionType::where('active', true)->get();
        return view('users.import', compact('roles', 'delegateTypes', 'institutions', 'electionTypes'));
    }

    public function importTemplate()
    {
        $this->requirePermission('create_users');
        $roles         = Role::all();
        $delegateTypes = UserAssignment::getDelegateTypes();
        $institutions  = Institution::where('status', 'activo')->orderBy('name')->get();
        $electionTypes = ElectionType::where('active', true)->get();
        $lines   = [];
        $lines[] = implode(',', self::IMPORT_HEADERS);
        $exampleRole   = $roles->first()?->display_name ?? $roles->first()?->name ?? 'Administrador';
        $exampleDt     = array_key_first(UserAssignment::getDelegateTypes());
        $exampleInst   = $institutions->first()?->code ?? 'REC-001';
        $exampleEt     = $electionTypes->first()?->name ?? 'Elecciones 2025';
        $lines[] = implode(',', [
            'Juan', 'Pérez', '12345678', 'juan.perez@example.com', '70000001',
            $exampleRole, $exampleDt, $exampleInst, '', $exampleEt, '',
        ]);
        $lines[] = '';
        $lines[] = '--- REFERENCIA: Valores válidos para cada columna ---';
        $lines[] = '';
        $lines[] = '=== ROL (columna: rol) ===';
        $lines[] = 'Nombre en sistema,Nombre para importar';
        foreach ($roles as $r) {
            $lines[] = $r->name . ',' . ($r->display_name ?? $r->name);
        }

        $lines[] = '';
        $lines[] = '=== TIPO_DELEGADO (columna: tipo_delegado) ===';
        $lines[] = 'Valor a usar,Descripción';
        foreach (UserAssignment::getDelegateTypes() as $val => $label) {
            $lines[] = $val . ',' . $label;
        }
        $lines[] = '';
        $lines[] = '=== RECINTO_CODIGO (columna: recinto_codigo) ===';
        $lines[] = 'Código,Nombre recinto';
        foreach ($institutions as $inst) {
            $lines[] = $inst->code . ',' . str_replace(',', ' ', $inst->name);
        }
        $lines[] = '';
        $lines[] = '=== TIPO_ELECCION (columna: tipo_eleccion) ===';
        $lines[] = 'Nombre,Código corto';
        foreach ($electionTypes as $et) {
            $lines[] = str_replace(',', ' ', $et->name) . ',' . ($et->short_name ?? '');
        }
        $csv = implode("
", $lines);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="plantilla_usuarios.csv"',
        ]);
    }

    public function importPreview(Request $request)
    {
        $this->requirePermission('create_users');

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file  = $request->file('file');
        $rows  = [];
        $errors = [];
        $headers = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $rowIndex = 0;
            while (($line = fgetcsv($handle)) !== false) {
                if (empty(array_filter($line)) || Str::startsWith($line[0] ?? '', ['---', '==='])) {
                    continue;
                }
                if ($rowIndex === 0) {
                    $headers = array_map('trim', $line);
                    $rowIndex++;
                    continue;
                }
                $row = array_combine($headers, array_pad($line, count($headers), ''));
                $rows[] = $row;
                $rowIndex++;
            }
            fclose($handle);
        }
        $roleMap       = Role::all()->flatMap(fn ($r) => [
            strtolower($r->name)         => $r,
            strtolower($r->display_name ?? '') => $r,
        ])->filter();
        $institutionMap = Institution::where('status', 'activo')->get()
            ->keyBy(fn ($i) => strtolower($i->code));
        $electionMap   = ElectionType::where('active', true)->get()->flatMap(fn ($e) => [
            strtolower($e->name)             => $e,
            strtolower($e->short_name ?? '') => $e,
        ])->filter();
        $validDelegates = array_keys(UserAssignment::getDelegateTypes());
        $existingEmails = User::pluck('email')->map('strtolower')->toArray();
        $existingCIs    = User::whereNotNull('id_card')->pluck('id_card')->toArray();

        $preview = [];

        foreach ($rows as $i => $row) {
            $rowNum  = $i + 2;
            $rowErrors = [];
            $warnings  = [];

            $nombre  = trim($row['nombre']   ?? '');
            $apellido = trim($row['apellido'] ?? '');
            $email   = strtolower(trim($row['email']    ?? ''));
            $ci      = trim($row['ci']        ?? '');
            $rolStr  = strtolower(trim($row['rol']      ?? ''));
            $dtStr   = strtolower(trim($row['tipo_delegado'] ?? ''));
            $instCod = strtolower(trim($row['recinto_codigo'] ?? ''));
            $etStr   = strtolower(trim($row['tipo_eleccion']  ?? ''));

            if (empty($nombre))  $rowErrors[] = 'Nombre requerido';
            if (empty($email))   $rowErrors[] = 'Email requerido';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
                $rowErrors[] = 'Email inválido';
            }
            if (in_array($email, $existingEmails)) {
                $rowErrors[] = 'Email ya registrado en el sistema';
            }
            if (!empty($ci) && in_array($ci, $existingCIs)) {
                $warnings[] = 'CI ya existe — será ignorado si duplicado';
            }
            $role = $roleMap->get($rolStr);
            if (!empty($rolStr) && !$role) {
                $rowErrors[] = "Rol '{$row['rol']}' no encontrado";
            }
            if (!empty($dtStr) && !in_array($dtStr, $validDelegates)) {
                $rowErrors[] = "tipo_delegado '{$row['tipo_delegado']}' no válido";
            }
            $institution = !empty($instCod) ? $institutionMap->get($instCod) : null;
            if (!empty($instCod) && !$institution) {
                $rowErrors[] = "Recinto '{$row['recinto_codigo']}' no encontrado";
            }
            $electionType = !empty($etStr) ? $electionMap->get($etStr) : null;
            if (!empty($etStr) && !$electionType) {
                $warnings[] = "Tipo elección '{$row['tipo_eleccion']}' no encontrado — se omitirá";
            }

            $preview[] = [
                'row'          => $rowNum,
                'nombre'       => $nombre,
                'apellido'     => $apellido,
                'email'        => $email,
                'ci'           => $ci,
                'telefono'     => $row['telefono'] ?? '',
                'rol'          => $role?->display_name ?? $row['rol'] ?? '',
                'role_id'      => $role?->id,
                'tipo_delegado'=> $row['tipo_delegado'] ?? '',
                'recinto'      => $institution?->name ?? $row['recinto_codigo'] ?? '',
                'institution_id' => $institution?->id,
                'tipo_eleccion'=> $electionType?->name ?? $row['tipo_eleccion'] ?? '',
                'election_type_id' => $electionType?->id,
                'errors'       => $rowErrors,
                'warnings'     => $warnings,
                'status'       => empty($rowErrors) ? 'ok' : 'error',
            ];
        }
        session(['import_preview' => $preview]);
        $roles         = Role::all();
        $delegateTypes = UserAssignment::getDelegateTypes();
        $institutions  = Institution::where('status', 'activo')->orderBy('name')->get();
        $electionTypes = ElectionType::where('active', true)->get();
        return view('users.import-preview', compact(
            'preview', 'roles', 'delegateTypes', 'institutions', 'electionTypes'
        ));
    }

    public function importExecute(Request $request)
    {
        $this->requirePermission('create_users');

        $preview = session('import_preview', []);
        if (empty($preview)) {
            return redirect()->route('users.import.form')
                ->with('error', 'Sesión de importación expirada. Suba el archivo nuevamente.');
        }
        $imported = 0;
        $skipped  = [];
        $confirmedRows = $request->input('confirmed_rows', []);
        DB::transaction(function () use ($preview, $confirmedRows, &$imported, &$skipped) {
            foreach ($preview as $row) {
                if ($row['status'] !== 'ok') { continue; }
                if (!in_array($row['row'], $confirmedRows)) { continue; }
                if (User::where('email', $row['email'])->exists()) {
                    $skipped[] = $row['email'] . ' (email duplicado)';
                    continue;
                }
                $password = !empty($row['contrasena'] ?? '') ? $row['contrasena'] : Str::random(10);

                $roleNames = $row['role_id'] ? [Role::find($row['role_id'])?->name ?? ''] : [];
                $avatar    = $this->resolveAvatar($roleNames);

                $user = User::create([
                    'name'       => $row['nombre'],
                    'last_name'  => $row['apellido'],
                    'id_card'    => $row['ci']       ?: null,
                    'email'      => $row['email'],
                    'phone'      => $row['telefono'] ?: null,
                    'password'   => Hash::make($password),
                    'avatar'     => $avatar,
                    'is_active'  => true,
                    'created_by' => Auth::id(),
                ]);

                if ($row['role_id']) {
                    $user->roles()->attach($row['role_id'], [
                        'scope'      => 'global',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (!empty($row['tipo_delegado']) && ($row['institution_id'] ?? null)) {
                    UserAssignment::create([
                        'user_id'          => $user->id,
                        'institution_id'   => $row['institution_id'],
                        'election_type_id' => $row['election_type_id'] ?? null,
                        'delegate_type'    => $row['tipo_delegado'],
                        'assignment_date'  => now(),
                        'status'           => 'activo',
                        'assigned_by'      => Auth::id(),
                    ]);
                }

                $imported++;
            }
        });

        session()->forget('import_preview');

        $message = "Se importaron {$imported} usuario(s) exitosamente.";
        if (!empty($skipped)) {
            $message .= ' Omitidos: ' . implode(', ', $skipped) . '.';
        }

        return redirect()->route('users.index')->with('success', $message);
    }

}
