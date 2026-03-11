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

    public function defaultDepartment()
    {
        return $this->belongsTo(\App\Models\Department::class, 'default_department_id');
    }

    public function defaultProvince()
    {
        return $this->belongsTo(\App\Models\Province::class, 'default_province_id');
    }

    public function defaultMunicipality()
    {
        return $this->belongsTo(\App\Models\Municipality::class, 'default_municipality_id');
    }
}
