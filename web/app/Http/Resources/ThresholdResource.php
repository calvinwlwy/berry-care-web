<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThresholdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
            'unit' => $this->unit,
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
