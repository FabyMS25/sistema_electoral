<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reviewer extends Model
{
    protected $fillable = [
        'user_id', 'assignable_type', 'assignable_id', 
        'assigned_at', 'assigned_by', 'is_active'
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignable()
    {
        return $this->morphTo();
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}