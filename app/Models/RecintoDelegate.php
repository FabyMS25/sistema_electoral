<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecintoDelegate extends Model
{
    protected $fillable = [
        'user_id', 'institution_id', 'assigned_at', 
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

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}