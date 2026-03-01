<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'order',
        'ballot_position',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    public const POSITION_UNICA = 'unica';
    public const POSITION_SUPERIOR = 'superior';
    public const POSITION_INFERIOR = 'inferior';

    public static function getPositions(): array
    {
        return [
            self::POSITION_UNICA => 'Única',
            self::POSITION_SUPERIOR => 'Superior',
            self::POSITION_INFERIOR => 'Inferior',
        ];
    }
    
    public function electionTypes()
    {
        return $this->belongsToMany(ElectionType::class, 'election_type_categories')
            ->withPivot('votes_per_person', 'has_blank_vote', 'has_null_vote')
            ->withTimestamps();
    }
    public function typeCategories()
    {
        return $this->hasMany(ElectionTypeCategory::class);
    }
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}