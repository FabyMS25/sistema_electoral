<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasPermissionsAndRoles; // ← IMPORTAR EL TRAIT

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasPermissionsAndRoles; // ← AGREGAR EL TRAIT

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

    // ===== RELACIONES EXISTENTES =====
    public function assignments()
    {
        return $this->hasMany(UserAssignment::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('scope', 'institution_id', 'voting_table_id', 'election_type_id', 'scope_settings')
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

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

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

    // ===== MÉTODOS DE PERMISOS (AHORA EN EL TRAIT) =====
    // Los métodos can(), hasPermissionTo(), hasRole(), etc. están en el trait

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
            $q->where('scope', 'institution')
              ->where('institution_id', $institutionId);
        })->orWhereHas('assignments', function($q) use ($institutionId) {
            $q->where('institution_id', $institutionId)
              ->where('status', 'activo');
        });
    }

    public function scopeByMesa($query, $votingTableId)
    {
        return $query->whereHas('roles', function($q) use ($votingTableId) {
            $q->where('scope', 'voting_table')
              ->where('voting_table_id', $votingTableId);
        })->orWhereHas('assignments', function($q) use ($votingTableId) {
            $q->where('voting_table_id', $votingTableId)
              ->where('status', 'activo');
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
