<?php
namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Institution;
use App\Models\VotingTable;

trait HasPermissionsAndRoles
{
    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function can($permission, $arguments = []): bool
    {
        if (is_string($permission)) {
            return $this->hasPermissionTo($permission);
        }

        return parent::can($permission, $arguments);
    }

    /**
     * Verificar si el usuario tiene un permiso específico (alias de can)
     */
    public function hasPermissionTo($permissionName, $scope = null, $scopeId = null): bool
    {
        $isGlobalAdmin = $this->roles()
            ->where('name', 'administrador')
            ->wherePivot('scope', 'global')
            ->exists();
        if ($isGlobalAdmin) {
            return true;
        }
        $directPermission = $this->permissions()
            ->where('name', $permissionName)
            ->where(function($q) use ($scope, $scopeId) {
                $q->where('scope', 'global')
                  ->orWhere(function($q2) use ($scope, $scopeId) {
                      $q2->where('scope', $scope);
                      if ($scopeId) {
                          $q2->where('scope_id', $scopeId);
                      }
                  });
            })->exists();

        if ($directPermission) return true;
        foreach ($this->roles as $role) {
            $hasPermission = $role->permissions()
                ->where('name', $permissionName)
                ->exists();
            if ($hasPermission) {
                if ($role->pivot->scope === 'global') {
                    return true;
                }
                if ($role->pivot->scope === $scope) {
                    if ($scope === 'institution' && $role->pivot->institution_id == $scopeId) {
                        return true;
                    }
                    if ($scope === 'voting_table' && $role->pivot->voting_table_id == $scopeId) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function hasRole($roleName): bool
    {
        if (is_array($roleName)) {
            return $this->roles()->whereIn('name', $roleName)->exists();
        }
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasAnyRole($roleNames): bool
    {
        return $this->roles()->whereIn('name', (array) $roleNames)->exists();
    }

    public function hasAllRoles($roleNames): bool
    {
        $count = $this->roles()->whereIn('name', (array) $roleNames)->count();
        return $count === count((array) $roleNames);
    }

    public function getAssignedInstitutionId()
    {
        if (method_exists($this, 'assignments')) {
            $assignment = $this->assignments()
                ->where('status', 'activo')
                ->whereNotNull('institution_id')
                ->whereNull('voting_table_id')
                ->first();
            if ($assignment) {
                return $assignment->institution_id;
            }
        }
        $role = $this->roles()
            ->wherePivot('scope', 'institution')
            ->wherePivot('institution_id', '!=', null)
            ->first();
        return $role ? $role->pivot->institution_id : null;
    }

    public function getAssignedVotingTableId()
    {
        if (method_exists($this, 'assignments')) {
            $assignment = $this->assignments()
                ->where('status', 'activo')
                ->whereNotNull('voting_table_id')
                ->first();
            if ($assignment) {
                return $assignment->voting_table_id;
            }
        }
        $role = $this->roles()
            ->wherePivot('scope', 'voting_table')
            ->wherePivot('voting_table_id', '!=', null)
            ->first();
        return $role ? $role->pivot->voting_table_id : null;
    }

    public function getAssignedInstitution()
    {
        $id = $this->getAssignedInstitutionId();
        return $id ? Institution::find($id) : null;
    }
    public function getAssignedVotingTable()
    {
        $id = $this->getAssignedVotingTableId();
        return $id ? VotingTable::with('institution')->find($id) : null;
    }
    public function getEffectiveScope()
    {
        if ($this->hasRole('administrador')) {
            return 'global';
        }
        if ($this->getAssignedInstitutionId()) {
            return 'institution';
        }
        if ($this->getAssignedVotingTableId()) {
            return 'voting_table';
        }
        foreach ($this->roles as $role) {
            if ($role->pivot->scope !== 'global') {
                return $role->pivot->scope;
            }
        }
        return 'global';
    }

    public function canAccessInstitution($institutionId)
    {
        if ($this->hasRole('administrador')) {
            return true;
        }
        if ($this->getAssignedInstitutionId() == $institutionId) {
            return true;
        }
        return $this->roles()
            ->wherePivot('scope', 'institution')
            ->wherePivot('institution_id', $institutionId)
            ->exists();
    }

    public function canAccessVotingTable($votingTableId)
    {
        if ($this->hasRole('administrador')) {
            return true;
        }
        if ($this->getAssignedVotingTableId() == $votingTableId) {
            return true;
        }
        $table = VotingTable::find($votingTableId);
        if ($table && $this->canAccessInstitution($table->institution_id)) {
            return true;
        }
        return $this->roles()
            ->wherePivot('scope', 'voting_table')
            ->wherePivot('voting_table_id', $votingTableId)
            ->exists();
    }
}
