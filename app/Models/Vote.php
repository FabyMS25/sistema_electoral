<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'percentage',
        'vote_status',
        'voting_table_id',
        'candidate_id',
        'election_type_id',
        'user_id',
        'verified_at',
        'verified_by',
        'corrected_by',
        'corrected_at',
        'observation_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'percentage' => 'float',
        'verified_at' => 'datetime',
        'corrected_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';
    const STATUS_OBSERVED = 'observed';
    const STATUS_CORRECTED = 'corrected';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_VERIFIED => 'Verificado',
            self::STATUS_OBSERVED => 'Observado',
            self::STATUS_CORRECTED => 'Corregido',
        ];
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function correctedBy()
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
    public function observation()
    {
        return $this->belongsTo(Observation::class);
    }

    public function isPending()
    {
        return $this->vote_status === self::STATUS_PENDING;
    }
    public function isVerified()
    {
        return $this->vote_status === self::STATUS_VERIFIED;
    }
    public function isObserved()
    {
        return $this->vote_status === self::STATUS_OBSERVED;
    }
    public function isCorrected()
    {
        return $this->vote_status === self::STATUS_CORRECTED;
    }
    public function getTallyAttribute()
    {
        $quantity = $this->quantity;
        $groups = floor($quantity / 5);
        $remaining = $quantity % 5;   
        $tally = '';
        for ($i = 0; $i < $groups; $i++) {
            $tally .= '卌 ';
        }        
        if ($remaining > 0) {
            $tally .= str_repeat('| ', $remaining);
        }        
        return trim($tally);
    }

    public function getVisualTallyAttribute()
    {
        $quantity = $this->quantity;
        $groups = floor($quantity / 5);
        $remaining = $quantity % 5;
        $visual = '';
        for ($i = 0; $i < $groups; $i++) {
            $visual .= '<span class="tally-group">□ □ □ □ □</span> ';
        }        
        if ($remaining > 0) {
            $visual .= '<span class="tally-remaining">' . str_repeat('■ ', $remaining) . '</span>';
        }        
        return trim($visual);
    }
}