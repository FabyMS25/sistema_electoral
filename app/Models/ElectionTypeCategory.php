<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionTypeCategory extends Model
{
    use HasFactory;

    protected $table = 'election_type_categories';

    protected $fillable = [
        'election_type_id',
        'election_category_id',
        'ballot_order',
        'votes_per_person',
        'has_blank_vote',
        'has_null_vote',
    ];

    protected $casts = [
        'ballot_order'     => 'integer',
        'votes_per_person' => 'integer',
        'has_blank_vote'   => 'boolean',
        'has_null_vote'    => 'boolean',
    ];

    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class);
    }

    public function electionCategory(): BelongsTo
    {
        return $this->belongsTo(ElectionCategory::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function categoryResults(): HasMany
    {
        return $this->hasMany(VotingTableCategoryResult::class);
    }

    public function actaCategoryResults(): HasMany
    {
        return $this->hasMany(ActaCategoryResult::class);
    }

    public function allowsBlankVote(): bool
    {
        return $this->has_blank_vote;
    }

    public function allowsNullVote(): bool
    {
        return $this->has_null_vote;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->electionType?->name} — {$this->electionCategory?->name}";
    }

    public function getFranjaLabelAttribute(): string
    {
        return "Franja {$this->ballot_order}: {$this->electionCategory?->name}";
    }
}
