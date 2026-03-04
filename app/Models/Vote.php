<?php
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
        'validated_at' => 'datetime',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
        'reopen_count' => 'integer',
        'has_physical_acta' => 'boolean',
    ];

    const VOTE_STATUS_PENDING_REVIEW = 'pending_review';
    const VOTE_STATUS_REVIEWED = 'reviewed';
    const VOTE_STATUS_OBSERVED = 'observed';
    const VOTE_STATUS_CORRECTED = 'corrected';
    const VOTE_STATUS_VALIDATED = 'validated';
    const VOTE_STATUS_APPROVED = 'approved';
    const VOTE_STATUS_REJECTED = 'rejected';

    public static function getVoteStatuses(): array
    {
        return [
            self::VOTE_STATUS_PENDING_REVIEW => 'Pendiente de Revisión',
            self::VOTE_STATUS_REVIEWED => 'Revisado',
            self::VOTE_STATUS_OBSERVED => 'Observado',
            self::VOTE_STATUS_CORRECTED => 'Corregido',
            self::VOTE_STATUS_VALIDATED => 'Validado',
            self::VOTE_STATUS_APPROVED => 'Aprobado',
            self::VOTE_STATUS_REJECTED => 'Rechazado',
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
    public function validationHistory()
    {
        return $this->hasMany(ValidationHistory::class);
    }

    // ===== MÉTODOS DE ESTADO =====
    public function isPendingReview(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_PENDING_REVIEW;
    }
    public function isReviewed(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_REVIEWED;
    }
    public function isObserved(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_OBSERVED;
    }
    public function isCorrected(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_CORRECTED;
    }
    public function isValidated(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_VALIDATED;
    }
    public function isApproved(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_APPROVED;
    }
    public function isRejected(): bool
    {
        return $this->vote_status === self::VOTE_STATUS_REJECTED;
    }
    public function canBeCorrected(): bool
    {
        return in_array($this->vote_status, [
            self::VOTE_STATUS_OBSERVED,
            self::VOTE_STATUS_PENDING_REVIEW
        ]);
    }

    // ===== SCOPES =====
    public function scopePendingReview($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_PENDING_REVIEW);
    }
    public function scopeReviewed($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_REVIEWED);
    }
    public function scopeObserved($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_OBSERVED);
    }
    public function scopeCorrected($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_CORRECTED);
    }
    public function scopeValidated($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_VALIDATED);
    }
    public function scopeApproved($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_APPROVED);
    }
    public function scopeRejected($query)
    {
        return $query->where('vote_status', self::VOTE_STATUS_REJECTED);
    }
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

    // ===== MÉTODOS DE ACCIÓN =====
    public function markAsObserved($userId, $observationId, $notes = null)
    {
        $oldStatus = $this->vote_status;
        $this->update([
            'vote_status' => self::VOTE_STATUS_OBSERVED,
            'observation_id' => $observationId,
            'verification_notes' => $notes,
            'verified_by' => $userId,
            'verified_at' => now(),
        ]);

        ValidationHistory::create([
            'vote_id' => $this->id,
            'user_id' => $userId,
            'action' => 'observe',
            'notes' => $notes,
            'previous_values' => ['status' => $oldStatus],
            'new_values' => ['status' => self::VOTE_STATUS_OBSERVED],
        ]);

        if ($this->votingTable) {
            $this->votingTable->updateValidationStatus();
        }
    }

    public function markAsCorrected($userId, $newQuantity, $notes = null)
    {
        $oldQuantity = $this->quantity;
        $oldStatus = $this->vote_status;

        $this->update([
            'quantity' => $newQuantity,
            'vote_status' => self::VOTE_STATUS_CORRECTED,
            'corrected_by' => $userId,
            'corrected_at' => now(),
            'correction_notes' => $notes,
        ]);

        ValidationHistory::create([
            'vote_id' => $this->id,
            'user_id' => $userId,
            'action' => 'correct',
            'notes' => $notes,
            'previous_values' => ['quantity' => $oldQuantity, 'status' => $oldStatus],
            'new_values' => ['quantity' => $newQuantity, 'status' => self::VOTE_STATUS_CORRECTED],
        ]);

        if ($this->votingTable) {
            $this->votingTable->updateValidationStatus();
        }
    }

    public function markAsValidated($userId, $notes = null)
    {
        $oldStatus = $this->vote_status;
        $this->update([
            'vote_status' => self::VOTE_STATUS_VALIDATED,
            'validated_by' => $userId,
            'validated_at' => now(),
            'validation_notes' => $notes,
        ]);

        ValidationHistory::create([
            'vote_id' => $this->id,
            'user_id' => $userId,
            'action' => 'validate',
            'notes' => $notes,
            'previous_values' => ['status' => $oldStatus],
            'new_values' => ['status' => self::VOTE_STATUS_VALIDATED],
        ]);

        if ($this->votingTable) {
            $this->votingTable->updateValidationStatus();
        }
    }

    public function markAsApproved($userId, $notes = null)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_APPROVED,
            'validated_by' => $userId,
            'validated_at' => now(),
            'validation_notes' => $notes,
        ]);

        ValidationHistory::create([
            'vote_id' => $this->id,
            'user_id' => $userId,
            'action' => 'approve',
            'notes' => $notes,
        ]);

        if ($this->votingTable) {
            $this->votingTable->updateValidationStatus();
        }
    }

    public function markAsRejected($userId, $notes = null)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_REJECTED,
            'validated_by' => $userId,
            'validated_at' => now(),
            'validation_notes' => $notes,
        ]);

        ValidationHistory::create([
            'vote_id' => $this->id,
            'user_id' => $userId,
            'action' => 'reject',
            'notes' => $notes,
        ]);

        if ($this->votingTable) {
            $this->votingTable->updateValidationStatus();
        }
    }

    public function markAsReviewed($userId, $notes = null)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_REVIEWED,
            'verified_by' => $userId,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    public function markAsVerified($userId, $notes = null)
    {
        $this->update([
            'vote_status' => self::VOTE_STATUS_REVIEWED,
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

    // ===== MÉTODOS DE UTILIDAD (se mantienen) =====
    public function getElectionCategoryAttribute()
    {
        return $this->candidate?->electionCategory;
    }

    public function getElectionCategoryCodeAttribute(): ?string
    {
        return $this->getElectionCategoryAttribute()?->code;
    }

    public function isForMayor(): bool
    {
        $category = $this->getElectionCategoryAttribute();
        return $category && $category->code === 'ALC';
    }

    public function isForCouncil(): bool
    {
        $category = $this->getElectionCategoryAttribute();
        return $category && $category->code === 'CON';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->vote_status) {
            self::VOTE_STATUS_PENDING_REVIEW => '<span class="badge bg-warning">Pendiente</span>',
            self::VOTE_STATUS_REVIEWED => '<span class="badge bg-info">Revisado</span>',
            self::VOTE_STATUS_OBSERVED => '<span class="badge bg-danger">Observado</span>',
            self::VOTE_STATUS_CORRECTED => '<span class="badge bg-primary">Corregido</span>',
            self::VOTE_STATUS_VALIDATED => '<span class="badge bg-success">Validado</span>',
            self::VOTE_STATUS_APPROVED => '<span class="badge bg-success">Aprobado</span>',
            self::VOTE_STATUS_REJECTED => '<span class="badge bg-dark">Rechazado</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }
}
