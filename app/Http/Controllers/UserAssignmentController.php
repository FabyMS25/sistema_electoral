<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institution;
use App\Models\VotingTable;
use App\Models\UserAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles assignment actions for delegates.
 * election_type_id is NO LONGER on user_assignments — it lives on the
 * role_user pivot instead. A user may have at most one active assignment
 * per institution (no-table) and one per institution+table.
 */
class UserAssignmentController extends Controller
{
    private function requirePermission(string $permission): void
    {
        if (! Auth::user()->allPermissions->contains('name', $permission)) {
            abort(403, "Acceso denegado: se requiere el permiso '{$permission}'.");
        }
    }

    // ─── Assign recinto delegate ──────────────────────────────────────────────

    public function assignRecinto(Request $request, User $user)
    {
        $this->requirePermission('assign_delegates');

        $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'assigned_until' => 'nullable|date|after:today',
        ]);

        // Unique index: user + institution WHERE voting_table IS NULL
        // Deactivate any existing active institution-level assignment first
        UserAssignment::where('user_id', $user->id)
            ->where('institution_id', $request->institution_id)
            ->whereNull('voting_table_id')
            ->where('status', UserAssignment::STATUS_ACTIVO)
            ->update([
                'status'          => UserAssignment::STATUS_FINALIZADO,
                'expiration_date' => now(),
            ]);

        UserAssignment::create([
            'user_id'         => $user->id,
            'institution_id'  => $request->institution_id,
            'delegate_type'   => UserAssignment::DELEGATE_TYPE_GENERAL,
            'assignment_date' => now(),
            'expiration_date' => $request->assigned_until,
            'status'          => UserAssignment::STATUS_ACTIVO,
            'assigned_by'     => Auth::id(),
        ]);

        return back()->with('success', 'Delegado de recinto asignado exitosamente.');
    }

    // ─── Assign mesa delegate ─────────────────────────────────────────────────

    public function assignTable(Request $request, User $user)
    {
        $this->requirePermission('assign_delegates');

        $request->validate([
            'voting_table_id' => 'required|exists:voting_tables,id',
            'role'            => 'required|in:presidente,secretario,vocal,delegado_mesa',
            'assigned_until'  => 'nullable|date|after:today',
        ]);

        // One active assignment per table across all users
        $conflict = UserAssignment::where('voting_table_id', $request->voting_table_id)
            ->where('status', UserAssignment::STATUS_ACTIVO)
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Esta mesa ya tiene un delegado asignado.');
        }

        // institution_id NOT NULL in DB — derive from the table
        $table = VotingTable::findOrFail($request->voting_table_id);

        // Deactivate existing assignment for this user+table
        UserAssignment::where('user_id', $user->id)
            ->where('voting_table_id', $request->voting_table_id)
            ->where('status', UserAssignment::STATUS_ACTIVO)
            ->update([
                'status'          => UserAssignment::STATUS_FINALIZADO,
                'expiration_date' => now(),
            ]);

        UserAssignment::create([
            'user_id'         => $user->id,
            'institution_id'  => $table->institution_id,
            'voting_table_id' => $request->voting_table_id,
            'delegate_type'   => $request->role,
            'assignment_date' => now(),
            'expiration_date' => $request->assigned_until,
            'status'          => UserAssignment::STATUS_ACTIVO,
            'assigned_by'     => Auth::id(),
        ]);

        return back()->with('success', 'Delegado de mesa asignado exitosamente.');
    }

    // ─── Assign reviewer ─────────────────────────────────────────────────────

    public function assignReviewer(Request $request, User $user)
    {
        $this->requirePermission('assign_permissions');

        $request->validate([
            'assignable_type' => 'required|in:institution,voting_table',
            'assignable_id'   => 'required|integer',
        ]);

        if ($request->assignable_type === 'institution') {
            $request->validate(['assignable_id' => 'exists:institutions,id']);
            $this->createOrReplace($user, [
                'institution_id' => $request->assignable_id,
                'delegate_type'  => UserAssignment::DELEGATE_TYPE_TECNICO,
            ]);
        } else {
            $request->validate(['assignable_id' => 'exists:voting_tables,id']);
            $table = VotingTable::findOrFail($request->assignable_id);
            $this->createOrReplace($user, [
                'institution_id'  => $table->institution_id,
                'voting_table_id' => $request->assignable_id,
                'delegate_type'   => UserAssignment::DELEGATE_TYPE_TECNICO,
            ]);
        }

        return back()->with('success', 'Revisor asignado exitosamente.');
    }

    // ─── Assign modifier ─────────────────────────────────────────────────────

    public function assignModifier(Request $request, User $user)
    {
        $this->requirePermission('assign_permissions');

        $request->validate([
            'assignable_type' => 'required|in:institution,voting_table',
            'assignable_id'   => 'required|integer',
        ]);

        if ($request->assignable_type === 'institution') {
            $request->validate(['assignable_id' => 'exists:institutions,id']);
            $this->createOrReplace($user, [
                'institution_id' => $request->assignable_id,
                'delegate_type'  => UserAssignment::DELEGATE_TYPE_OBSERVADOR,
            ]);
        } else {
            $request->validate(['assignable_id' => 'exists:voting_tables,id']);
            $table = VotingTable::findOrFail($request->assignable_id);
            $this->createOrReplace($user, [
                'institution_id'  => $table->institution_id,
                'voting_table_id' => $request->assignable_id,
                'delegate_type'   => UserAssignment::DELEGATE_TYPE_OBSERVADOR,
            ]);
        }

        return back()->with('success', 'Modificador asignado exitosamente.');
    }

    // ─── Remove assignment ────────────────────────────────────────────────────

    public function removeAssignment(User $user, UserAssignment $assignment)
    {
        $this->requirePermission('edit_users');

        if ($assignment->user_id !== $user->id) {
            return back()->with('error', 'La asignación no pertenece a este usuario.');
        }

        $assignment->update([
            'status'          => UserAssignment::STATUS_FINALIZADO,
            'expiration_date' => now(),
        ]);

        return back()->with('success', 'Asignación removida exitosamente.');
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function createOrReplace(User $user, array $fields): void
    {
        $query = UserAssignment::where('user_id', $user->id)
            ->where('institution_id', $fields['institution_id'])
            ->where('delegate_type', $fields['delegate_type'])
            ->where('status', UserAssignment::STATUS_ACTIVO);

        isset($fields['voting_table_id'])
            ? $query->where('voting_table_id', $fields['voting_table_id'])
            : $query->whereNull('voting_table_id');

        $query->update([
            'status'          => UserAssignment::STATUS_FINALIZADO,
            'expiration_date' => now(),
        ]);

        UserAssignment::create(array_merge([
            'user_id'         => $user->id,
            'assignment_date' => now(),
            'status'          => UserAssignment::STATUS_ACTIVO,
            'assigned_by'     => Auth::id(),
        ], $fields));
    }
}
