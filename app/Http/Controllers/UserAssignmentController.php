<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institution;
use App\Models\VotingTable;
use App\Models\RecintoDelegate;
use App\Models\TableDelegate;
use App\Models\Reviewer;
use App\Models\Modifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAssignmentController extends Controller
{
    public function assignRecinto(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('assign_recinto_delegates')) {
            abort(403);
        }
        $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'assigned_until' => 'nullable|date|after:today'
        ]);
        RecintoDelegate::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Crear nueva asignación
        RecintoDelegate::create([
            'user_id' => $user->id,
            'institution_id' => $request->institution_id,
            'assigned_at' => now(),
            'assigned_until' => $request->assigned_until,
            'assigned_by' => Auth::id(),
            'is_active' => true
        ]);

        return back()->with('success', 'Delegado de recinto asignado exitosamente');
    }

    public function assignTable(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('assign_table_delegates')) {
            abort(403);
        }

        $request->validate([
            'voting_table_id' => 'required|exists:voting_tables,id',
            'role' => 'required|in:presidente,secretario,vocal',
            'assigned_until' => 'nullable|date|after:today'
        ]);

        TableDelegate::create([
            'user_id' => $user->id,
            'voting_table_id' => $request->voting_table_id,
            'role' => $request->role,
            'assigned_at' => now(),
            'assigned_until' => $request->assigned_until,
            'assigned_by' => Auth::id(),
            'is_active' => true
        ]);

        return back()->with('success', 'Delegado de mesa asignado exitosamente');
    }

    public function assignReviewer(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('assign_permissions')) {
            abort(403);
        }

        $request->validate([
            'assignable_type' => 'required|in:institution,voting_table',
            'assignable_id' => 'required'
        ]);

        Reviewer::create([
            'user_id' => $user->id,
            'assignable_type' => $request->assignable_type === 'institution'
                ? Institution::class
                : VotingTable::class,
            'assignable_id' => $request->assignable_id,
            'assigned_at' => now(),
            'assigned_by' => Auth::id(),
            'is_active' => true
        ]);

        return back()->with('success', 'Revisor asignado exitosamente');
    }

    public function assignModifier(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('assign_permissions')) {
            abort(403);
        }

        $request->validate([
            'assignable_type' => 'required|in:institution,voting_table',
            'assignable_id' => 'required'
        ]);

        Modifier::create([
            'user_id' => $user->id,
            'assignable_type' => $request->assignable_type === 'institution'
                ? Institution::class
                : VotingTable::class,
            'assignable_id' => $request->assignable_id,
            'assigned_at' => now(),
            'assigned_by' => Auth::id(),
            'is_active' => true
        ]);

        return back()->with('success', 'Modificador asignado exitosamente');
    }

    public function removeAssignment(Request $request, $type, $id)
    {
        if (!auth()->user()->hasPermission('edit_users')) {
            abort(403);
        }

        switch ($type) {
            case 'recinto':
                $assignment = RecintoDelegate::findOrFail($id);
                break;
            case 'mesa':
                $assignment = TableDelegate::findOrFail($id);
                break;
            case 'revisor':
                $assignment = Reviewer::findOrFail($id);
                break;
            case 'modificador':
                $assignment = Modifier::findOrFail($id);
                break;
            default:
                return back()->with('error', 'Tipo de asignación no válido');
        }

        $assignment->update([
            'is_active' => false,
            'assigned_until' => now()
        ]);

        return back()->with('success', 'Asignación removida exitosamente');
    }
}
