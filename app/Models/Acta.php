<?php
// app/Models/Acta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Acta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'actas';

    protected $fillable = [
        'code',
        'acta_number',
        'voting_table_id',
        'election_type_id',
        'user_id',
        'photo_path',
        'pdf_path',
        'original_filename',
        'total_votes',
        'blank_votes',
        'null_votes',
        'valid_votes',
        'status',
        'metadata',
        'hash',
        'file_size',
        'ocr_text',
        'ocr_data',
        'ocr_processed',
        'ocr_processed_at',
        'digital_signature',
        'is_signed',
        'signed_at',
        'signed_by',
    ];

    protected $casts = [
        'total_votes' => 'integer',
        'blank_votes' => 'integer',
        'null_votes' => 'integer',
        'valid_votes' => 'integer',
        'file_size' => 'integer',
        'metadata' => 'array',
        'ocr_data' => 'array',
        'ocr_processed' => 'boolean',
        'is_signed' => 'boolean',
        'ocr_processed_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    public function votingTable(): BelongsTo
    {
        return $this->belongsTo(VotingTable::class);
    }

    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}