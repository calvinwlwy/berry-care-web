<?php

namespace App\Models;

use Database\Factories\DiagnosisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    /** @use HasFactory<DiagnosisFactory> */
    use HasFactory;

    protected $fillable = [
        'polybag_id', 'user_id', 'type', 'label', 'confidence', 'severity', 'notes', 'image_path', 'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'scanned_at' => 'datetime',
        ];
    }

    public function polybag(): BelongsTo
    {
        return $this->belongsTo(Polybag::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
