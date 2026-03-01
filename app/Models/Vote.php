<?php
// app/Models/Vote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'votes';

    protected $fillable = [
        'quantity',
        'percentage',
        'vote_status',
        'validation_status',
        'voting_table_id',
        'candidate_id',
        'election_type_id',
        'user_id',
        'registered_at',
        'verified_at',
        'verified_by',
        'verification_notes',
        'corrected_by',
        'corrected_at',
        'correction_notes',
        'observation_id',
        'acta_photo',
        'acta_pdf',
        'has_physical_acta',
        'is_synced',
        'synced_at',
        'validated_at',
        'validated_by',
        'validation_notes',
        'closed_at',
        'closed_by',
        'reopened_at',
        'reopened_by',
        'reopen_count',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'percentage' => 'float',
        'registered_at' => 'datetime',
        'verified_at' => 'datetime',
        'corrected_at' => 'datetime',
        'synced_at' => 'datetime',
        'validated_at' => 'datetime',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
        'reopen_count' => 'integer',
        'has_physical_acta' => 'boolean',
        'is_synced' => 'boolean',
    ];

    // Constantes para estados
    const VOTE_STATUS_PENDING = 'pending';
    const VOTE_STATUS_VERIFIED = 'verified';
    const VOTE_STATUS_OBSERVED = 'observed';
    const VOTE_STATUS_CORRECTED = 'corrected';
    const VOTE_STATUS_APPROVED = 'approved';
    const VOTE_STATUS_REJECTED = 'rejected';

    const VALIDATION_STATUS_PENDING = 'pending';
    const VALIDATION_STATUS_REVIEWED = 'reviewed';
    const VALIDATION_STATUS_OBSERVED = 'observed';
    const VALIDATION_STATUS_CORRECTED = 'corrected';
    const VALIDATION_STATUS_VALIDATED = 'validated';
    const VALIDATION_STATUS_APPROVED = 'approved';
    const VALIDATION_STATUS_REJECTED = 'rejected';

    public static function getVoteStatuses(): array
    {
        return [
            self::VOTE_STATUS_PENDING => 'Pendiente',
            self::VOTE_STATUS_VERIFIED => 'Verificado',
            self::VOTE_STATUS_OBSERVED => 'Observado',
            self::VOTE_STATUS_CORRECTED => 'Corregido',
            self::VOTE_STATUS_APPROVED => 'Aprobado',
            self::VOTE_STATUS_REJECTED => 'Rechazado',
        ];
    }

    public static function getValidationStatuses(): array
    {
        return [
            self::VALIDATION_STATUS_PENDING => 'Pendiente',
            self::VALIDATION_STATUS_REVIEWED => 'Revisado',
            self::VALIDATION_STATUS_OBSERVED => 'Observado',
            self::VALIDATION_STATUS_CORRECTED => 'Corregido',
            self::VALIDATION_STATUS_VALIDATED => 'Validado',
            self::VALIDATION_STATUS_APPROVED => 'Aprobado',
            self::VALIDATION_STATUS_REJECTED => 'Rechazado',
        ];
    }

    // ===== RELACIONES =====

    public function votingTable(): BelongsTo
    {
        return $this->belongsTo(VotingTable::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    // ===== MÉTODOS DE ESTADO =====

    public function isPending(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_PENDING;
    }

    public function isVerified(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_VERIFIED;
    }

    public function isObserved(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_OBSERVED;
    }

    public function isCorrected(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_CORRECTED;
    }

    public function isApproved(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_REJECTED;
    }

    public function isValidated(): bool
    {
        return $this->validation_status === self::VALIDATION_STATUS_VALIDATED;
    }

    public function isValidationPending(): bool
    {
        return $this->validation_status === self::VALIDATION_STATUS_PENDING;
    }

    public function isValidationReviewed(): bool
    {
        return $this->validation_status === self::VALIDATION_STATUS_REVIEWED;
    }

    public function isValidationObserved(): bool
    {
        return $this->validation_status === self::VALIDATION_STATUS_OBSERVED;
    }

    public function isValidationCorrected(): bool
    {
        return $this->validation_status === self::VALIDATION_STATUS_CORRECTED;
    }

    public function isValidationApproved(): bool
    {
        return $this->validation_status === self::VALIDATION_STATUS_APPROVED;
    }

    public function isValidationRejected(): bool
    {
        return $this->validation_status === self::VALIDATION_STATUS_REJECTED;
    }

    // ===== SCOPES =====

    public function scopeByElectionType($query, $electionTypeId)
    {
        return $query->where('election_type_id', $electionTypeId);
    }

    public function scopeByVotingTable($query, $votingTableId)
    {
        return $query->where('voting_table_id', $votingTableId);
    }

    public function scopeByCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    public function scopeWithVoteStatus($query, $status)
    {
        return $query->where('vote_status', $status);
    }

    public function scopeWithValidationStatus($query, $status)
    {
        return $query->where('validation_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_PENDING);
    }

    public function scopeVerified($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_VERIFIED);
    }

    public function scopeObserved($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_OBSERVED);
    }

    public function scopeCorrected($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_CORRECTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_REJECTED);
    }

    public function scopeValidationPending($query)
    {
        return $query->where('validation_status', self::VALIDATION_STATUS_PENDING);
    }

    public function scopeValidationReviewed($query)
    {
        return $query->where('validation_status', self::VALIDATION_STATUS_REVIEWED);
    }

    public function scopeValidationValidated($query)
    {
        return $query->where('validation_status', self::VALIDATION_STATUS_VALIDATED);
    }

    // ===== MÉTODOS DE ACCIÓN =====

    public function markAsVerified($userId, $notes = null)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_VERIFIED,
            'verified_by' => $userId,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    public function markAsObserved($observationId)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_OBSERVED,
            'validation_status' => self::VALIDATION_STATUS_OBSERVED,
            'observation_id' => $observationId,
        ]);
    }

    public function markAsCorrected($userId, $notes = null)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_CORRECTED,
            'corrected_by' => $userId,
            'corrected_at' => now(),
            'correction_notes' => $notes,
        ]);
    }

    public function markAsValidated($userId, $notes = null)
    {
        $this->update([
            'validation_status' => self::VALIDATION_STATUS_VALIDATED,
            'validated_by' => $userId,
            'validated_at' => now(),
            'validation_notes' => $notes,
        ]);
    }

    public function markAsApproved($userId, $notes = null)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_APPROVED,
            'validation_status' => self::VALIDATION_STATUS_APPROVED,
            'validated_by' => $userId,
            'validated_at' => now(),
            'validation_notes' => $notes,
        ]);
    }

    public function markAsRejected($userId, $notes = null)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_REJECTED,
            'validation_status' => self::VALIDATION_STATUS_REJECTED,
            'validated_by' => $userId,
            'validated_at' => now(),
            'validation_notes' => $notes,
        ]);
    }

    public function markAsReviewed($userId, $notes = null)
    {
        $this->update([
            'validation_status' => self::VALIDATION_STATUS_REVIEWED,
            'verified_by' => $userId,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    // ===== MÉTODOS DE AUDITORÍA =====

    public function close($userId)
    {
        $this->update([
            'closed_at' => now(),
            'closed_by' => $userId,
        ]);
    }

    public function reopen($userId)
    {
        $this->update([
            'reopened_at' => now(),
            'reopened_by' => $userId,
            'reopen_count' => $this->reopen_count + 1,
        ]);
    }

    // ===== MÉTODOS DE UTILIDAD =====

    /**
     * Calcula el tally (conteo con palotes) para visualización
     */
    public function getTallyAttribute(): string
    {
        $quantity = $this->quantity;
        $groups = floor($quantity / 5);
        $remaining = $quantity % 5;

        $tally = '';
        for ($i = 0; $i < $groups; $i++) {
            $tally .= '卌 ';
        }
        if ($remaining > 0) {
            $tally .= str_repeat('| ', $remaining);
        }
        return trim($tally);
    }

    /**
     * Versión visual mejorada del tally
     */
    public function getVisualTallyAttribute(): string
    {
        $quantity = $this->quantity;
        $groups = floor($quantity / 5);
        $remaining = $quantity % 5;

        $visual = '';
        for ($i = 0; $i < $groups; $i++) {
            $visual .= '<span class="tally-group bg-light p-1 rounded me-1">||||</span> ';
        }
        if ($remaining > 0) {
            $visual .= '<span class="tally-remaining">' . str_repeat('|', $remaining) . '</span>';
        }
        return trim($visual);
    }

    /**
     * Obtiene la categoría de elección (Alcalde/Concejal) a través del candidato
     */
    public function getElectionCategoryAttribute()
    {
        return $this->candidate?->electionCategory;
    }

    /**
     * Verifica si el voto es para Alcalde
     */
    public function isForMayor(): bool
    {
        $category = $this->getElectionCategoryAttribute();
        return $category && $category->code === 'ALC';
    }

    /**
     * Verifica si el voto es para Concejal
     */
    public function isForCouncil(): bool
    {
        $category = $this->getElectionCategoryAttribute();
        return $category && $category->code === 'CON';
    }

    /**
     * Obtiene el color del badge según el estado
     */
    public function getVoteStatusBadgeAttribute(): string
    {
        return match($this->vote_status) {
            self::VOTE_STATUS_PENDING => '<span class="badge bg-warning">Pendiente</span>',
            self::VOTE_STATUS_VERIFIED => '<span class="badge bg-info">Verificado</span>',
            self::VOTE_STATUS_OBSERVED => '<span class="badge bg-danger">Observado</span>',
            self::VOTE_STATUS_CORRECTED => '<span class="badge bg-primary">Corregido</span>',
            self::VOTE_STATUS_APPROVED => '<span class="badge bg-success">Aprobado</span>',
            self::VOTE_STATUS_REJECTED => '<span class="badge bg-dark">Rechazado</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }

    /**
     * Obtiene el color del badge según el estado de validación
     */
    public function getValidationStatusBadgeAttribute(): string
    {
        return match($this->validation_status) {
            self::VALIDATION_STATUS_PENDING => '<span class="badge bg-warning">Pendiente</span>',
            self::VALIDATION_STATUS_REVIEWED => '<span class="badge bg-info">Revisado</span>',
            self::VALIDATION_STATUS_OBSERVED => '<span class="badge bg-danger">Observado</span>',
            self::VALIDATION_STATUS_CORRECTED => '<span class="badge bg-primary">Corregido</span>',
            self::VALIDATION_STATUS_VALIDATED => '<span class="badge bg-success">Validado</span>',
            self::VALIDATION_STATUS_APPROVED => '<span class="badge bg-success">Aprobado</span>',
            self::VALIDATION_STATUS_REJECTED => '<span class="badge bg-dark">Rechazado</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }
}
