<?php
// app/Models/VotingTable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotingTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'voting_tables';

    protected $fillable = [
        // Códigos
        'oep_code',
        'internal_code',
        'number',
        'letter',
        'type',
        
        // Ubicación
        'municipality_id',
        'institution_id',
        'election_type_id',
        
        // Datos pre-electorales
        'expected_voters',
        'ballots_received',
        'ballots_spoiled',
        
        // Rango de votantes
        'voter_range_start_name',
        'voter_range_end_name',
        
        // Personal de mesa
        'president_id',
        'secretary_id',
        'vocal1_id',
        'vocal2_id',
        'vocal3_id',
        'vocal4_name',
        
        // Fechas y horas
        'election_date',
        'opening_time',
        'closing_time',
        
        // Estado
        'status',
        
        // Control de papeletas
        'ballots_used',
        'ballots_leftover',
        
        // Resultados Alcalde
        'valid_votes',
        'blank_votes',
        'null_votes',
        
        // Resultados Concejal
        'valid_votes_second',
        'blank_votes_second',
        'null_votes_second',
        
        // Totales
        'total_voters',
        'total_voters_second',
        
        // Acta
        'acta_number',
        'acta_photo',
        'acta_uploaded_at',
        'observations',
        
        // Auditoría
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'number' => 'integer',
        'expected_voters' => 'integer',
        'ballots_received' => 'integer',
        'ballots_spoiled' => 'integer',
        'ballots_used' => 'integer',
        'ballots_leftover' => 'integer',
        'valid_votes' => 'integer',
        'blank_votes' => 'integer',
        'null_votes' => 'integer',
        'valid_votes_second' => 'integer',
        'blank_votes_second' => 'integer',
        'null_votes_second' => 'integer',
        'total_voters' => 'integer',
        'total_voters_second' => 'integer',
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
        'election_date' => 'date',
        'acta_uploaded_at' => 'datetime',
    ];

    // Estados
    public const STATUS_CONFIGURADA = 'configurada';
    public const STATUS_EN_ESPERA = 'en_espera';
    public const STATUS_VOTACION = 'votacion';
    public const STATUS_CERRADA = 'cerrada';
    public const STATUS_EN_ESCRUTINIO = 'en_escrutinio';
    public const STATUS_ESCRUTADA = 'escrutada';
    public const STATUS_OBSERVADA = 'observada';
    public const STATUS_TRANSMITIDA = 'transmitida';
    public const STATUS_ANULADA = 'anulada';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_CONFIGURADA => 'Configurada',
            self::STATUS_EN_ESPERA => 'En Espera',
            self::STATUS_VOTACION => 'Votación',
            self::STATUS_CERRADA => 'Cerrada',
            self::STATUS_EN_ESCRUTINIO => 'En Escrutinio',
            self::STATUS_ESCRUTADA => 'Escrutada',
            self::STATUS_OBSERVADA => 'Observada',
            self::STATUS_TRANSMITIDA => 'Transmitida',
            self::STATUS_ANULADA => 'Anulada',
        ];
    }

    // ===== RELACIONES =====
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class);
    }
    public function electionCategory()
    {
        return $this->belongsTo(ElectionCategory::class, 'election_category_id');
    }
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function president(): BelongsTo
    {
        return $this->belongsTo(User::class, 'president_id');
    }

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    public function vocal1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vocal1_id');
    }

    public function vocal2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vocal2_id');
    }

    public function vocal3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vocal3_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(Acta::class);
    }
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // ===== MÉTODOS DE NEGOCIO =====    
    public function validateResults(): array
    {
        $errors = [];        
        $totalMayor = $this->valid_votes + $this->blank_votes + $this->null_votes;
        $totalCouncil = $this->valid_votes_second + $this->blank_votes_second + $this->null_votes_second;
        $this->total_voters = $totalMayor;
        $this->total_voters_second = $totalCouncil;
        $this->ballots_used = $totalMayor;        
        if ($totalMayor != $totalCouncil) {
            $errors[] = "El número de votantes en Alcaldes ($totalMayor) debe ser igual al de Concejales ($totalCouncil)";
        }        
        if ($totalMayor > $this->expected_voters) {
            $errors[] = "Los votantes ($totalMayor) exceden los habilitados ({$this->expected_voters})";
        }        
        $totalBallotsAvailable = $this->ballots_received - $this->ballots_spoiled;
        $this->ballots_leftover = $this->ballots_received - $totalMayor - $this->ballots_spoiled;
        if ($totalMayor > $totalBallotsAvailable) {
            $errors[] = "Papeletas usadas ($totalMayor) exceden disponibles ($totalBallotsAvailable)";
        }        
        if ($this->ballots_leftover < 0) {
            $errors[] = "Error en cálculo de papeletas sobrantes";
        }        
        return $errors;
    }

    public function isConsistent(): bool
    {
        return empty($this->validateResults());
    }
        
    public function getAbsentVotersAttribute(): int
    {
        return max(0, $this->expected_voters - $this->total_voters);
    }

    public function getParticipationPercentageAttribute(): float
    {
        if ($this->expected_voters == 0) return 0;
        return round(($this->total_voters / $this->expected_voters) * 100, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $colors = [
            self::STATUS_CONFIGURADA => 'secondary',
            self::STATUS_EN_ESPERA => 'info',
            self::STATUS_VOTACION => 'primary',
            self::STATUS_CERRADA => 'warning',
            self::STATUS_EN_ESCRUTINIO => 'dark',
            self::STATUS_ESCRUTADA => 'success',
            self::STATUS_OBSERVADA => 'danger',
            self::STATUS_TRANSMITIDA => 'success',
            self::STATUS_ANULADA => 'dark',
        ];
        
        $statuses = self::getStatuses();
        $color = $colors[$this->status] ?? 'secondary';
        $label = $statuses[$this->status] ?? $this->status;
        
        return "<span class='badge bg-{$color}'>{$label}</span>";
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'mixta' => 'Mixta',
            'masculina' => 'Masculina',
            'femenina' => 'Femenina',
            default => $this->type,
        };
    }

    public function getFullCodeAttribute(): string
    {
        return $this->internal_code ?? $this->oep_code;
    }
}