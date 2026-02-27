<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection as EloquentCollection; // ALIAS para evitar confusión
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Collection; // Para Support Collection

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

    // Relaciones con roles y permisos
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
                    ->withTimestamps();
    }
    
    public function recintoDelegations()
    {
        return $this->hasMany(RecintoDelegate::class, 'user_id');
    }

    public function tableDelegations()
    {
        return $this->hasMany(TableDelegate::class, 'user_id');
    }

    public function reviewerAssignments()
    {
        return $this->hasMany(Reviewer::class, 'user_id');
    }

    public function modifierAssignments()
    {
        return $this->hasMany(Modifier::class, 'user_id');
    }

    // Relaciones para auditoría
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

    // Cache de permisos - CORREGIDO: Usamos Collection de Support
    private ?Collection $allPermissionsCache = null;

    /**
     * Obtener todos los permisos del usuario (roles + directos)
     * CORREGIDO: Retorna Collection, no EloquentCollection
     */
    public function getAllPermissionsAttribute(): Collection
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

    /**
     * Verificar si tiene un permiso específico
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->all_permissions->contains('name', $permissionName);
    }

    /**
     * Verificar si tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Verificar si tiene alguno de los roles dados
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles->whereIn('name', $roleNames)->isNotEmpty();
    }

    /**
     * Obtener el recinto asignado actualmente (si es delegado de recinto)
     */
    public function getAssignedRecintoAttribute()
    {
        $activeDelegate = $this->recintoDelegations()
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('assigned_until')
                  ->orWhere('assigned_until', '>=', now());
            })
            ->with('institution')
            ->first();
            
        return $activeDelegate ? $activeDelegate->institution : null;
    }

    /**
     * Obtener la mesa asignada actualmente (si es delegado de mesa)
     */
    public function getAssignedVotingTableAttribute()
    {
        $activeDelegate = $this->tableDelegations()
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('assigned_until')
                  ->orWhere('assigned_until', '>=', now());
            })
            ->with('votingTable')
            ->first();
            
        return $activeDelegate ? $activeDelegate->votingTable : null;
    }

    /**
     * Obtener todas las asignaciones activas del usuario
     */
    public function getActiveAssignmentsAttribute()
    {
        return [
            'recinto' => $this->assigned_recinto,
            'voting_table' => $this->assigned_voting_table,
            'reviewer_assignments' => $this->reviewerAssignments()
                ->where('is_active', true)
                ->get(),
            'modifier_assignments' => $this->modifierAssignments()
                ->where('is_active', true)
                ->get()
        ];
    }

    /**
     * Scope para usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para usuarios por rol
     */
    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('roles', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Limpiar caché cuando se actualiza el usuario
     */
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