<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Observation extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'observations';

    protected $fillable = [
        'code',
        'type',
        'description',
        'severity',
        'status',
        'voting_table_id',
        'election_type_id',
        'candidate_id',
        'reviewed_by',
        'reviewer_role',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'resolution_type',
        'evidence_photo',
        'evidence_document',
        'is_escalated',
        'escalated_to',
        'escalated_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'escalated_at' => 'datetime',
        'is_escalated' => 'boolean',
    ];

    const TYPE_INCONSISTENCIA_ACTA = 'inconsistencia_acta';
    const TYPE_ERROR_DATOS = 'error_datos';
    const TYPE_FALTA_FIRMA = 'falta_firma';
    const TYPE_ACTA_ILEGIBLE = 'acta_ilegible';
    const TYPE_VOTOS_INCONSISTENTES = 'votos_inconsistentes';
    const TYPE_MESA_ANULADA = 'mesa_anulada';
    const TYPE_RECLAMO_PARTIDO = 'reclamo_partido';
    const TYPE_DIFERENCIA_PAPELETAS = 'diferencia_papeletas';
    const TYPE_CIERRE_ANTICIPADO = 'cierre_anticipado';
    const TYPE_OTRO = 'otro';

    const STATUS_PENDING = 'pending';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ESCALATED = 'escalated';

    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_CRITICAL = 'critical';

    const RESOLUTION_CORRECCION = 'correccion';
    const RESOLUTION_ANULACION = 'anulacion';
    const RESOLUTION_RECHAZO = 'rechazo';
    const RESOLUTION_ESCALAMIENTO = 'escalamiento';

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            self::SEVERITY_INFO => 'info',
            self::SEVERITY_WARNING => 'warning',
            self::SEVERITY_ERROR => 'danger',
            self::SEVERITY_CRITICAL => 'dark',
            default => 'secondary',
        };
    }
    public static function getTypes(): array
    {
        return [
            self::TYPE_INCONSISTENCIA_ACTA => 'Inconsistencia en Acta',
            self::TYPE_ERROR_DATOS => 'Error en Datos',
            self::TYPE_FALTA_FIRMA => 'Falta Firma',
            self::TYPE_ACTA_ILEGIBLE => 'Acta Ilegible',
            self::TYPE_VOTOS_INCONSISTENTES => 'Votos Inconsistentes',
            self::TYPE_MESA_ANULADA => 'Mesa Anulada',
            self::TYPE_RECLAMO_PARTIDO => 'Reclamo de Partido',
            self::TYPE_DIFERENCIA_PAPELETAS => 'Diferencia de Papeletas',
            self::TYPE_CIERRE_ANTICIPADO => 'Cierre Anticipado',
            self::TYPE_OTRO => 'Otro',
        ];
    }
    public function getSeverityBadgeAttribute(): string
    {
        return match($this->severity) {
            self::SEVERITY_INFO => '<span class="badge bg-info">Info</span>',
            self::SEVERITY_WARNING => '<span class="badge bg-warning">Advertencia</span>',
            self::SEVERITY_ERROR => '<span class="badge bg-danger">Error</span>',
            self::SEVERITY_CRITICAL => '<span class="badge bg-dark">Crítico</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '<span class="badge bg-warning">Pendiente</span>',
            self::STATUS_IN_REVIEW => '<span class="badge bg-info">En Revisión</span>',
            self::STATUS_RESOLVED => '<span class="badge bg-success">Resuelto</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">Rechazado</span>',
            self::STATUS_ESCALATED => '<span class="badge bg-primary">Escalado</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }

    public function votingTable()
    {
        return $this->belongsTo(VotingTable::class);
    }
    public function electionType()
    {
        return $this->belongsTo(ElectionType::class);
    }
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
    public function electionCategory()
    {
        return $this->belongsTo(ElectionCategory::class);
    }
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    // ===== MÉTODOS DE ACCIÓN =====

    public function resolve($userId, $resolutionType, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'resolution_type' => $resolutionType,
            'resolution_notes' => $notes,
        ]);

        // Actualizar votos asociados si es necesario
        if ($resolutionType === self::RESOLUTION_CORRECCION) {
            $this->votes()->update([
                'observation_id' => null,
            ]);
        }
    }

    public function escalate($userId, $escalatedToUserId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_ESCALATED,
            'is_escalated' => true,
            'escalated_to' => $escalatedToUserId,
            'escalated_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function reject($userId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'resolution_type' => self::RESOLUTION_RECHAZO,
            'resolution_notes' => $notes,
        ]);
    }

    // ===== MÉTODOS DE CONSULTA =====

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isEscalated(): bool
    {
        return $this->is_escalated;
    }

    public static function generateCode(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastObservation = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastObservation) {
            $lastNumber = intval(substr($lastObservation->code, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "OBS-{$year}{$month}-{$newNumber}";
    }
}
