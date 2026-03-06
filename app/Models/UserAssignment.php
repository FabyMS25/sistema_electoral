<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_assignments';

    protected $fillable = [
        'user_id',
        'institution_id',
        'election_type_id',
        'delegate_type',
        'voting_table_id',
        'assignment_date',
        'expiration_date',
        'credential_number',
        'credential_photo',
        'status',
        'assigned_by',
        'observations',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'expiration_date' => 'date',
    ];

    const DELEGATE_TYPE_GENERAL = 'delegado_general';
    const DELEGATE_TYPE_MESA = 'delegado_mesa';
    const DELEGATE_TYPE_PRESIDENTE = 'presidente';
    const DELEGATE_TYPE_SECRETARIO = 'secretario';
    const DELEGATE_TYPE_VOCAL = 'vocal';
    const DELEGATE_TYPE_TECNICO = 'tecnico';
    const DELEGATE_TYPE_OBSERVADOR = 'observador';

    const STATUS_ACTIVO = 'activo';
    const STATUS_SUSPENDIDO = 'suspendido';
    const STATUS_FINALIZADO = 'finalizado';
    const STATUS_PENDIENTE = 'pendiente';

    public static function getDelegateTypes(): array
    {
        return [
            self::DELEGATE_TYPE_GENERAL => 'Delegado General',
            self::DELEGATE_TYPE_MESA => 'Delegado de Mesa',
            self::DELEGATE_TYPE_PRESIDENTE => 'Presidente',
            self::DELEGATE_TYPE_SECRETARIO => 'Secretario',
            self::DELEGATE_TYPE_VOCAL => 'Vocal',
            self::DELEGATE_TYPE_TECNICO => 'Técnico',
            self::DELEGATE_TYPE_OBSERVADOR => 'Observador',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVO => 'Activo',
            self::STATUS_SUSPENDIDO => 'Suspendido',
            self::STATUS_FINALIZADO => 'Finalizado',
            self::STATUS_PENDIENTE => 'Pendiente',
        ];
    }

    // ===== RELACIONES =====
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class);
    }

    public function votingTable(): BelongsTo
    {
        return $this->belongsTo(VotingTable::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // ===== SCOPES =====
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVO);
    }

    public function scopeByElectionType($query, $electionTypeId)
    {
        return $query->where('election_type_id', $electionTypeId);
    }

    public function scopeByInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    public function scopeByVotingTable($query, $votingTableId)
    {
        return $query->where('voting_table_id', $votingTableId);
    }

    public function scopeByDelegateType($query, $type)
    {
        return $query->where('delegate_type', $type);
    }

    // ===== MÉTODOS =====
    public function isValid(): bool
    {
        return $this->status === self::STATUS_ACTIVO &&
               (!$this->expiration_date || $this->expiration_date >= now());
    }

    public function getDelegateTypeLabelAttribute(): string
    {
        return self::getDelegateTypes()[$this->delegate_type] ?? $this->delegate_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVO => '<span class="badge bg-success">Activo</span>',
            self::STATUS_SUSPENDIDO => '<span class="badge bg-warning">Suspendido</span>',
            self::STATUS_FINALIZADO => '<span class="badge bg-secondary">Finalizado</span>',
            self::STATUS_PENDIENTE => '<span class="badge bg-info">Pendiente</span>',
            default => '<span class="badge bg-dark">Desconocido</span>',
        };
    }
}
