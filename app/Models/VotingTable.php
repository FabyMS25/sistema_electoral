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
        'expected_voters' => 'integer',
        'number'          => 'integer',
    ];

    public const TYPE_MASCULINA = 'masculina';
    public const TYPE_FEMENINA  = 'femenina';
    public const TYPE_MIXTA     = 'mixta';

    // ─── Relationships ────────────────────────────────────────────────────────

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function elections(): HasMany
    {
        return $this->hasMany(VotingTableElection::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
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

    public function municipality()
    {
        return $this->hasOneThrough(
            Municipality::class,
            Institution::class,
            'id',
            'id',
            'institution_id',
            'municipality_id'
        );
    }

    // ─── Accessors for view compatibility ───────────────────────────────────

    public function getStatusAttribute(): ?string
    {
        $latest = $this->elections()->latest('updated_at')->first();
        return $latest?->status;
    }

    public function getTotalVotersAttribute(): int
    {
        return $this->elections()->sum('total_voters');
    }

    public function getBallotsReceivedAttribute(): int
    {
        return $this->elections()->sum('ballots_received');
    }

    public function getBallotsUsedAttribute(): int
    {
        return $this->elections()->sum('ballots_used');
    }

    public function getBallotsLeftoverAttribute(): int
    {
        return $this->elections()->sum('ballots_leftover');
    }

    public function getBallotsSpoiledAttribute(): int
    {
        return $this->elections()->sum('ballots_spoiled');
    }

    public function getOpeningTimeAttribute(): ?string
    {
        $latest = $this->elections()->latest('updated_at')->first();
        return $latest?->opening_time;
    }

    public function getClosingTimeAttribute(): ?string
    {
        $latest = $this->elections()->latest('updated_at')->first();
        return $latest?->closing_time;
    }

    public function getElectionDateAttribute(): ?string
    {
        $latest = $this->elections()->latest('updated_at')->first();
        return $latest?->election_date;
    }

    public function getElectionTypeAttribute()
    {
        $latest = $this->elections()->with('electionType')->latest('updated_at')->first();
        return $latest?->electionType;
    }

    // Temporary for backward compatibility with old views
    public function getValidVotesAttribute(): int
    {
        return 0; // This should be calculated from category results
    }

    public function getBlankVotesAttribute(): int
    {
        return 0;
    }

    public function getNullVotesAttribute(): int
    {
        return 0;
    }

    public function getValidVotesSecondAttribute(): int
    {
        return 0;
    }

    public function getBlankVotesSecondAttribute(): int
    {
        return 0;
    }

    public function getNullVotesSecondAttribute(): int
    {
        return 0;
    }

    public function getTotalVotersSecondAttribute(): int
    {
        return 0;
    }

    public function getActaNumberAttribute(): ?string
    {
        return null;
    }

    public function getActaUploadedAtAttribute(): ?string
    {
        return null;
    }

    public function getActaPhotoAttribute(): ?string
    {
        return null;
    }

    public function getVocal4NameAttribute(): ?string
    {
        return $this->vocal4?->name;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForElections($query)
    {
        return $query->whereHas('institution', fn($q) =>
            $q->where('status', 'activo')->where('is_operative', true)
        );
    }

    public function scopeByInstitution($query, int $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function electionStatus(int $electionTypeId): ?VotingTableElection
    {
        return $this->elections()->where('election_type_id', $electionTypeId)->first();
    }

    public function getFullCodeAttribute(): string
    {
        return $this->letter
            ? "{$this->number}{$this->letter}"
            : (string) $this->number;
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_MASCULINA => 'Masculina',
            self::TYPE_FEMENINA  => 'Femenina',
            default              => 'Mixta',
        };
    }

    public static function getStatuses(): array
    {
        return [
            'configurada'   => 'Configurada',
            'en_espera'     => 'En Espera',
            'votacion'      => 'En Votación',
            'cerrada'       => 'Cerrada',
            'en_escrutinio' => 'En Escrutinio',
            'escrutada'     => 'Escrutada',
            'observada'     => 'Observada',
            'transmitida'   => 'Transmitida',
            'anulada'       => 'Anulada',
        ];
    }
}
