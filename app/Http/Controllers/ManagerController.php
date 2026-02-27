<?php
// app/Http/Controllers/ManagerController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TableDelegate;
use App\Models\VotingTable;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_table_delegates')->only(['index', 'show']);
        $this->middleware('permission:assign_table_delegates')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Listado de delegados de mesa
     */
    public function index(Request $request)
    {
        $query = TableDelegate::with(['user', 'votingTable.institution', 'assignedBy'])
            ->where('is_active', true);

        // Filtros
        if ($request->filled('institution_id')) {
            $query->whereHas('votingTable', function($q) use ($request) {
                $q->where('institution_id', $request->institution_id);
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $delegates = $query->paginate(15);
        $institutions = Institution::where('active', true)->orderBy('name')->get();
        $roles = ['presidente', 'secretario', 'vocal'];

        return view('managers.index', compact('delegates', 'institutions', 'roles'));
    }

    /**
     * Formulario para asignar nuevo delegado
     */
    public function create()
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $institutions = Institution::where('active', true)->orderBy('name')->get();
        $roles = ['presidente', 'secretario', 'vocal'];

        return view('managers.create', compact('users', 'institutions', 'roles'));
    }

    /**
     * Guardar nuevo delegado
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'voting_table_id' => 'required|exists:voting_tables,id',
            'role' => 'required|in:presidente,secretario,vocal',
            'assigned_until' => 'nullable|date|after:today',
        ]);

        // Verificar que la mesa no tenga ya un delegado activo con el mismo rol
        $existing = TableDelegate::where('voting_table_id', $request->voting_table_id)
            ->where('role', $request->role)
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return back()->with('error', 'Esta mesa ya tiene un ' . $request->role . ' asignado.')
                ->withInput();
        }

        TableDelegate::create([
            'user_id' => $request->user_id,
            'voting_table_id' => $request->voting_table_id,
            'role' => $request->role,
            'assigned_at' => now(),
            'assigned_until' => $request->assigned_until,
            'assigned_by' => Auth::id(),
            'is_active' => true,
        ]);

        return redirect()->route('managers.index')
            ->with('success', 'Delegado de mesa asignado correctamente.');
    }

    /**
     * Ver detalle del delegado
     */
    public function show(TableDelegate $manager)
    {
        $manager->load(['user', 'votingTable.institution', 'assignedBy']);
        return view('managers.show', compact('manager'));
    }

    /**
     * Formulario de edición
     */
    public function edit(TableDelegate $manager)
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $institutions = Institution::where('active', true)->orderBy('name')->get();
        $roles = ['presidente', 'secretario', 'vocal'];

        return view('managers.edit', compact('manager', 'users', 'institutions', 'roles'));
    }

    /**
     * Actualizar delegado
     */
    public function update(Request $request, TableDelegate $manager)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'voting_table_id' => 'required|exists:voting_tables,id',
            'role' => 'required|in:presidente,secretario,vocal',
            'assigned_until' => 'nullable|date|after:today',
            'is_active' => 'boolean',
        ]);

        // Verificar que no haya conflicto con otro delegado
        $existing = TableDelegate::where('voting_table_id', $request->voting_table_id)
            ->where('role', $request->role)
            ->where('is_active', true)
            ->where('id', '!=', $manager->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Esta mesa ya tiene un ' . $request->role . ' asignado.')
                ->withInput();
        }

        $manager->update([
            'user_id' => $request->user_id,
            'voting_table_id' => $request->voting_table_id,
            'role' => $request->role,
            'assigned_until' => $request->assigned_until,
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('managers.show', $manager)
            ->with('success', 'Delegado actualizado correctamente.');
    }

    /**
     * Desactivar delegado
     */
    public function destroy(TableDelegate $manager)
    {
        $manager->update([
            'is_active' => false,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('managers.index')
            ->with('success', 'Delegado desactivado correctamente.');
    }

    /**
     * Obtener mesas de una institución (para AJAX)
     */
    public function getVotingTables($institution)
    {
        $tables = VotingTable::where('institution_id', $institution)
            ->where('status', 'activo')
            ->orderBy('number')
            ->get(['id', 'number', 'code']);

        return response()->json($tables);
    }
}