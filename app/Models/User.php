<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 
        'last_name', 
        'id_card', 
        'email', 
        'phone', 
        'address',
        'password', 
        'avatar', 
        'is_active', 
        'last_login_at', 
        'last_login_ip',
        'created_by', 
        'updated_by'
    ];
    
    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime'
    ];

    // ===== RELACIONES =====
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('scope', 'scope_id', 'scope_type')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withPivot('scope', 'scope_id', 'scope_type')
            ->withTimestamps();
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'reviewed_by');
    }

    public function resolvedObservations()
    {
        return $this->hasMany(Observation::class, 'resolved_by');
    }

    // ===== CACHE DE PERMISOS =====
    
    private $allPermissionsCache = null;

    public function getAllPermissionsAttribute()
    {
        if ($this->allPermissionsCache === null) {
            $rolePermissions = $this->roles->flatMap(function($role) {
                return $role->permissions;
            });
            
            $directPermissions = $this->permissions;
            
            $this->allPermissionsCache = $rolePermissions
                ->merge($directPermissions)
                ->unique('id');
        }
        
        return $this->allPermissionsCache;
    }

    // ===== MÉTODOS DE PERMISOS =====
    
    public function hasPermission(string $permissionName): bool
    {
        return $this->all_permissions->contains('name', $permissionName);
    }
    
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
                      $q2->where('scope', $scope)
                         ->where('scope_id', $scopeId);
                  });
            })->exists();

        if ($directPermission) return true;

        // 3. Verificar a través de roles
        foreach ($this->roles as $role) {
            $hasPermission = $role->permissions()
                ->where('name', $permissionName)
                ->exists();

            if ($hasPermission) {
                // Verificar ámbito del rol
                if ($role->pivot->scope === 'global') return true;
                if ($role->pivot->scope === $scope && $role->pivot->scope_id == $scopeId) return true;
            }
        }

        return false;
    }
    
    // ===== OBTENER ASIGNACIONES POR ÁMBITO =====
    
    public function getAssignedRecintos()
    {
        $recintoIds = $this->roles()
            ->where('scope', 'recinto')
            ->where('scope_type', Institution::class)
            ->pluck('scope_id')
            ->unique();

        return Institution::whereIn('id', $recintoIds)->get();
    }

    public function getAssignedMesas()
    {
        $mesaIds = $this->roles()
            ->where('scope', 'mesa')
            ->where('scope_type', VotingTable::class)
            ->pluck('scope_id')
            ->unique();

        return VotingTable::whereIn('id', $mesaIds)->get();
    }

    public function getAssignedInstitutionId()
    {
        $assignment = $this->roles()
            ->where('scope', 'recinto')
            ->where('scope_type', Institution::class)
            ->first();
            
        return $assignment ? $assignment->pivot->scope_id : null;
    }

    public function getAssignedVotingTableId()
    {
        $assignment = $this->roles()
            ->where('scope', 'mesa')
            ->where('scope_type', VotingTable::class)
            ->first();
            
        return $assignment ? $assignment->pivot->scope_id : null;
    }

    // ===== MÉTODOS DE ROLES =====
    
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles->whereIn('name', $roleNames)->isNotEmpty();
    }

    public function hasAllRoles(array $roleNames): bool
    {
        $userRoleNames = $this->roles->pluck('name')->toArray();
        return empty(array_diff($roleNames, $userRoleNames));
    }

    public function getRoleNamesAttribute(): string
    {
        return $this->roles->pluck('display_name')->implode(', ');
    }

    // ===== SCOPES =====
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('roles', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    public function scopeByRecinto($query, $institutionId)
    {
        return $query->whereHas('roles', function($q) use ($institutionId) {
            $q->where('scope', 'recinto')
              ->where('scope_id', $institutionId)
              ->where('scope_type', Institution::class);
        });
    }

    // ===== BOOT =====
    
    protected static function booted()
    {
        static::saved(function ($user) {
            $user->allPermissionsCache = null;
        });

        static::updated(function ($user) {
            $user->allPermissionsCache = null;
        });
    }
}