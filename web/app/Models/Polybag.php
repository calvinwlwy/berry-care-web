<?php

namespace App\Models;

use Database\Factories\PolybagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Polybag extends Model
{
    /** @use HasFactory<PolybagFactory> */
    use HasFactory;

    protected $fillable = ['code', 'block', 'location', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function readings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function latestReading(): HasOne
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('recorded_at');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
