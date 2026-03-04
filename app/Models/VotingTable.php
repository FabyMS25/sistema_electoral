<?php
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
        'status',
        'oep_code',
        'internal_code',
        'number',
        'letter',
        'type',
        'institution_id',
        'election_type_id',
        // Datos pre-electorales
        'expected_voters',
        'ballots_received',
        'ballots_spoiled',
        'voter_range_start_name',
        'voter_range_end_name',
        'election_date',
        'opening_time',
        'closing_time',
        // Personal de mesa
        'president_id',
        'secretary_id',
        'vocal1_id',
        'vocal2_id',
        'vocal3_id',
        'vocal4_id',
        // Control de papeletas
        'ballots_used',
        'ballots_leftover',
        // Resultados Primera Categorria (Ej: Alcaldes)
        'valid_votes',
        'blank_votes',
        'null_votes',
        // Resultados Segunda Categoria (Ej: Concejales)
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

        'validated_at',
        'validated_by',
        'verified_at',
        'verified_by',
        'verification_notes',
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
        'validated_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Estados de mesa
    const STATUS_CONFIGURADA = 'configurada';
    const STATUS_EN_ESPERA = 'en_espera';
    const STATUS_VOTACION = 'votacion';
    const STATUS_CERRADA = 'cerrada';
    const STATUS_EN_ESCRUTINIO = 'en_escrutinio';
    const STATUS_ESCRUTADA = 'escrutada';
    const STATUS_OBSERVADA = 'observada';
    const STATUS_TRANSMITIDA = 'transmitida';
    const STATUS_ANULADA = 'anulada';
    // Estados de validación
    const VALIDATION_PENDING = 'pending';
    const VALIDATION_REVIEWED = 'reviewed';
    const VALIDATION_OBSERVED = 'observed';
    const VALIDATION_CORRECTED = 'corrected';
    const VALIDATION_VALIDATED = 'validated';
    const VALIDATION_APPROVED = 'approved';
    const VALIDATION_REJECTED = 'rejected';

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
    public function vocal4(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vocal4_id');
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
    public function assignments()
    {
        return $this->hasMany(UserAssignment::class, 'voting_table_id');
    }

    public function delegates()
    {
        return $this->assignments()
            ->whereIn('delegate_type', ['delegado_mesa', 'presidente', 'secretario', 'vocal'])
            ->where('status', 'activo');
    }
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ===== MÉTODOS DE NEGOCIO =====
    public function updateValidationStatus()
    {
        $votes = $this->votes;
        if ($votes->isEmpty()) {
            $this->status = self::STATUS_CONFIGURADA;
            $this->save();
            return;
        }
        $statusCounts = [
            Vote::VOTE_STATUS_OBSERVED => $votes->where('vote_status', Vote::VOTE_STATUS_OBSERVED)->count(),
            Vote::VOTE_STATUS_CORRECTED => $votes->where('vote_status', Vote::VOTE_STATUS_CORRECTED)->count(),
            Vote::VOTE_STATUS_APPROVED => $votes->where('vote_status', Vote::VOTE_STATUS_APPROVED)->count(),
            Vote::VOTE_STATUS_REJECTED => $votes->where('vote_status', Vote::VOTE_STATUS_REJECTED)->count(),
        ];
        if ($statusCounts[Vote::VOTE_STATUS_OBSERVED] > 0) {
            $this->status = self::STATUS_OBSERVADA;
        } elseif ($statusCounts[Vote::VOTE_STATUS_CORRECTED] > 0) {
            $this->status = self::STATUS_EN_ESCRUTINIO;
        } elseif ($statusCounts[Vote::VOTE_STATUS_APPROVED] == $votes->count()) {
            $this->status = self::STATUS_ESCRUTADA;
        } elseif ($statusCounts[Vote::VOTE_STATUS_REJECTED] > 0) {
            $this->status = self::STATUS_ANULADA;
        }

        $this->save();
    }
    public function validateResults(): array
    {
        $errors = [];

        // Calcular totales de votantes en cada categoría
        $totalMayor = $this->valid_votes + $this->blank_votes + $this->null_votes;
        $totalCouncil = $this->valid_votes_second + $this->blank_votes_second + $this->null_votes_second;

        // Actualizar campos calculados
        $this->total_voters = $totalMayor;
        $this->total_voters_second = $totalCouncil;
        $this->ballots_used = $totalMayor;

        // 🔴 VALIDACIÓN CORREGIDA: El número de votantes DEBE ser igual en ambas categorías
        if ($totalMayor != $totalCouncil) {
            $errors[] = "El número de votantes en Alcaldes ($totalMayor) debe ser igual al de Concejales ($totalCouncil)";
        }

        // Validar contra votantes habilitados
        if ($totalMayor > $this->expected_voters) {
            $errors[] = "Los votantes ($totalMayor) exceden los habilitados ({$this->expected_voters})";
        }

        // Validar papeletas
        $totalBallotsAvailable = ($this->ballots_received ?? 0) - ($this->ballots_spoiled ?? 0);
        $this->ballots_leftover = ($this->ballots_received ?? 0) - $totalMayor - ($this->ballots_spoiled ?? 0);

        if ($totalBallotsAvailable > 0 && $totalMayor > $totalBallotsAvailable) {
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


    /**
     * Verifica si la mesa tiene observaciones pendientes
     */
    public function hasPendingObservations(): bool
    {
        return $this->observations()
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Obtiene las observaciones pendientes
     */
    public function getPendingObservations()
    {
        return $this->observations()
            ->where('status', 'pending')
            ->with('reviewer')
            ->get();
    }

    /**
     * Marca la mesa como observada
     */
    public function markAsObserved($userId, $observationId, $notes = null)
    {
        $this->status = self::STATUS_OBSERVADA;
        $this->validation_status = self::VALIDATION_OBSERVED;
        $this->verified_by = $userId;
        $this->verified_at = now();
        $this->verification_notes = $notes;
        $this->save();
        $this->votes()->update([
            'vote_status' => Vote::VOTE_STATUS_OBSERVED,
            'observation_id' => $observationId,
            'verified_by' => $userId,
            'verified_at' => now(),
        ]);
    }

    /**
     * Marca la mesa como corregida
     */
    public function markAsCorrected($userId, $notes = null)
    {
        $this->status = self::STATUS_EN_ESCRUTINIO;
        $this->validation_status = self::VALIDATION_CORRECTED;
        $this->corrected_by = $userId;
        $this->corrected_at = now();
        $this->correction_notes = $notes;
        $this->save();

        // Cerrar observaciones asociadas
        Observation::where('voting_table_id', $this->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'resolved_by' => $userId,
                'resolved_at' => now(),
                'resolution_notes' => 'Corregido por usuario',
            ]);
    }

    /**
     * Marca la mesa como validada
     */
    public function markAsValidated($userId, $notes = null)
    {
        $this->validation_status = self::VALIDATION_VALIDATED;
        $this->validated_by = $userId;
        $this->validated_at = now();
        $this->validation_notes = $notes;
        $this->save();

        $this->votes()->update([
            'validated_by' => $userId,
            'validated_at' => now(),
        ]);
    }

    /**
     * Marca la mesa como aprobada
     */
    public function markAsApproved($userId, $notes = null)
    {
        $this->status = self::STATUS_ESCRUTADA;
        $this->validation_status = self::VALIDATION_APPROVED;
        $this->validated_by = $userId;
        $this->validated_at = now();
        $this->validation_notes = $notes;
        $this->save();

        $this->votes()->update([
            'vote_status' => Vote::VOTE_STATUS_APPROVED,
            'validated_by' => $userId,
            'validated_at' => now(),
        ]);
    }

    public function canBeModified(): bool
    {
        return !in_array($this->status, [
            self::STATUS_ESCRUTADA,
            self::STATUS_TRANSMITIDA,
            self::STATUS_ANULADA
        ]);
    }

    /**
     * Verifica si la mesa puede ser observada
     */
    public function canBeObserved(): bool
    {
        return in_array($this->status, [
            self::STATUS_VOTACION,
            self::STATUS_CERRADA,
            self::STATUS_EN_ESCRUTINIO
        ]) && $this->validation_status !== self::VALIDATION_APPROVED;
    }
}
