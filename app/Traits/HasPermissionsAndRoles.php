<?php
// app/Traits/HasPermissionsAndRoles.php

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
        // 1. SUPER ADMIN - Si tiene rol admin con ámbito global, puede todo
        $isGlobalAdmin = $this->roles()
            ->where('name', 'administrador')
            ->wherePivot('scope', 'global')
            ->exists();

        if ($isGlobalAdmin) {
            return true;
        }

        // 2. Verificar permisos directos del usuario
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

        // 3. Verificar a través de roles
        foreach ($this->roles as $role) {
            $hasPermission = $role->permissions()
                ->where('name', $permissionName)
                ->exists();

            if ($hasPermission) {
                // Verificar ámbito del rol usando los campos correctos del pivot
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

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($roleName): bool
    {
        if (is_array($roleName)) {
            return $this->roles()->whereIn('name', $roleName)->exists();
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole($roleNames): bool
    {
        return $this->roles()->whereIn('name', (array) $roleNames)->exists();
    }

    /**
     * Verificar si el usuario tiene todos los roles especificados
     */
    public function hasAllRoles($roleNames): bool
    {
        $count = $this->roles()->whereIn('name', (array) $roleNames)->count();
        return $count === count((array) $roleNames);
    }

    /**
     * Obtener el ID de la institución asignada (si existe)
     */
    public function getAssignedInstitutionId()
    {
        // Primero buscar en assignments
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

        // Luego buscar en roles
        $role = $this->roles()
            ->wherePivot('scope', 'institution')
            ->wherePivot('institution_id', '!=', null)
            ->first();

        return $role ? $role->pivot->institution_id : null;
    }

    /**
     * Obtener el ID de la mesa asignada (si existe)
     */
    public function getAssignedVotingTableId()
    {
        // Primero buscar en assignments
        if (method_exists($this, 'assignments')) {
            $assignment = $this->assignments()
                ->where('status', 'activo')
                ->whereNotNull('voting_table_id')
                ->first();

            if ($assignment) {
                return $assignment->voting_table_id;
            }
        }

        // Luego buscar en roles
        $role = $this->roles()
            ->wherePivot('scope', 'voting_table')
            ->wherePivot('voting_table_id', '!=', null)
            ->first();

        return $role ? $role->pivot->voting_table_id : null;
    }

    /**
     * Obtener la institución asignada completa
     */
    public function getAssignedInstitution()
    {
        $id = $this->getAssignedInstitutionId();
        return $id ? Institution::find($id) : null;
    }

    /**
     * Obtener la mesa asignada completa
     */
    public function getAssignedVotingTable()
    {
        $id = $this->getAssignedVotingTableId();
        return $id ? VotingTable::with('institution')->find($id) : null;
    }

    /**
     * Obtener el ámbito efectivo del usuario
     */
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

        // Verificar roles
        foreach ($this->roles as $role) {
            if ($role->pivot->scope !== 'global') {
                return $role->pivot->scope;
            }
        }

        return 'global';
    }

    /**
     * Verificar si el usuario puede acceder a una institución específica
     */
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

    /**
     * Verificar si el usuario puede acceder a una mesa específica
     */
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
