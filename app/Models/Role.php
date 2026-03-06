<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'default_scope'];

    protected $casts = [
        'default_scope' => 'string',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('scope', 'institution_id', 'voting_table_id', 'election_type_id', 'scope_settings')
            ->withTimestamps();
    }

    public function getUsersByScope($scope, $scopeId = null)
    {
        $query = $this->users()->wherePivot('scope', $scope);
        if ($scopeId) {
            $column = $scope === 'recinto' ? 'institution_id' : 'voting_table_id';
            $query->wherePivot($column, $scopeId);
        }
        return $query->get();
    }

    public function scopeByDefaultScope($query, $scope)
    {
        return $query->where('default_scope', $scope);
    }
}
