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

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_users')->only(['index', 'show']);
        $this->middleware('permission:create_users')->only(['create', 'store']);
        $this->middleware('permission:edit_users')->only(['edit', 'update']);
        $this->middleware('permission:delete_users')->only(['destroy']);
        $this->middleware('permission:activate_users')->only(['activate']);
        $this->middleware('permission:assign_roles')->only(['assignRolesForm', 'assignRoles']);
        $this->middleware('permission:assign_permissions')->only(['permissionsForm', 'updatePermissions']);
        $this->middleware('permission:assign_delegates')->only([
            'assignInstitutionForm', 'assignInstitution',
            'assignTableForm', 'assignTable',
            'removeAssignment'
        ]);
    }

    public function checkEmail(Request $request)
    {
        $exists = User::where('email', $request->email)
            ->when($request->user_id, function($q, $userId) {
                $q->where('id', '!=', $userId);
            })
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function index(Request $request)
    {
        $query = User::with(['roles', 'createdBy', 'assignments' => function($q) {
            $q->where('status', 'activo')->with('institution', 'votingTable');
        }]);
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%")
                  ->orWhere('last_name', 'ilike', "%{$request->search}%")
                  ->orWhere('email', 'ilike', "%{$request->search}%")
                  ->orWhere('id_card', 'ilike', "%{$request->search}%");
            });
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('delegate_type')) {
            $query->whereHas('assignments', function($q) use ($request) {
                $q->where('delegate_type', $request->delegate_type)
                  ->where('status', 'activo');
            });
        }
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);
        $users = $query->paginate(15)->withQueryString();
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'online_today' => User::whereDate('last_login_at', today())->count(),
            'delegates' => UserAssignment::where('status', 'activo')->count(),
        ];
        $roles = Role::all();
        $delegateTypes = UserAssignment::getDelegateTypes();
        return view('users.index', compact('users', 'stats', 'roles', 'delegateTypes'));
    }

    public function create()
    {
        $this->authorize('create_users');
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('group');
        $electionTypes = ElectionType::where('active', true)->get();
        return view('users.create', compact('roles', 'permissions', 'electionTypes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create_users');
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'id_card' => 'nullable|string|unique:users,id_card',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'id_card' => $request->id_card,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);
            if ($request->has('roles')) {
                $roleData = [];
                foreach ($request->roles as $roleId) {
                    $roleData[$roleId] = [
                        'scope' => 'global',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $user->roles()->attach($roleData);
            }
            if ($request->has('permissions')) {
                $user->permissions()->attach($request->permissions);
            }
        });
        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Ver detalle del usuario
     */
    public function show(User $user)
    {
        $this->authorize('view_users');

        $user->load([
            'roles' => function($q) {
                $q->withPivot(['scope', 'institution_id', 'voting_table_id', 'election_type_id']);
            },
            'permissions',
            'createdBy',
            'updatedBy',
            'assignments' => function($q) {
                $q->with(['institution', 'votingTable', 'electionType', 'assignedBy'])
                  ->orderBy('created_at', 'desc');
            }
        ]);

        $activeElection = ElectionType::where('active', true)->first();

        return view('users.show', compact('user', 'activeElection'));
    }

    /**
     * Formulario de edición
     */
    public function edit(User $user)
    {
        $this->authorize('edit_users');

        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('group');
        $electionTypes = ElectionType::where('active', true)->get();

        $userRoles = $user->roles->mapWithKeys(function($role) {
            return [$role->id => [
                'scope' => $role->pivot->scope,
                'institution_id' => $role->pivot->institution_id,
                'voting_table_id' => $role->pivot->voting_table_id,
                'election_type_id' => $role->pivot->election_type_id,
            ]];
        })->toArray();

        $userPermissions = $user->permissions->pluck('id')->toArray();

        return view('users.edit', compact(
            'user', 'roles', 'permissions', 'electionTypes',
            'userRoles', 'userPermissions'
        ));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit_users');

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'id_card' => 'nullable|string|unique:users,id_card,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'last_name' => $request->last_name,
            'id_card' => $request->id_card,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => Auth::id(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Desactivar usuario
     */
    public function destroy(User $user)
    {
        $this->authorize('delete_users');

        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes desactivarte a ti mismo.');
        }

        $user->update([
            'is_active' => false,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario desactivado exitosamente.');
    }

    /**
     * Activar usuario
     */
    public function activate(User $user)
    {
        $this->authorize('activate_users');

        $user->update([
            'is_active' => true,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario activado exitosamente.');
    }

    /**
     * Formulario de asignación de roles
     */
    public function assignRolesForm(User $user)
    {
        $this->authorize('assign_roles');

        $roles = Role::with('permissions')->get();
        $electionTypes = ElectionType::where('active', true)->get();
        $institutions = Institution::where('status', 'activo')->orderBy('name')->get();
        $votingTables = VotingTable::with('institution')
            ->where('status', 'configurada')
            ->orderBy('institution_id')
            ->orderBy('number')
            ->get();

        $currentRoles = $user->roles()->withPivot(['scope', 'institution_id', 'voting_table_id', 'election_type_id'])->get();

        return view('users.assign-roles', compact('user', 'roles', 'electionTypes', 'institutions', 'votingTables', 'currentRoles'));
    }

    /**
     * Guardar asignación de roles
     */
    public function assignRoles(Request $request, User $user)
    {
        $this->authorize('assign_roles');

        $request->validate([
            'roles' => 'array',
            'roles.*.role_id' => 'required|exists:roles,id',
            'roles.*.scope' => 'required|in:global,institution,voting_table',
            'roles.*.institution_id' => 'required_if:roles.*.scope,institution|nullable|exists:institutions,id',
            'roles.*.voting_table_id' => 'required_if:roles.*.scope,voting_table|nullable|exists:voting_tables,id',
            'roles.*.election_type_id' => 'nullable|exists:election_types,id',
        ]);

        DB::transaction(function() use ($request, $user) {
            $user->roles()->detach();

            if ($request->has('roles')) {
                $roleData = [];
                foreach ($request->roles as $role) {
                    $roleData[$role['role_id']] = [
                        'scope' => $role['scope'],
                        'institution_id' => $role['institution_id'] ?? null,
                        'voting_table_id' => $role['voting_table_id'] ?? null,
                        'election_type_id' => $role['election_type_id'] ?? null,
                        'scope_settings' => json_encode(['assigned_at' => now()->toDateTimeString()]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $user->roles()->attach($roleData);
            }
        });

        return redirect()->route('users.show', $user)
            ->with('success', 'Roles asignados exitosamente.');
    }

    /**
     * Formulario de permisos directos
     */
    public function permissionsForm(User $user)
    {
        $this->authorize('assign_permissions');

        $permissions = Permission::all()->groupBy('group');
        $currentPermissions = $user->permissions()->withPivot(['scope', 'scope_id', 'scope_type'])->get();

        return view('users.permissions', compact('user', 'permissions', 'currentPermissions'));
    }

    /**
     * Actualizar permisos directos
     */
    public function updatePermissions(Request $request, User $user)
    {
        $this->authorize('assign_permissions');

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user->permissions()->sync($request->permissions ?? []);

        return redirect()->route('users.show', $user)
            ->with('success', 'Permisos actualizados exitosamente.');
    }

    /**
     * Formulario de asignación de institución (recinto)
     */
    public function assignInstitutionForm(User $user)
    {
        $this->authorize('assign_delegates');

        $electionTypes = ElectionType::where('active', true)->get();

        $institutions = Institution::where('status', 'activo')
            ->orderBy('name')
            ->get()
            ->groupBy(function($item) {
                return $item->municipality->name ?? 'Sin municipio';
            });

        $currentAssignments = $user->assignments()
            ->where('status', 'activo')
            ->whereNotNull('institution_id')
            ->whereNull('voting_table_id')
            ->with(['institution', 'electionType'])
            ->get();

        return view('users.assign-institution', compact('user', 'electionTypes', 'institutions', 'currentAssignments'));
    }

    /**
     * Guardar asignación de institución
     */
    public function assignInstitution(Request $request, User $user)
    {
        $this->authorize('assign_delegates');

        $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'election_type_id' => 'required|exists:election_types,id',
            'delegate_type' => 'required|in:delegado_general,tecnico,observador',
            'assignment_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:assignment_date',
            'credential_number' => 'nullable|string|max:50',
            'observations' => 'nullable|string|max:500',
        ]);
        $existing = UserAssignment::where('user_id', $user->id)
            ->where('institution_id', $request->institution_id)
            ->where('election_type_id', $request->election_type_id)
            ->where('status', 'activo')
            ->first();

        if ($existing) {
            return back()->with('error', 'El usuario ya tiene una asignación activa para esta institución.');
        }

        UserAssignment::create([
            'user_id' => $user->id,
            'institution_id' => $request->institution_id,
            'election_type_id' => $request->election_type_id,
            'delegate_type' => $request->delegate_type,
            'assignment_date' => $request->assignment_date ?? now(),
            'expiration_date' => $request->expiration_date,
            'credential_number' => $request->credential_number,
            'status' => 'activo',
            'assigned_by' => Auth::id(),
            'observations' => $request->observations,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Asignación de recinto guardada exitosamente.');
    }

    /**
     * Formulario de asignación de mesa
     */
    public function assignTableForm(User $user)
    {
        $this->authorize('assign_delegates');
        $electionTypes = ElectionType::where('active', true)->get();
        $assignedTableIds = UserAssignment::where('status', 'activo')
            ->whereNotNull('voting_table_id')
            ->pluck('voting_table_id')
            ->toArray();

        $votingTables = VotingTable::with('institution')
            ->where('status', 'configurada')
            ->whereNotIn('id', $assignedTableIds)
            ->orWhereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'activo');
            })
            ->orderBy('institution_id')
            ->orderBy('number')
            ->get()
            ->groupBy(function($item) {
                return $item->institution->name ?? 'Sin institución';
            });

        $currentAssignments = $user->assignments()
            ->where('status', 'activo')
            ->whereNotNull('voting_table_id')
            ->with(['votingTable.institution', 'electionType'])
            ->get();

        return view('users.assign-table', compact('user', 'electionTypes', 'votingTables', 'currentAssignments'));
    }

    /**
     * Guardar asignación de mesa
     */
    public function assignTable(Request $request, User $user)
    {
        $this->authorize('assign_delegates');

        $request->validate([
            'voting_table_id' => 'required|exists:voting_tables,id',
            'election_type_id' => 'required|exists:election_types,id',
            'delegate_type' => 'required|in:delegado_mesa,presidente,secretario,vocal',
            'assignment_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:assignment_date',
            'credential_number' => 'nullable|string|max:50',
            'observations' => 'nullable|string|max:500',
        ]);
        $existing = UserAssignment::where('voting_table_id', $request->voting_table_id)
            ->where('status', 'activo')
            ->first();
        if ($existing && $existing->user_id != $user->id) {
            return back()->with('error', 'Esta mesa ya tiene un delegado asignado.');
        }
        if ($existing && $existing->user_id == $user->id) {
            $existing->update([
                'delegate_type' => $request->delegate_type,
                'expiration_date' => $request->expiration_date,
                'credential_number' => $request->credential_number,
                'observations' => $request->observations,
                'assigned_by' => Auth::id(),
            ]);
            $message = 'Asignación actualizada exitosamente.';
        } else {
            UserAssignment::create([
                'user_id' => $user->id,
                'voting_table_id' => $request->voting_table_id,
                'election_type_id' => $request->election_type_id,
                'delegate_type' => $request->delegate_type,
                'assignment_date' => $request->assignment_date ?? now(),
                'expiration_date' => $request->expiration_date,
                'credential_number' => $request->credential_number,
                'status' => 'activo',
                'assigned_by' => Auth::id(),
                'observations' => $request->observations,
            ]);
            $message = 'Asignación de mesa guardada exitosamente.';
        }
        return redirect()->route('users.show', $user)
            ->with('success', $message);
    }

    /**
     * Remover asignación
     */
    public function removeAssignment(User $user, UserAssignment $assignment)
    {
        $this->authorize('assign_delegates');

        if ($assignment->user_id !== $user->id) {
            return back()->with('error', 'La asignación no pertenece a este usuario.');
        }

        $assignment->update([
            'status' => 'finalizado',
            'expiration_date' => now(),
        ]);

        return back()->with('success', 'Asignación removida exitosamente.');
    }
}
