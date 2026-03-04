<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'display_name', 'description', 'group', 'scope']; // AÑADIR 'scope'

    protected $casts = [
        'scope' => 'string',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('scope', 'scope_id', 'scope_type')
            ->withTimestamps();
    }

    public function scopeByScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    public function scopeGlobal($query)
    {
        return $query->where('scope', 'global');
    }
}
