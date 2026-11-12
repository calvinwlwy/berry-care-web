<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'polybag_id' => $this->polybag_id,
            'polybag_code' => $this->whenLoaded('polybag', fn () => $this->polybag?->code),
            'type' => $this->type,
            'severity' => $this->severity,
            'message' => $this->message,
            'status' => $this->status,
            'source' => $this->source,
            'resolved_at' => $this->resolved_at?->toISOString(),
            'resolved_by' => $this->resolved_by,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
