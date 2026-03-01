<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'election_date',
        'start_time',
        'end_time',
        'registration_start',
        'registration_end',
        'campaign_start',
        'campaign_end',
        'total_voters',
        'total_tables',
        'total_recintos',
        'status',
        'active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'election_date' => 'date',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'campaign_start' => 'date',
        'campaign_end' => 'date',
        'active' => 'boolean',
        'total_voters' => 'integer',
        'total_tables' => 'integer',
        'total_recintos' => 'integer',
    ];

    public const STATUS_PREPARACION = 'preparacion';
    public const STATUS_INSCRIPCION = 'inscripcion';
    public const STATUS_CAMPANA = 'campana';
    public const STATUS_VOTACION = 'votacion';
    public const STATUS_COMPUTO = 'computo';
    public const STATUS_FINALIZADO = 'finalizado';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PREPARACION => 'Preparación',
            self::STATUS_INSCRIPCION => 'Inscripción',
            self::STATUS_CAMPANA => 'Campaña',
            self::STATUS_VOTACION => 'Votación',
            self::STATUS_COMPUTO => 'Cómputo',
            self::STATUS_FINALIZADO => 'Finalizado',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(ElectionCategory::class, 'election_type_categories')
            ->withPivot('votes_per_person', 'has_blank_vote', 'has_null_vote')
            ->withTimestamps();
    }
    public function typeCategories()
    {
        return $this->hasMany(ElectionTypeCategory::class);
    }
    public function votingTables()
    {
        return $this->hasMany(VotingTable::class);
    }
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        $colors = [
            self::STATUS_PREPARACION => 'secondary',
            self::STATUS_INSCRIPCION => 'info',
            self::STATUS_CAMPANA => 'primary',
            self::STATUS_VOTACION => 'warning',
            self::STATUS_COMPUTO => 'dark',
            self::STATUS_FINALIZADO => 'success',
        ];
        
        $color = $colors[$this->status] ?? 'secondary';
        $label = self::getStatuses()[$this->status] ?? $this->status;
        
        return "<span class='badge bg-{$color}'>{$label}</span>";
    }
}