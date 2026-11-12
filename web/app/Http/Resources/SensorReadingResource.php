<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'polybag_id' => $this->polybag_id,
            'soil_moisture' => $this->soil_moisture,
            'nitrogen' => $this->nitrogen,
            'phosphorus' => $this->phosphorus,
            'potassium' => $this->potassium,
            'recorded_at' => $this->recorded_at?->toISOString(),
        ];
    }
}
