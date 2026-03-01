<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'description',
        'severity',
        'status',
        'voting_table_id',
        'election_type_id',
        'election_category_id',
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

    public const TYPE_INCONSISTENCIA_ACTA = 'inconsistencia_acta';
    public const TYPE_ERROR_DATOS = 'error_datos';
    public const TYPE_FALTA_FIRMA = 'falta_firma';
    public const TYPE_ACTA_ILEGIBLE = 'acta_ilegible';
    public const TYPE_VOTOS_INCONSISTENTES = 'votos_inconsistentes';
    public const TYPE_MESA_ANULADA = 'mesa_anulada';
    public const TYPE_RECLAMO_PARTIDO = 'reclamo_partido';
    public const TYPE_DIFERENCIA_PAPELETAS = 'diferencia_papeletas';
    public const TYPE_CIERRE_ANTICIPADO = 'cierre_anticipado';
    public const TYPE_OTRO = 'otro';

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

    public function votingTable()
    {
        return $this->belongsTo(VotingTable::class);
    }
    public function electionType()
    {
        return $this->belongsTo(ElectionType::class);
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
}