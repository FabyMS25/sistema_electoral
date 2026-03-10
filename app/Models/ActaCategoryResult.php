<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActaCategoryResult extends Model
{
    use HasFactory;

    protected $table = 'acta_category_results';

    protected $fillable = [
        'acta_id',
        'election_type_category_id',
        'valid_votes',
        'blank_votes',
        'null_votes',
        'total_votes',
        'matches_digital',
        'discrepancies',
        'ocr_valid_votes',
        'ocr_blank_votes',
        'ocr_null_votes',
        'ocr_confidence',
    ];

    protected $casts = [
        'valid_votes'      => 'integer',
        'blank_votes'      => 'integer',
        'null_votes'       => 'integer',
        'total_votes'      => 'integer',
        'matches_digital'  => 'boolean',
        'discrepancies'    => 'array',
        'ocr_valid_votes'  => 'integer',
        'ocr_blank_votes'  => 'integer',
        'ocr_null_votes'   => 'integer',
        'ocr_confidence'   => 'float',
    ];

    public function acta(): BelongsTo
    {
        return $this->belongsTo(Acta::class);
    }
    public function electionTypeCategory(): BelongsTo
    {
        return $this->belongsTo(ElectionTypeCategory::class);
    }
    public function getCategoryNameAttribute(): string
    {
        return $this->electionTypeCategory?->electionCategory?->name ?? 'N/A';
    }
    public function isInternallyConsistent(): bool
    {
        return ($this->valid_votes + $this->blank_votes + $this->null_votes) === $this->total_votes;
    }
    public function hasOcrDiscrepancy(): bool
    {
        if ($this->ocr_valid_votes === null) return false;
        return $this->ocr_valid_votes !== $this->valid_votes
            || $this->ocr_blank_votes !== $this->blank_votes
            || $this->ocr_null_votes  !== $this->null_votes;
    }
}
