<?php
// app/Models/VotingTable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotingTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'code_ine',
        'number',
        'letter',
        'type',
        'from_name',
        'to_name',
        'from_number',
        'to_number',
        'registered_citizens',
        'voted_citizens',
        'absent_citizens',
        'computed_records',
        'annulled_records',
        'enabled_records',
        'blank_votes',
        'null_votes',
        'status',
        'opening_time',
        'closing_time',
        'election_date',
        'institution_id',
        'election_type_id',
        'president_id',
        'secretary_id',
        'vocal1_id',
        'vocal2_id',
        'vocal3_id',
        'vocal4_id',
        'acta_number',
        'acta_photo',
        'acta_pdf',
        'acta_uploaded_at',
        'created_by',
        'updated_by',
        'observations',
    ];

    protected $casts = [
        'registered_citizens' => 'integer',
        'voted_citizens' => 'integer',
        'absent_citizens' => 'integer',
        'computed_records' => 'integer',
        'annulled_records' => 'integer',
        'enabled_records' => 'integer',
        'blank_votes' => 'integer',
        'null_votes' => 'integer',
        'number' => 'integer',
        'from_number' => 'integer',
        'to_number' => 'integer',
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
        'election_date' => 'date',
        'acta_uploaded_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pendiente';
    public const STATUS_IN_PROGRESS = 'en_proceso';
    public const STATUS_CLOSED = 'cerrado';
    public const STATUS_COMPUTING = 'en_computo';
    public const STATUS_COMPUTED = 'computado';
    public const STATUS_OBSERVED = 'observado';
    public const STATUS_ANNULLED = 'anulado';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_IN_PROGRESS => 'En Proceso',
            self::STATUS_CLOSED => 'Cerrado',
            self::STATUS_COMPUTING => 'En Cómputo',
            self::STATUS_COMPUTED => 'Computado',
            self::STATUS_OBSERVED => 'Observado',
            self::STATUS_ANNULLED => 'Anulado',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($votingTable) {
            if (empty($votingTable->code)) {
                $votingTable->code = self::generateUniqueCode();
            }
        });
    }

    protected static function generateUniqueCode(): string
    {
        $prefix = 'MESA';
        $number = 1;
        $lastCode = self::withTrashed()->orderBy('id', 'desc')->value('code');
        if ($lastCode && preg_match('/MESA(\d+)/', $lastCode, $matches)) {
            $number = (int)$matches[1] + 1;
        }
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // ===== RELACIONES =====
    
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    // ¡ESTA ES LA RELACIÓN QUE FALTABA!
    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class, 'election_type_id');
    }

    public function president(): BelongsTo
    {
        return $this->belongsTo(User::class, 'president_id');
    }

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    public function vocal1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vocal1_id');
    }

    public function vocal2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vocal2_id');
    }

    public function vocal3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vocal3_id');
    }

    public function vocal4(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vocal4_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function delegates(): HasMany
    {
        return $this->hasMany(TableDelegate::class, 'voting_table_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(Acta::class);
    }

    // ===== HELPERS =====
    
    public function getActiveDelegateAttribute()
    {
        return $this->delegates()
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('assigned_until')
                  ->orWhere('assigned_until', '>=', now());
            })
            ->with('user')
            ->first();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '<span class="badge bg-warning">Pendiente</span>',
            self::STATUS_IN_PROGRESS => '<span class="badge bg-info">En Proceso</span>',
            self::STATUS_CLOSED => '<span class="badge bg-secondary">Cerrado</span>',
            self::STATUS_COMPUTING => '<span class="badge bg-primary">En Cómputo</span>',
            self::STATUS_COMPUTED => '<span class="badge bg-success">Computado</span>',
            self::STATUS_OBSERVED => '<span class="badge bg-danger">Observado</span>',
            self::STATUS_ANNULLED => '<span class="badge bg-dark">Anulado</span>',
            default => '<span class="badge bg-light">Desconocido</span>',
        };
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->registered_citizens == 0) return 0;
        return round(($this->voted_citizens / $this->registered_citizens) * 100, 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'mixta' => 'Mixta',
            'masculina' => 'Masculina',
            'femenina' => 'Femenina',
            default => $this->type,
        };
    }

    public function getFullCodeAttribute(): string
    {
        return $this->code . ($this->letter ? '-' . $this->letter : '');
    }
}