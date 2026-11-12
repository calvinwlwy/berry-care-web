<?php

namespace App\Models;

use Database\Factories\ThresholdFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Threshold extends Model
{
    /** @use HasFactory<ThresholdFactory> */
    use HasFactory;

    protected $fillable = ['key', 'label', 'value', 'unit'];

    protected function casts(): array
    {
        return ['value' => 'float'];
    }
}
