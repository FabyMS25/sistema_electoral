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
        'municipality_id',
        'province_id',
        'department_id',
        'active',
    ];

    protected $casts = [
        'active'     => 'boolean',
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
            'id',
            'id',
            'election_type_category_id',
            'election_type_id'
        );
    }
    public function electionCategory()
    {
        return $this->hasOneThrough(
            ElectionCategory::class,
            ElectionTypeCategory::class,
            'id',
            'id',
            'election_type_category_id',
            'election_category_id'
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
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByElectionType($query, $electionTypeId)
    {
        return $query->whereHas('electionTypeCategory', function ($q) use ($electionTypeId) {
            $q->where('election_type_id', $electionTypeId);
        });
    }

    public function scopeByCategoryCode($query, $code)
    {
        return $query->whereHas('electionTypeCategory.electionCategory', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }

    public function scopeAlcaldes($query)
    {
        return $query->byCategoryCode('ALC');
    }

    public function scopeConcejales($query)
    {
        return $query->byCategoryCode('CON');
    }

    public function scopeGobernadores($query)
    {
        return $query->byCategoryCode('GOB');
    }

    // ===== HELPERS =====
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('build/images/default-candidate.jpg');
    }
    public function getPartyLogoUrlAttribute(): string
    {
        return $this->party_logo
            ? asset('storage/' . $this->party_logo)
            : asset('build/images/default-party.png');
    }
    public function getElectionTypeNameAttribute(): string
    {
        return $this->electionTypeCategory?->electionType?->name ?? 'N/A';
    }
    public function getElectionCategoryNameAttribute(): string
    {
        return $this->electionTypeCategory?->electionCategory?->name ?? 'N/A';
    }
    public function getElectionCategoryCodeAttribute(): string
    {
        return $this->electionTypeCategory?->electionCategory?->code ?? 'N/A';
    }
    public function getCategoryAttribute()
    {
        return $this->electionTypeCategory?->electionCategory;
    }
    public function getTotalVotesAttribute(): int
    {
        return $this->votes()->sum('quantity');
    }
}
