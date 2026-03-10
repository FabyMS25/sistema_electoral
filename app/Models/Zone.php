<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'district_id', 'latitude', 'longitude', 'active'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function institutions()
    {
        return $this->hasMany(Institution::class);
    }
}
