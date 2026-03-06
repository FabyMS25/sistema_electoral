<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public const TYPE_MIXTA     = 'mixta';
    public const TYPE_MASCULINA = 'masculina';
    public const TYPE_FEMENINA  = 'femenina';

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function tableElections(): HasMany
    {
        return $this->hasMany(VotingTableElection::class);
    }

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

    public function categoryResults(): HasMany
    {
        return $this->hasMany(VotingTableCategoryResult::class);
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
    public function assignments(): HasMany
    {
        return $this->hasMany(UserAssignment::class, 'voting_table_id');
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
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function forElection(int $electionTypeId): ?VotingTableElection
    {
        return $this->tableElections()
            ->where('election_type_id', $electionTypeId)
            ->first();
    }
    public function categoryResultsForElection(int $electionTypeId)
    {
        return $this->categoryResults()
            ->whereHas('electionTypeCategory', function ($q) use ($electionTypeId) {
                $q->where('election_type_id', $electionTypeId);
            })
            ->get();
    }
    public function isConsistentForElection(int $electionTypeId): bool
    {
        return $this->categoryResultsForElection($electionTypeId)
            ->every(fn($r) => $r->is_consistent);
    }
    public function delegates()
    {
        return $this->assignments()
            ->whereIn('delegate_type', ['delegado_mesa', 'presidente', 'secretario', 'vocal'])
            ->where('status', 'activo');
    }
    public function hasPendingObservations(): bool
    {
        return $this->observations()
            ->where('status', 'pending')
            ->exists();
    }

    public function getParticipationPercentageAttribute(): float
    {
        if ($this->expected_voters === 0) return 0.0;

        $maxVoters = $this->tableElections()->max('total_voters') ?? 0;

        return round(($maxVoters / $this->expected_voters) * 100, 2);
    }

    public function getAbsentVotersAttribute(): int
    {
        $maxVoters = $this->tableElections()->max('total_voters') ?? 0;

        return max(0, $this->expected_voters - $maxVoters);
    }

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
