<?php
// app/Models/ElectionType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElectionType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'election_date',
        'start_time',
        'end_time',
        'registration_start',
        'registration_end',
        'campaign_start',
        'campaign_end',
        'total_voters',
        'total_tables',
        'total_recintos',
        'status',
        'active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'election_date' => 'date',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'campaign_start' => 'date',
        'campaign_end' => 'date',
        'active' => 'boolean',
        'total_voters' => 'integer',
        'total_tables' => 'integer',
        'total_recintos' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public const STATUS_PREPARACION = 'preparacion';
    public const STATUS_INSCRIPCION = 'inscripcion';
    public const STATUS_CAMPANA = 'campana';
    public const STATUS_VOTACION = 'votacion';
    public const STATUS_COMPUTO = 'computo';
    public const STATUS_FINALIZADO = 'finalizado';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PREPARACION => 'Preparación',
            self::STATUS_INSCRIPCION => 'Inscripción',
            self::STATUS_CAMPANA => 'Campaña',
            self::STATUS_VOTACION => 'Votación',
            self::STATUS_COMPUTO => 'Cómputo',
            self::STATUS_FINALIZADO => 'Finalizado',
        ];
    }

    // Relación con categorías a través de la tabla pivot
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ElectionCategory::class, 'election_type_categories')
            ->withPivot('votes_per_person', 'has_blank_vote', 'has_null_vote')
            ->withTimestamps();
    }

    public function typeCategories(): HasMany
    {
        return $this->hasMany(ElectionTypeCategory::class);
    }

    public function votingTables(): HasMany
    {
        return $this->hasMany(VotingTable::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(Acta::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        $colors = [
            self::STATUS_PREPARACION => 'secondary',
            self::STATUS_INSCRIPCION => 'info',
            self::STATUS_CAMPANA => 'primary',
            self::STATUS_VOTACION => 'warning',
            self::STATUS_COMPUTO => 'dark',
            self::STATUS_FINALIZADO => 'success',
        ];

        $color = $colors[$this->status] ?? 'secondary';
        $label = self::getStatuses()[$this->status] ?? $this->status;

        return "<span class='badge bg-{$color}'>{$label}</span>";
    }

    // Scope para tipos activos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Scope para tipos por fecha
    public function scopeByDate($query, $date)
    {
        return $query->where('election_date', $date);
    }

    // Scope para tipos actuales (fecha >= hoy)
    public function scopeCurrent($query)
    {
        return $query->where('election_date', '>=', now()->format('Y-m-d'));
    }

    // Scope para tipos pasados
    public function scopePast($query)
    {
        return $query->where('election_date', '<', now()->format('Y-m-d'));
    }

    // Actualizar totales
    public function updateTotals()
    {
        $this->update([
            'total_voters' => $this->votingTables()->sum('expected_voters'),
            'total_tables' => $this->votingTables()->count(),
            'total_recintos' => $this->votingTables()->distinct('institution_id')->count('institution_id'),
        ]);
    }
}
