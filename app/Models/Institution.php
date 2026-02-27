<?php
// app/Models/Institution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'department_id',
        'province_id',
        'municipality_id',
        'locality_id',
        'district_id',
        'zone_id',
        'address',
        'reference',
        'latitude',
        'longitude',
        'registered_citizens',
        'total_voting_tables',
        'total_computed_records',
        'total_annulled_records',
        'total_enabled_records',
        'total_pending_records',
        'phone',
        'email',
        'responsible_name',
        'status',
        'is_operative',
        'observations',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_operative' => 'boolean',
        'registered_citizens' => 'integer',
        'total_voting_tables' => 'integer',
        'total_computed_records' => 'integer',
        'total_annulled_records' => 'integer',
        'total_enabled_records' => 'integer',
        'total_pending_records' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = static::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode()
    {
        $prefix = 'INST';
        $maxId = static::withTrashed()->max('id') ?? 0;
        $nextId = $maxId + 1;
        return $prefix . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    // ===== RELACIONES =====
    
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function votingTables(): HasMany
    {
        return $this->hasMany(VotingTable::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ===== SCOPES =====
    
    public function scopeActive($query)
    {
        return $query->where('status', 'activo');
    }

    public function scopeOperative($query)
    {
        return $query->where('is_operative', true);
    }

    public function scopeByMunicipality($query, $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    // ===== MÉTODOS =====
    
    public function updateTotals()
    {
        $this->update([
            'total_computed_records' => $this->votingTables()->sum('computed_records'),
            'total_annulled_records' => $this->votingTables()->sum('annulled_records'),
            'total_enabled_records' => $this->votingTables()->sum('enabled_records'),
            'total_voting_tables' => $this->votingTables()->count(),
            'registered_citizens' => $this->votingTables()->sum('registered_citizens'),
        ]);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if ($this->address) $parts[] = $this->address;
        if ($this->locality) $parts[] = $this->locality->name;
        if ($this->municipality) $parts[] = $this->municipality->name;
        if ($this->province) $parts[] = $this->province->name;
        if ($this->department) $parts[] = $this->department->name;
        
        return implode(', ', $parts);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'activo' => '<span class="badge bg-success">Activo</span>',
            'inactivo' => '<span class="badge bg-danger">Inactivo</span>',
            'en_mantenimiento' => '<span class="badge bg-warning">Mantenimiento</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }
}