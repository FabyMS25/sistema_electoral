<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElectionType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'level',
        'geographic_scope_type',
        'geographic_scope_id',
        'election_date',
        'start_time',
        'end_time',
        'active',
        'description',
    ];

    protected $casts = [
        'election_date' => 'date',
        'active'        => 'boolean',
    ];

    public const LEVEL_NACIONAL      = 'nacional';
    public const LEVEL_DEPARTAMENTAL = 'departamental';  // 3-franja ballot
    public const LEVEL_MUNICIPAL     = 'municipal';       // 2-franja ballot
    public const LEVEL_REGIONAL      = 'regional';
    public const LEVEL_INDIGENA_IOC  = 'indigena_ioc';

    public static function getLevels(): array
    {
        return [
            self::LEVEL_NACIONAL      => 'Nacional',
            self::LEVEL_DEPARTAMENTAL => 'Departamental',
            self::LEVEL_MUNICIPAL     => 'Municipal',
            self::LEVEL_REGIONAL      => 'Regional',
            self::LEVEL_INDIGENA_IOC  => 'Indígena IOC',
        ];
    }


    public function geographicScope(): MorphTo
    {
        return $this->morphTo('geographic_scope');
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ElectionCategory::class, 'election_type_categories')
            ->withPivot([
                'ballot_order',
                'votes_per_person',
                'has_blank_vote',
                'has_null_vote',
            ])
            ->orderByPivot('ballot_order')
            ->withTimestamps();
    }

    public function typeCategories(): HasMany
    {
        return $this->hasMany(ElectionTypeCategory::class);
    }

    public function votingTableElections(): HasMany
    {
        return $this->hasMany(VotingTableElection::class);
    }

    public function votingTables()
    {
        return $this->hasManyThrough(
            VotingTable::class,
            VotingTableElection::class,
            'election_type_id',  // FK on voting_table_elections
            'id',                // PK on voting_tables
            'id',                // PK on election_types
            'voting_table_id'    // FK on voting_table_elections
        );
    }

    public function candidates()
    {
        return $this->hasManyThrough(
            Candidate::class,
            ElectionTypeCategory::class,
            'election_type_id',
            'election_type_category_id'
        );
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

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('election_date', $date);
    }

    public function scopeMunicipal($query)
    {
        return $query->where('level', self::LEVEL_MUNICIPAL);
    }

    public function scopeDepartamental($query)
    {
        return $query->where('level', self::LEVEL_DEPARTAMENTAL);
    }

    public function isMunicipal(): bool
    {
        return $this->level === self::LEVEL_MUNICIPAL;
    }

    public function isDepartamental(): bool
    {
        return $this->level === self::LEVEL_DEPARTAMENTAL;
    }

    public function getFranjaCountAttribute(): int
    {
        return $this->typeCategories()->count();
    }

    public function getLevelLabelAttribute(): string
    {
        return self::getLevels()[$this->level] ?? $this->level;
    }
}
