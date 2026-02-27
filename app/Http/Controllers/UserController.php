<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Institution;
use App\Models\VotingTable;
use App\Models\TableDelegate;
use App\Models\RecintoDelegate;
use App\Models\Reviewer;
use App\Models\Modifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_users')->only(['index', 'show']);
        $this->middleware('permission:create_users')->only(['create', 'store']);
        $this->middleware('permission:edit_users')->only(['edit', 'update']);
        $this->middleware('permission:delete_users')->only(['destroy']);
        $this->middleware('permission:assign_roles')->only(['assignRoles', 'updateRoles']);
        $this->middleware('permission:assign_permissions')->only(['assignPermissions', 'updatePermissions']);
        $this->middleware('permission:assign_recinto_delegates')->only(['assignRecintoForm', 'assignRecinto']);
        $this->middleware('permission:assign_table_delegates')->only(['assignTableForm', 'assignTable']);
    }

    /**
     * Listado de usuarios con filtros
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'createdBy']);

        // Filtros
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

        // Ordenamiento
        $query->orderBy($request->get('sort', 'created_at'), $request->get('order', 'desc'));

        $users = $query->paginate(15)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'online_today' => User::whereDate('last_login_at', today())->count(),
        ];

        $roles = Role::all();

        return view('users.index', compact('users', 'stats', 'roles'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('group');
        
        return view('users.create', compact('roles', 'permissions'));
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
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

        // Asignar roles
        if ($request->has('roles')) {
            $user->roles()->attach($request->roles);
        }

        // Asignar permisos directos
        if ($request->has('permissions')) {
            $user->permissions()->attach($request->permissions);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Ver detalle del usuario
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions', 'createdBy', 'updatedBy']);
        
        // Cargar asignaciones activas
        $activeAssignments = [
            'recinto' => $user->recintoDelegations()
                ->with('institution')
                ->where('is_active', true)
                ->first(),
            'mesas' => $user->tableDelegations()
                ->with('votingTable')
                ->where('is_active', true)
                ->get(),
            'revisor' => $user->reviewerAssignments()
                ->with('assignable')
                ->where('is_active', true)
                ->get(),
            'modificador' => $user->modifierAssignments()
                ->with('assignable')
                ->where('is_active', true)
                ->get(),
        ];

        return view('users.show', compact('user', 'activeAssignments'));
    }

    /**
     * Formulario de edición
     */
    public function edit(User $user)
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('group');
        $userRoles = $user->roles->pluck('id')->toArray();
        $userPermissions = $user->permissions->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'permissions', 'userRoles', 'userPermissions'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'id_card' => 'nullable|string|unique:users,id_card,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
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

        // Sincronizar roles
        $user->roles()->sync($request->roles ?? []);

        // Sincronizar permisos directos
        $user->permissions()->sync($request->permissions ?? []);

        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Eliminar usuario (soft delete o desactivar)
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        // En lugar de eliminar, desactivamos
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
        $user->update([
            'is_active' => true,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario activado exitosamente.');
    }

    /**
     * Formulario de asignación de recinto
     */
    public function assignRecintoForm(User $user)
    {
        $this->authorize('assign_recinto_delegates');

        $recintos = Institution::where('active', true)->orderBy('name')->get();
        $currentAssignment = $user->recintoDelegations()
            ->where('is_active', true)
            ->first();

        return view('users.assign-recinto', compact('user', 'recintos', 'currentAssignment'));
    }

    /**
     * Guardar asignación de recinto
     */
    public function assignRecinto(Request $request, User $user)
    {
        $this->authorize('assign_recinto_delegates');

        $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'assigned_until' => 'nullable|date|after:today',
        ]);

        // Desactivar asignaciones anteriores
        $user->recintoDelegations()->update(['is_active' => false]);

        // Crear nueva asignación
        $user->recintoDelegations()->create([
            'institution_id' => $request->institution_id,
            'assigned_at' => now(),
            'assigned_until' => $request->assigned_until,
            'assigned_by' => Auth::id(),
            'is_active' => true,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Recinto asignado exitosamente.');
    }

    /**
     * Formulario de asignación de mesa
     */
    public function assignTableForm(User $user)
    {
        $this->authorize('assign_table_delegates');

        // Obtener mesas disponibles (del recinto del usuario si tiene)
        $recinto = $user->assigned_recinto;
        
        $query = VotingTable::with('institution')->where('status', 'activo');
        
        if ($recinto) {
            $query->where('institution_id', $recinto->id);
        }

        $mesas = $query->orderBy('institution_id')->orderBy('number')->get();
        $currentAssignments = $user->tableDelegations()
            ->where('is_active', true)
            ->with('votingTable')
            ->get();

        return view('users.assign-table', compact('user', 'mesas', 'currentAssignments'));
    }

    /**
     * Guardar asignación de mesa
     */
    public function assignTable(Request $request, User $user)
    {
        // if (!auth()->user()->hasPermission('assign_table_delegates')) {
        //     abort(403, 'No tienes permiso para asignar mesas');
        // }
        $this->authorize('assign_table_delegates');

        $request->validate([
            'voting_table_id' => 'required|exists:voting_tables,id',
            'role' => 'required|in:presidente,secretario,vocal',
            'assigned_until' => 'nullable|date|after:today',
        ]);

        // Verificar que la mesa no tenga ya un delegado activo
        $existingDelegate = TableDelegate::where('voting_table_id', $request->voting_table_id)
            ->where('is_active', true)
            ->first();

        if ($existingDelegate && $existingDelegate->user_id != $user->id) {
            return back()->with('error', 'Esta mesa ya tiene un delegado activo.');
        }

        // Crear asignación
        $user->tableDelegations()->create([
            'voting_table_id' => $request->voting_table_id,
            'role' => $request->role,
            'assigned_at' => now(),
            'assigned_until' => $request->assigned_until,
            'assigned_by' => Auth::id(),
            'is_active' => true,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Mesa asignada exitosamente.');
    }

    /**
     * Remover asignación
     */
    public function removeAssignment(User $user, $type, $assignmentId)
    {
        $this->authorize('assign_recinto_delegates');

        $model = null;
        switch ($type) {
            case 'recinto':
                $model = $user->recintoDelegations()->findOrFail($assignmentId);
                break;
            case 'mesa':
                $model = $user->tableDelegations()->findOrFail($assignmentId);
                break;
            case 'revisor':
                $model = $user->reviewerAssignments()->findOrFail($assignmentId);
                break;
            case 'modificador':
                $model = $user->modifierAssignments()->findOrFail($assignmentId);
                break;
            default:
                return back()->with('error', 'Tipo de asignación inválido.');
        }

        $model->update(['is_active' => false]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Asignación removida exitosamente.');
    }
}