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
        'is_consistent',
        'inconsistencies',
    ];

    protected $casts = [
        'total_votes' => 'integer',
        'blank_votes' => 'integer',
        'null_votes' => 'integer',
        'valid_votes' => 'integer',
        'file_size' => 'integer',
        'metadata' => 'array',
        'ocr_data' => 'array',
        'inconsistencies' => 'array',
        'ocr_processed' => 'boolean',
        'is_signed' => 'boolean',
        'is_consistent' => 'boolean',
        'ocr_processed_at' => 'datetime',
        'signed_at' => 'datetime',
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

    // ===== MÉTODOS DE ACCIÓN =====
    public function verifyConsistency(VotingTable $votingTable)
    {
        $inconsistencies = [];
        $isConsistent = true;

        // Verificar total de votos
        if ($this->total_votes !== $votingTable->total_voters) {
            $inconsistencies[] = "Total de votos: Acta {$this->total_votes} vs Mesa {$votingTable->total_voters}";
            $isConsistent = false;
        }

        // Verificar votos válidos
        if ($this->valid_votes !== $votingTable->valid_votes) {
            $inconsistencies[] = "Votos válidos: Acta {$this->valid_votes} vs Mesa {$votingTable->valid_votes}";
            $isConsistent = false;
        }

        // Verificar votos en blanco
        if ($this->blank_votes !== $votingTable->blank_votes) {
            $inconsistencies[] = "Votos en blanco: Acta {$this->blank_votes} vs Mesa {$votingTable->blank_votes}";
            $isConsistent = false;
        }

        // Verificar votos nulos
        if ($this->null_votes !== $votingTable->null_votes) {
            $inconsistencies[] = "Votos nulos: Acta {$this->null_votes} vs Mesa {$votingTable->null_votes}";
            $isConsistent = false;
        }

        $this->is_consistent = $isConsistent;
        $this->inconsistencies = $inconsistencies;
        $this->save();

        return $isConsistent;
    }

    public function markAsVerified($userId)
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
        ]);

        // Crear observación si hay inconsistencias
        if (!$this->is_consistent && !empty($this->inconsistencies)) {
            $observation = Observation::create([
                'code' => Observation::generateCode(),
                'type' => Observation::TYPE_INCONSISTENCIA_ACTA,
                'description' => 'Inconsistencias detectadas en el acta: ' . implode(', ', $this->inconsistencies),
                'severity' => Observation::SEVERITY_ERROR,
                'status' => Observation::STATUS_PENDING,
                'voting_table_id' => $this->voting_table_id,
                'election_type_id' => $this->election_type_id,
                'reviewed_by' => $userId,
                'reviewer_role' => 'revisor',
            ]);

            $this->votingTable->markAsObserved($userId, $observation->id, 'Acta con inconsistencias');
        }
    }

    public function markAsObserved($userId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_OBSERVED,
        ]);

        Observation::create([
            'code' => Observation::generateCode(),
            'type' => Observation::TYPE_ACTA_ILEGIBLE,
            'description' => $notes ?? 'Acta observada durante revisión',
            'severity' => Observation::SEVERITY_WARNING,
            'status' => Observation::STATUS_PENDING,
            'voting_table_id' => $this->voting_table_id,
            'election_type_id' => $this->election_type_id,
            'reviewed_by' => $userId,
            'reviewer_role' => 'revisor',
        ]);
    }

    public function markAsApproved($userId)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'is_signed' => true,
            'signed_by' => $userId,
            'signed_at' => now(),
        ]);

        $this->votingTable->markAsApproved($userId, 'Acta aprobada');
    }

    // ===== MÉTODOS DE CONSULTA =====
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
        $lastActa = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastActa) {
            $lastNumber = intval(substr($lastActa->code, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "ACTA-{$date}-{$newNumber}";
    }
}
