<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'is_consistent',
        'inconsistencies',
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
        'file_size'        => 'integer',
        'metadata'         => 'array',
        'ocr_data'         => 'array',
        'inconsistencies'  => 'array',
        'ocr_processed'    => 'boolean',
        'is_signed'        => 'boolean',
        'is_consistent'    => 'boolean',
        'ocr_processed_at' => 'datetime',
        'signed_at'        => 'datetime',
    ];

    public const STATUS_UPLOADED  = 'uploaded';
    public const STATUS_VERIFIED  = 'verified';
    public const STATUS_OBSERVED  = 'observed';
    public const STATUS_CORRECTED = 'corrected';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_UPLOADED  => 'Subida',
            self::STATUS_VERIFIED  => 'Verificada',
            self::STATUS_OBSERVED  => 'Observada',
            self::STATUS_CORRECTED => 'Corregida',
            self::STATUS_APPROVED  => 'Aprobada',
            self::STATUS_REJECTED  => 'Rechazada',
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

    public function categoryResults(): HasMany
    {
        return $this->hasMany(ActaCategoryResult::class);
    }

    public function verifyConsistency(): bool
    {
        $inconsistencies = [];

        foreach ($this->categoryResults as $actaResult) {
            $digitalResult = VotingTableCategoryResult::where('voting_table_id', $this->voting_table_id)
                ->where('election_type_category_id', $actaResult->election_type_category_id)
                ->first();

            if (!$digitalResult) {
                $inconsistencies[] = "Sin datos digitales para franja ID {$actaResult->election_type_category_id}";
                $actaResult->update(['matches_digital' => false]);
                continue;
            }

            $match = $actaResult->valid_votes === $digitalResult->valid_votes
                && $actaResult->blank_votes   === $digitalResult->blank_votes
                && $actaResult->null_votes    === $digitalResult->null_votes;

            if (!$match) {
                $categoryName = $actaResult->electionTypeCategory?->electionCategory?->name
                    ?? "Franja {$actaResult->election_type_category_id}";

                $inconsistencies[] = "{$categoryName}: "
                    . "Acta [V:{$actaResult->valid_votes} B:{$actaResult->blank_votes} N:{$actaResult->null_votes}] "
                    . "≠ Digital [V:{$digitalResult->valid_votes} B:{$digitalResult->blank_votes} N:{$digitalResult->null_votes}]";
            }

            $actaResult->update([
                'matches_digital' => $match,
                'discrepancies'   => $match ? null : [$inconsistencies[array_key_last($inconsistencies)]],
            ]);
        }

        $isConsistent = empty($inconsistencies);

        $this->update([
            'is_consistent'   => $isConsistent,
            'inconsistencies' => $isConsistent ? null : $inconsistencies,
        ]);

        return $isConsistent;
    }

    public function markAsVerified(int $userId): void
    {
        $this->verifyConsistency();
        $this->update(['status' => self::STATUS_VERIFIED]);

        if (!$this->is_consistent && !empty($this->inconsistencies)) {
            Observation::create([
                'code'             => Observation::generateCode(),
                'type'             => Observation::TYPE_INCONSISTENCIA_ACTA,
                'description'      => 'Inconsistencias en acta: ' . implode('; ', $this->inconsistencies),
                'severity'         => Observation::SEVERITY_ERROR,
                'status'           => Observation::STATUS_PENDING,
                'voting_table_id'  => $this->voting_table_id,
                'election_type_id' => $this->election_type_id,
                'reviewed_by'      => $userId,
                'reviewer_role'    => 'revisor',
            ]);
            VotingTableElection::where('voting_table_id', $this->voting_table_id)
                ->where('election_type_id', $this->election_type_id)
                ->first()
                ?->markAsObserved($userId, 'Inconsistencias detectadas al verificar acta');
        }
    }

    /**
     * Manually observe this acta (e.g. illegible photo).
     */
    public function markAsObserved(int $userId, ?string $notes = null): void
    {
        $this->update(['status' => self::STATUS_OBSERVED]);

        Observation::create([
            'code'             => Observation::generateCode(),
            'type'             => Observation::TYPE_ACTA_ILEGIBLE,
            'description'      => $notes ?? 'Acta observada durante revisión',
            'severity'         => Observation::SEVERITY_WARNING,
            'status'           => Observation::STATUS_PENDING,
            'voting_table_id'  => $this->voting_table_id,
            'election_type_id' => $this->election_type_id,
            'reviewed_by'      => $userId,
            'reviewer_role'    => 'revisor',
        ]);
    }

    /**
     * Approve this acta — finalizes the VotingTableElection for this election.
     */
    public function markAsApproved(int $userId): void
    {
        $this->update([
            'status'    => self::STATUS_APPROVED,
            'is_signed' => true,
            'signed_by' => $userId,
            'signed_at' => now(),
        ]);
        VotingTableElection::where('voting_table_id', $this->voting_table_id)
            ->where('election_type_id', $this->election_type_id)
            ->first()
            ?->markAsEscrutada($userId);
    }

    public function markAsRejected(int $userId, ?string $notes = null): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);

        Observation::create([
            'code'             => Observation::generateCode(),
            'type'             => Observation::TYPE_ERROR_DATOS,
            'description'      => $notes ?? 'Acta rechazada',
            'severity'         => Observation::SEVERITY_CRITICAL,
            'status'           => Observation::STATUS_PENDING,
            'voting_table_id'  => $this->voting_table_id,
            'election_type_id' => $this->election_type_id,
            'reviewed_by'      => $userId,
            'reviewer_role'    => 'revisor',
        ]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UPLOADED  => '<span class="badge bg-info">Subida</span>',
            self::STATUS_VERIFIED  => '<span class="badge bg-success">Verificada</span>',
            self::STATUS_OBSERVED  => '<span class="badge bg-warning">Observada</span>',
            self::STATUS_CORRECTED => '<span class="badge bg-primary">Corregida</span>',
            self::STATUS_APPROVED  => '<span class="badge bg-success">Aprobada</span>',
            self::STATUS_REJECTED  => '<span class="badge bg-danger">Rechazada</span>',
            default                => '<span class="badge bg-secondary">Desconocido</span>',
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

    public static function generateCode(): string
    {
        $date = date('Ymd');
        $last = self::whereDate('created_at', today())->orderBy('id', 'desc')->first();
        $n    = $last
            ? str_pad(intval(substr($last->code, -4)) + 1, 4, '0', STR_PAD_LEFT)
            : '0001';
        return "ACTA-{$date}-{$n}";
    }
}
