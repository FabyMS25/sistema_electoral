<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ElectionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_order',
        'geographic_scope',
        'allows_list',
        'active',
    ];

    protected $casts = [
        'active'        => 'boolean',
        'allows_list'   => 'boolean',
        'default_order' => 'integer',
    ];

    public const SCOPE_NACIONAL      = 'nacional';
    public const SCOPE_DEPARTAMENTAL = 'departamental';
    public const SCOPE_PROVINCIAL    = 'provincial';
    public const SCOPE_MUNICIPAL     = 'municipal';
    public const SCOPE_INDIGENA_IOC  = 'indigena_ioc';

    // Category codes — match what's in your seeder
    public const CODE_GOBERNADOR = 'GOB';
    public const CODE_ASM_TERRITORIO = 'AST';
    public const CODE_ASM_POBLACION  = 'ASP';
    public const CODE_ALCALDE    = 'ALC';
    public const CODE_CONCEJAL   = 'CON';

    public static function getScopes(): array
    {
        return [
            self::SCOPE_NACIONAL      => 'Nacional',
            self::SCOPE_DEPARTAMENTAL => 'Departamental',
            self::SCOPE_PROVINCIAL    => 'Provincial',
            self::SCOPE_MUNICIPAL     => 'Municipal',
            self::SCOPE_INDIGENA_IOC  => 'Indígena IOC',
        ];
    }

    public function electionTypes(): BelongsToMany
    {
        return $this->belongsToMany(ElectionType::class, 'election_type_categories')
            ->withPivot([
                'ballot_order',
                'votes_per_person',
                'has_blank_vote',
                'has_null_vote',
            ])
            ->withTimestamps();
    }

    public function typeCategories(): HasMany
    {
        return $this->hasMany(ElectionTypeCategory::class);
    }

    public function candidates(): HasManyThrough
    {
        return $this->hasManyThrough(
            Candidate::class,
            ElectionTypeCategory::class,
            'election_category_id',
            'election_type_category_id'
        );
    }

    public function isMunicipal(): bool
    {
        return $this->geographic_scope === self::SCOPE_MUNICIPAL;
    }

    public function isDepartamental(): bool
    {
        return $this->geographic_scope === self::SCOPE_DEPARTAMENTAL;
    }

    public function isListBased(): bool
    {
        return $this->allows_list;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByScope($query, string $scope)
    {
        return $query->where('geographic_scope', $scope);
    }
}
