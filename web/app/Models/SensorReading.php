<?php

namespace App\Models;

use Database\Factories\SensorReadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    /** @use HasFactory<SensorReadingFactory> */
    use HasFactory;

    protected $fillable = [
        'polybag_id', 'soil_moisture', 'nitrogen', 'phosphorus', 'potassium', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'soil_moisture' => 'float',
            'nitrogen' => 'float',
            'phosphorus' => 'float',
            'potassium' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    public function polybag(): BelongsTo
    {
        return $this->belongsTo(Polybag::class);
    }
}
