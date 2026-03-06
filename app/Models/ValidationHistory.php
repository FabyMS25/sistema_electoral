<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidationHistory extends Model
{
    use HasFactory;

    protected $table = 'validation_history';

    protected $fillable = [
        'vote_id',
        'user_id',
        'action',
        'notes',
        'previous_values',
        'new_values',
    ];

    protected $casts = [
        'previous_values' => 'array',
        'new_values'      => 'array',
    ];

    public const ACTION_REVIEW   = 'review';
    public const ACTION_OBSERVE  = 'observe';
    public const ACTION_CORRECT  = 'correct';
    public const ACTION_VALIDATE = 'validate';
    public const ACTION_APPROVE  = 'approve';
    public const ACTION_REJECT   = 'reject';
    public const ACTION_UPDATE   = 'update';

    public static function getActions(): array
    {
        return [
            self::ACTION_REVIEW   => 'Revisión',
            self::ACTION_OBSERVE  => 'Observación',
            self::ACTION_CORRECT  => 'Corrección',
            self::ACTION_VALIDATE => 'Validación',
            self::ACTION_APPROVE  => 'Aprobación',
            self::ACTION_REJECT   => 'Rechazo',
            self::ACTION_UPDATE   => 'Actualización',
        ];
    }

    public function vote(): BelongsTo
    {
        return $this->belongsTo(Vote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_REVIEW   => '<span class="badge bg-info">Revisión</span>',
            self::ACTION_OBSERVE  => '<span class="badge bg-warning">Observación</span>',
            self::ACTION_CORRECT  => '<span class="badge bg-primary">Corrección</span>',
            self::ACTION_VALIDATE => '<span class="badge bg-success">Validación</span>',
            self::ACTION_APPROVE  => '<span class="badge bg-success">Aprobación</span>',
            self::ACTION_REJECT   => '<span class="badge bg-danger">Rechazo</span>',
            self::ACTION_UPDATE   => '<span class="badge bg-secondary">Actualización</span>',
            default               => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }
}
