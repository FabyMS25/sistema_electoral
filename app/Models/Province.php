<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'department_id','latitude','longitude'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }
}
