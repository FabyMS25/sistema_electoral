<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionTypeCategory extends Model
{
    use HasFactory;

    protected $table = 'election_type_categories';

    protected $fillable = [
        'election_type_id',
        'election_category_id',
        'votes_per_person',
        'has_blank_vote',
        'has_null_vote',
    ];

    protected $casts = [
        'votes_per_person' => 'integer',
        'has_blank_vote' => 'boolean',
        'has_null_vote' => 'boolean',
    ];

    // ===== RELACIONES =====
    
    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class);
    }

    public function electionCategory(): BelongsTo
    {
        return $this->belongsTo(ElectionCategory::class);
    }

    // ===== MÉTODOS DE AYUDA =====
    
    /**
     * Verifica si esta categoría permite voto en blanco
     */
    public function allowsBlankVote(): bool
    {
        return $this->has_blank_vote;
    }

    /**
     * Verifica si esta categoría permite voto nulo
     */
    public function allowsNullVote(): bool
    {
        return $this->has_null_vote;
    }

    /**
     * Obtiene el nombre de la categoría con el tipo de elección
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->electionType->name} - {$this->electionCategory->name}";
    }
}