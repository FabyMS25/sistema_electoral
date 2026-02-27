<?php
namespace App\Models;

use App\Traits\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableDelegate extends Model
{
    use HasFactory;
    use ActiveScope;

    protected $table = 'table_delegates';
    
    protected $fillable = [
        'user_id', 'voting_table_id', 'role', 'assigned_at', 
        'assigned_until', 'assigned_by', 'is_active'
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'assigned_until' => 'date',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votingTable()
    {
        return $this->belongsTo(VotingTable::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope para asignaciones vigentes (por fecha)
    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('assigned_until')
                  ->orWhere('assigned_until', '>=', now());
            });
    }
}