<?php

namespace App\Models;

use Database\Factories\AlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    /** @use HasFactory<AlertFactory> */
    use HasFactory;

    protected $fillable = [
        'polybag_id', 'type', 'severity', 'message', 'status', 'source', 'resolved_at', 'resolved_by',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function polybag(): BelongsTo
    {
        return $this->belongsTo(Polybag::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
