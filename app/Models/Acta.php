<?php
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
        'has_physical_acta'
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
        'has_physical_acta' => 'boolean',
        'ocr_processed_at' => 'datetime',
        'signed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_consistent' => 'boolean',
    ];

    const STATUS_UPLOADED = 'uploaded';
    const STATUS_VERIFIED = 'verified';
    const STATUS_OBSERVED = 'observed';
    const STATUS_CORRECTED = 'corrected';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_UPLOADED => 'Subida',
            self::STATUS_VERIFIED => 'Verificada',
            self::STATUS_OBSERVED => 'Observada',
            self::STATUS_CORRECTED => 'Corregida',
            self::STATUS_APPROVED => 'Aprobada',
            self::STATUS_REJECTED => 'Rechazada',
        ];
    }

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
    
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_UPLOADED => '<span class="badge bg-info">Subida</span>',
            self::STATUS_VERIFIED => '<span class="badge bg-success">Verificada</span>',
            self::STATUS_OBSERVED => '<span class="badge bg-warning">Observada</span>',
            self::STATUS_CORRECTED => '<span class="badge bg-primary">Corregida</span>',
            self::STATUS_APPROVED => '<span class="badge bg-success">Aprobada</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">Rechazada</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}