<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotingTableCategoryResult extends Model
{
    use HasFactory;

    protected $table = 'voting_table_category_results';

    protected $fillable = [
        'voting_table_id',
        'election_type_category_id',
        'valid_votes',
        'blank_votes',
        'null_votes',
        'total_votes',
        'is_consistent',
        'inconsistencies',
        'status',
        'entered_by',
        'entered_at',
        'validated_by',
        'validated_at',
        'notes',
    ];

    protected $casts = [
        'valid_votes'     => 'integer',
        'blank_votes'     => 'integer',
        'null_votes'      => 'integer',
        'total_votes'     => 'integer',
        'is_consistent'   => 'boolean',
        'inconsistencies' => 'array',
        'entered_at'      => 'datetime',
        'validated_at'    => 'datetime',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_ENTERED   = 'entered';
    public const STATUS_REVIEWED  = 'reviewed';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_OBSERVED  = 'observed';
    public const STATUS_CORRECTED = 'corrected';
    public const STATUS_CLOSED    = 'closed';

    public function votingTable(): BelongsTo
    {
        return $this->belongsTo(VotingTable::class);
    }

    public function electionTypeCategory(): BelongsTo
    {
        return $this->belongsTo(ElectionTypeCategory::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // =========================================================================
    // CONSISTENCY LOGIC
    // =========================================================================

    /**
     * Runs all three consistency rules and persists the result.
     * Call this after any vote quantity change for this franja+mesa.
     *
     * Rule 1: valid + blank + null == total_votes
     * Rule 2: total_votes <= mesa expected_voters
     * Rule 3: sum of candidate Vote.quantity == valid_votes
     *
     * @return bool  True if all rules pass.
     */
    public function checkConsistency(): bool
    {
        $inconsistencies = [];

        // Rule 1: totals must add up
        $sumCheck = $this->valid_votes + $this->blank_votes + $this->null_votes;
        if ($sumCheck !== $this->total_votes) {
            $inconsistencies[] = "Suma de votos ({$sumCheck}) ≠ total ({$this->total_votes})";
        }

        // Rule 2: total must not exceed registered voters on this mesa
        $expected = $this->votingTable->expected_voters;
        if ($this->total_votes > $expected) {
            $inconsistencies[] = "Total votos ({$this->total_votes}) excede habilitados ({$expected})";
        }

        // Rule 3: candidate vote sum must equal valid_votes
        $candidateSum = Vote::where('voting_table_id', $this->voting_table_id)
            ->where('election_type_category_id', $this->election_type_category_id)
            ->sum('quantity');

        if ($candidateSum !== $this->valid_votes) {
            $inconsistencies[] = "Suma candidatos ({$candidateSum}) ≠ votos válidos ({$this->valid_votes})";
        }

        $this->is_consistent   = empty($inconsistencies);
        $this->inconsistencies = empty($inconsistencies) ? null : $inconsistencies;
        $this->save();

        return $this->is_consistent;
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->electionTypeCategory?->electionCategory?->name ?? 'N/A';
    }

    public function getCategoryCodeAttribute(): string
    {
        return $this->electionTypeCategory?->electionCategory?->code ?? 'N/A';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => '<span class="badge bg-secondary">Pendiente</span>',
            self::STATUS_ENTERED   => '<span class="badge bg-info">Ingresado</span>',
            self::STATUS_REVIEWED  => '<span class="badge bg-primary">Revisado</span>',
            self::STATUS_VALIDATED => '<span class="badge bg-success">Validado</span>',
            self::STATUS_OBSERVED  => '<span class="badge bg-danger">Observado</span>',
            self::STATUS_CORRECTED => '<span class="badge bg-warning">Corregido</span>',
            self::STATUS_CLOSED    => '<span class="badge bg-dark">Cerrado</span>',
            default                => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }
}
