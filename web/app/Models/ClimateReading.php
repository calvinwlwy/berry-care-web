<?php

namespace App\Models;

use Database\Factories\ClimateReadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClimateReading extends Model
{
    /** @use HasFactory<ClimateReadingFactory> */
    use HasFactory;

    protected $fillable = ['temperature', 'humidity', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'humidity' => 'float',
            'recorded_at' => 'datetime',
        ];
    }
}
