<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotingTableElection extends Model
{
    use HasFactory;

    protected $table = 'voting_table_elections';

    protected $fillable = [
        'voting_table_id',
        'election_type_id',
        'ballots_received',
        'ballots_used',
        'ballots_leftover',
        'ballots_spoiled',
        'total_voters',
        'status',
        'election_date',
        'opening_time',
        'closing_time',
        'observations',
        'updated_by',
    ];

    protected $casts = [
        'ballots_received' => 'integer',
        'ballots_used'     => 'integer',
        'ballots_leftover' => 'integer',
        'ballots_spoiled'  => 'integer',
        'total_voters'     => 'integer',
        'election_date'    => 'date',
    ];

    public const STATUS_CONFIGURADA    = 'configurada';    // Mesa set up, not yet open
    public const STATUS_EN_ESPERA      = 'en_espera';      // Waiting for opening time
    public const STATUS_VOTACION       = 'votacion';       // Actively receiving votes
    public const STATUS_CERRADA        = 'cerrada';        // Voting closed, escrutinio pending
    public const STATUS_EN_ESCRUTINIO  = 'en_escrutinio';  // Counting in progress
    public const STATUS_ESCRUTADA      = 'escrutada';      // Count complete, pending transmission
    public const STATUS_OBSERVADA      = 'observada';      // Has active unresolved observation
    public const STATUS_TRANSMITIDA    = 'transmitida';    // Results transmitted, final
    public const STATUS_ANULADA        = 'anulada';        // Annulled

    public static function getStatuses(): array
    {
        return [
            self::STATUS_CONFIGURADA   => 'Configurada',
            self::STATUS_EN_ESPERA     => 'En Espera',
            self::STATUS_VOTACION      => 'Votación',
            self::STATUS_CERRADA       => 'Cerrada',
            self::STATUS_EN_ESCRUTINIO => 'En Escrutinio',
            self::STATUS_ESCRUTADA     => 'Escrutada',
            self::STATUS_OBSERVADA     => 'Observada',
            self::STATUS_TRANSMITIDA   => 'Transmitida',
            self::STATUS_ANULADA       => 'Anulada',
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
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_VOTACION;
    }
    public function isClosed(): bool
    {
        return in_array($this->status, [
            self::STATUS_CERRADA,
            self::STATUS_EN_ESCRUTINIO,
            self::STATUS_ESCRUTADA,
            self::STATUS_TRANSMITIDA,
        ]);
    }
    public function isFinalized(): bool
    {
        return in_array($this->status, [
            self::STATUS_ESCRUTADA,
            self::STATUS_TRANSMITIDA,
            self::STATUS_ANULADA,
        ]);
    }
    public function canBeModified(): bool
    {
        return !$this->isFinalized();
    }
    public function canBeObserved(): bool
    {
        return in_array($this->status, [
            self::STATUS_VOTACION,
            self::STATUS_CERRADA,
            self::STATUS_EN_ESCRUTINIO,
        ]);
    }

    public function open(int $updatedBy): void
    {
        $this->update([
            'status'       => self::STATUS_VOTACION,
            'opening_time' => now()->toTimeString(),
            'updated_by'   => $updatedBy,
        ]);
    }
    public function close(int $updatedBy): void
    {
        $this->update([
            'status'       => self::STATUS_CERRADA,
            'closing_time' => now()->toTimeString(),
            'updated_by'   => $updatedBy,
        ]);
    }
    public function reopen(int $updatedBy): void
    {
        $this->update([
            'status'     => self::STATUS_VOTACION,
            'updated_by' => $updatedBy,
        ]);
    }
    public function startEscrutinio(int $updatedBy): void
    {
        $this->update([
            'status'     => self::STATUS_EN_ESCRUTINIO,
            'updated_by' => $updatedBy,
        ]);
    }
    public function markAsObserved(int $updatedBy, ?string $notes = null): void
    {
        $this->update([
            'status'       => self::STATUS_OBSERVADA,
            'observations' => $notes,
            'updated_by'   => $updatedBy,
        ]);
    }
    public function markAsCorrected(int $updatedBy, ?string $notes = null): void
    {
        $this->update([
            'status'       => self::STATUS_EN_ESCRUTINIO,
            'observations' => $notes,
            'updated_by'   => $updatedBy,
        ]);
    }
    public function markAsEscrutada(int $updatedBy): void
    {
        $this->update([
            'status'     => self::STATUS_ESCRUTADA,
            'updated_by' => $updatedBy,
        ]);
    }
    public function markAsTransmitida(int $updatedBy): void
    {
        $this->update([
            'status'     => self::STATUS_TRANSMITIDA,
            'updated_by' => $updatedBy,
        ]);
    }
    public function annul(int $updatedBy, ?string $notes = null): void
    {
        $this->update([
            'status'       => self::STATUS_ANULADA,
            'observations' => $notes,
            'updated_by'   => $updatedBy,
        ]);
    }
    public function validateBallots(): array
    {
        $errors = [];

        if ($this->ballots_received > 0) {
            $calculated = $this->ballots_used + $this->ballots_leftover + $this->ballots_spoiled;

            if ($calculated !== $this->ballots_received) {
                $errors[] = "Papeletas: recibidas ({$this->ballots_received}) ≠ "
                    . "usadas ({$this->ballots_used}) + sobrantes ({$this->ballots_leftover}) "
                    . "+ deterioradas ({$this->ballots_spoiled}) = {$calculated}";
            }
        }
        $expected = $this->votingTable->expected_voters;
        if ($this->total_voters > $expected) {
            $errors[] = "Votantes ({$this->total_voters}) exceden habilitados ({$expected})";
        }
        if ($this->ballots_used > 0 && $this->total_voters !== $this->ballots_used) {
            $errors[] = "Votantes ({$this->total_voters}) ≠ papeletas usadas ({$this->ballots_used})";
        }
        return $errors;
    }

    public function isBallotsConsistent(): bool
    {
        return empty($this->validateBallots());
    }
    public function getParticipationPercentageAttribute(): float
    {
        $expected = $this->votingTable->expected_voters ?? 0;
        if ($expected === 0) return 0.0;

        return round(($this->total_voters / $expected) * 100, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $colors = [
            self::STATUS_CONFIGURADA   => 'secondary',
            self::STATUS_EN_ESPERA     => 'info',
            self::STATUS_VOTACION      => 'primary',
            self::STATUS_CERRADA       => 'warning',
            self::STATUS_EN_ESCRUTINIO => 'dark',
            self::STATUS_ESCRUTADA     => 'success',
            self::STATUS_OBSERVADA     => 'danger',
            self::STATUS_TRANSMITIDA   => 'success',
            self::STATUS_ANULADA       => 'dark',
        ];

        $color = $colors[$this->status] ?? 'secondary';
        $label = self::getStatuses()[$this->status] ?? $this->status;

        return "<span class='badge bg-{$color}'>{$label}</span>";
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }
}
