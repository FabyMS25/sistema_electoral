<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dashboard extends Model
{
    protected $fillable = [
        'title',
        'is_public',
        'default_election_type_id',
        'default_category_id',
        'show_election_switcher',
        'show_category_filter',
        'auto_refresh_seconds',
    ];

    protected $casts = [
        'is_public'              => 'boolean',
        'show_election_switcher' => 'boolean',
        'show_category_filter'   => 'boolean',
        'auto_refresh_seconds'   => 'integer',
    ];

    public function defaultElectionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class, 'default_election_type_id');
    }

    public function defaultCategory(): BelongsTo
    {
        return $this->belongsTo(ElectionCategory::class, 'default_category_id');
    }
}
