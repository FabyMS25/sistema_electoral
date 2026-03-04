<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'party',
        'party_full_name',
        'party_logo',
        'photo',
        'color',
        'election_type_category_id',
        'list_order',
        'list_name',
        'type',
        'municipality_id',
        'province_id',
        'department_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'list_order' => 'integer',
    ];

    public function electionTypeCategory()
    {
        return $this->belongsTo(ElectionTypeCategory::class);
    }

    public function electionType()
    {
        return $this->hasOneThrough(
            ElectionType::class,
            ElectionTypeCategory::class,
            'id', // Foreign key on election_type_categories table
            'id', // Foreign key on election_types table
            'election_type_category_id', // Local key on candidates table
            'election_type_id' // Local key on election_type_categories table
        );
    }

    public function electionCategory()
    {
        return $this->hasOneThrough(
            ElectionCategory::class,
            ElectionTypeCategory::class,
            'id', // Foreign key on election_type_categories table
            'id', // Foreign key on election_categories table
            'election_type_category_id', // Local key on candidates table
            'election_category_id' // Local key on election_type_categories table
        );
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function scopeBlankVotes($query)
    {
        return $query->where('type', 'blank_votes');
    }

    public function scopeNullVotes($query)
    {
        return $query->where('type', 'null_votes');
    }
    public function scopeRealCandidates($query)
    {
        return $query->where('type', 'candidato');
    }
    public function isBlankVote(): bool
    {
        return $this->type === 'blank_votes';
    }
    public function isNullVote(): bool
    {
        return $this->type === 'null_votes';
    }
    public function isRealCandidate(): bool
    {
        return $this->type === 'candidato';
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : asset('build/images/default-candidate.jpg');
    }

    public function getPartyLogoUrlAttribute()
    {
        return $this->party_logo ? asset('storage/' . $this->party_logo) : asset('build/images/default-party.png');
    }

    public function getElectionTypeNameAttribute()
    {
        return $this->electionTypeCategory?->electionType?->name ?? 'N/A';
    }

    public function getElectionCategoryNameAttribute()
    {
        return $this->electionTypeCategory?->electionCategory?->name ?? 'N/A';
    }

    public function getElectionCategoryCodeAttribute()
    {
        return $this->electionTypeCategory?->electionCategory?->code ?? 'N/A';
    }

    public function getCategoryAttribute()
    {
        return $this->electionTypeCategory?->electionCategory;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function scopeByElectionType($query, $electionTypeId)
    {
        return $query->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
            $q->where('election_type_id', $electionTypeId);
        });
    }
    public function scopeByCategoryCode($query, $code)
    {
        return $query->whereHas('electionTypeCategory.electionCategory', function($q) use ($code) {
            $q->where('code', $code);
        });
    }
    public function scopeAlcaldes($query)
    {
        return $query->whereHas('electionTypeCategory.electionCategory', function($q) {
            $q->where('code', 'ALC');
        });
    }
    public function scopeConcejales($query)
    {
        return $query->whereHas('electionTypeCategory.electionCategory', function($q) {
            $q->where('code', 'CON');
        });
    }
}
