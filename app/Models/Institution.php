<?php

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
        'is_operative'           => 'boolean',
        'registered_citizens'    => 'integer',
        'total_voting_tables'    => 'integer',
        'total_computed_records' => 'integer',
        'total_annulled_records' => 'integer',
        'total_enabled_records'  => 'integer',
        'total_pending_records'  => 'integer',
        'latitude'               => 'decimal:7',
        'longitude'              => 'decimal:7',
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

    public static function generateUniqueCode(): string
    {
        $maxId  = static::withTrashed()->max('id') ?? 0;
        $nextId = $maxId + 1;
        return 'INST' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

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

    /**
     * Province and Department are reached through Municipality, not stored directly
     * on Institution. Use $institution->municipality->province->department.
     * Keeping these as convenience pass-throughs via municipality relationship.
     */
    public function province(): BelongsTo
    {
        return $this->municipality->province()
            ?? $this->belongsTo(Province::class); // fallback — won't resolve without province_id
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class); // fallback only — no department_id on institutions
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

    // =========================================================================
    // SCOPES
    // =========================================================================

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

    // =========================================================================
    // BUSINESS LOGIC
    // =========================================================================

    /**
     * Recomputes the institution's aggregate counters from its voting tables
     * and their VotingTableElection pivot rows.
     *
     * ✅ FIXED from original: voting_tables has no computed_records, annulled_records,
     *    enabled_records, or registered_citizens columns — those aggregate counts
     *    come from VotingTableElection status and expected_voters on VotingTable.
     */
    public function updateTotals(): void
    {
        $tables = $this->votingTables()->withCount([])->get();

        $totalTables = $tables->count();

        // Computed = tables where ALL their elections are escrutada or transmitida
        $computed = $tables->filter(function ($table) {
            return $table->tableElections->every(
                fn($te) => in_array($te->status, [
                    VotingTableElection::STATUS_ESCRUTADA,
                    VotingTableElection::STATUS_TRANSMITIDA,
                ])
            );
        })->count();

        // Annulled = tables where any election is anulada
        $annulled = $tables->filter(function ($table) {
            return $table->tableElections->contains(
                fn($te) => $te->status === VotingTableElection::STATUS_ANULADA
            );
        })->count();

        // Pending = tables where any election is still configurada or en_espera
        $pending = $tables->filter(function ($table) {
            return $table->tableElections->contains(
                fn($te) => in_array($te->status, [
                    VotingTableElection::STATUS_CONFIGURADA,
                    VotingTableElection::STATUS_EN_ESPERA,
                ])
            );
        })->count();

        $this->update([
            'total_voting_tables'    => $totalTables,
            'total_computed_records' => $computed,
            'total_annulled_records' => $annulled,
            'total_enabled_records'  => $totalTables - $annulled,
            'total_pending_records'  => $pending,
        ]);
    }

    /**
     * Total expected voters across all voting tables in this institution.
     */
    public function getTotalExpectedVotersAttribute(): int
    {
        return $this->votingTables()->sum('expected_voters');
    }

    // =========================================================================
    // DISPLAY HELPERS
    // =========================================================================

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->locality?->name,
            $this->municipality?->name,
            $this->municipality?->province?->name,
            $this->municipality?->province?->department?->name,
        ]);

        return implode(', ', $parts);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'activo'           => '<span class="badge bg-success">Activo</span>',
            'inactivo'         => '<span class="badge bg-danger">Inactivo</span>',
            'en_mantenimiento' => '<span class="badge bg-warning">Mantenimiento</span>',
            default            => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }
}
