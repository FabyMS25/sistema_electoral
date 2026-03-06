<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * VotingTable — the PHYSICAL mesa only.
 *
 * Has NO status, NO vote totals, NO ballot counts.
 * All per-election state lives in VotingTableElection (one row per mesa × election).
 * All per-franja totals live in VotingTableCategoryResult.
 */
class VotingTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'voting_tables';

    protected $fillable = [
        'oep_code',
        'internal_code',
        'number',
        'letter',
        'type',
        'institution_id',
        'expected_voters',
        'voter_range_start_name',
        'voter_range_end_name',
        'president_id',
        'secretary_id',
        'vocal1_id',
        'vocal2_id',
        'vocal3_id',
        'vocal4_id',
        'observations',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'number'          => 'integer',
        'expected_voters' => 'integer',
    ];

    // Mesa type constants — the only constants that belong here
    public const TYPE_MIXTA     = 'mixta';
    public const TYPE_MASCULINA = 'masculina';
    public const TYPE_FEMENINA  = 'femenina';

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * All per-election pivot rows for this mesa.
     * Use forElection() to get a specific one.
     */
    public function tableElections(): HasMany
    {
        return $this->hasMany(VotingTableElection::class);
    }

    /**
     * Shortcut: get election types this mesa participates in via pivot.
     */
    public function electionTypes()
    {
        return $this->belongsToMany(ElectionType::class, 'voting_table_elections')
            ->withPivot([
                'status',
                'ballots_received',
                'ballots_used',
                'ballots_leftover',
                'ballots_spoiled',
                'total_voters',
                'election_date',
                'opening_time',
                'closing_time',
                'observations',
            ])
            ->withTimestamps();
    }

    /**
     * Per-franja aggregate results (valid + blank + null per category).
     */
    public function categoryResults(): HasMany
    {
        return $this->hasMany(VotingTableCategoryResult::class);
    }

    /**
     * Individual candidate vote rows for this mesa.
     */
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

    public function assignments(): HasMany
    {
        return $this->hasMany(UserAssignment::class, 'voting_table_id');
    }

    // ── Mesa members ──────────────────────────────────────────────────────────

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

    // ── Audit ─────────────────────────────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =========================================================================
    // HELPERS — physical mesa queries, no status assumptions
    // =========================================================================

    /**
     * Get the VotingTableElection pivot row for a specific election.
     * This is where status, ballots, and timing live.
     */
    public function forElection(int $electionTypeId): ?VotingTableElection
    {
        return $this->tableElections()
            ->where('election_type_id', $electionTypeId)
            ->first();
    }

    /**
     * Get all category results for a specific election type.
     */
    public function categoryResultsForElection(int $electionTypeId)
    {
        return $this->categoryResults()
            ->whereHas('electionTypeCategory', function ($q) use ($electionTypeId) {
                $q->where('election_type_id', $electionTypeId);
            })
            ->get();
    }

    /**
     * True if every franja result for the given election is consistent.
     */
    public function isConsistentForElection(int $electionTypeId): bool
    {
        return $this->categoryResultsForElection($electionTypeId)
            ->every(fn($r) => $r->is_consistent);
    }

    /**
     * Active delegates assigned to this mesa.
     */
    public function delegates()
    {
        return $this->assignments()
            ->whereIn('delegate_type', ['delegado_mesa', 'presidente', 'secretario', 'vocal'])
            ->where('status', 'activo');
    }

    /**
     * True if this mesa has any unresolved observations.
     */
    public function hasPendingObservations(): bool
    {
        return $this->observations()
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Participation % based on the election with the highest voter turnout.
     * Use forElection($id)->participationPercentage for election-specific value.
     */
    public function getParticipationPercentageAttribute(): float
    {
        if ($this->expected_voters === 0) return 0.0;

        $maxVoters = $this->tableElections()->max('total_voters') ?? 0;

        return round(($maxVoters / $this->expected_voters) * 100, 2);
    }

    /**
     * Absent voters based on the election with the highest turnout.
     */
    public function getAbsentVotersAttribute(): int
    {
        $maxVoters = $this->tableElections()->max('total_voters') ?? 0;

        return max(0, $this->expected_voters - $maxVoters);
    }

    // =========================================================================
    // DISPLAY HELPERS
    // =========================================================================

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_MIXTA     => 'Mixta',
            self::TYPE_MASCULINA => 'Masculina',
            self::TYPE_FEMENINA  => 'Femenina',
            default              => $this->type,
        };
    }

    public function getFullCodeAttribute(): string
    {
        return $this->internal_code ?? $this->oep_code;
    }
}
