<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'severity',
        'status',
        'voting_table_id',
        'election_type_id',
        'reviewed_by',
        'resolved_by',
        'resolved_at',
        'resolution_notes'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function votingTable()
    {
        return $this->belongsTo(VotingTable::class);
    }

    public function electionType()
    {
        return $this->belongsTo(ElectionType::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}