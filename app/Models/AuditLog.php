<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'notes',
        'performed_at',
    ];

    protected $casts = [
        'old_data'     => 'array',
        'new_data'     => 'array',
        'performed_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo('auditable', 'model_type', 'model_id');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModel($query, $modelType, $modelId)
    {
        return $query->where('model_type', $modelType)
                     ->where('model_id', $modelId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeLastDays($query, $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public static function log($action, $model, $oldData = null, $newData = null, $notes = null): self
    {
        return self::create([
            'user_id'      => auth()->check() ? auth()->id() : null,
            'action'       => $action,
            'model_type'   => get_class($model),
            'model_id'     => $model->id,
            'old_data'     => $oldData,
            'new_data'     => $newData,
            'ip_address'   => app()->runningInConsole() ? null : request()->ip(),
            'user_agent'   => app()->runningInConsole() ? null : request()->userAgent(),
            'notes'        => $notes,
            'performed_at' => now(),
        ]);
    }

    public function getDescriptionAttribute(): string
    {
        $userName  = $this->user ? $this->user->name : 'Sistema';
        $modelName = class_basename($this->model_type);

        return match ($this->action) {
            'created'   => "{$userName} creó {$modelName} #{$this->model_id}",
            'updated'   => "{$userName} actualizó {$modelName} #{$this->model_id}",
            'deleted'   => "{$userName} eliminó {$modelName} #{$this->model_id}",
            'restored'  => "{$userName} restauró {$modelName} #{$this->model_id}",
            'reviewed'  => "{$userName} revisó {$modelName} #{$this->model_id}",
            'validated' => "{$userName} validó {$modelName} #{$this->model_id}",
            'observed'  => "{$userName} observó {$modelName} #{$this->model_id}",
            'corrected' => "{$userName} corrigió {$modelName} #{$this->model_id}",
            'approved'  => "{$userName} aprobó {$modelName} #{$this->model_id}",
            'rejected'  => "{$userName} rechazó {$modelName} #{$this->model_id}",
            'closed'    => "{$userName} cerró {$modelName} #{$this->model_id}",
            'uploaded'  => "{$userName} subió un archivo a {$modelName} #{$this->model_id}",
            default     => "{$userName} {$this->action} en {$modelName} #{$this->model_id}",
        };
    }

    public function getChangesSummaryAttribute(): ?string
    {
        if (!$this->old_data || !$this->new_data) {
            return null;
        }

        $changes = [];
        foreach ($this->new_data as $key => $value) {
            $oldValue = $this->old_data[$key] ?? null;
            if ($oldValue != $value) {
                $changes[] = "$key: '$oldValue' → '$value'";
            }
        }

        return implode(', ', $changes);
    }
}
