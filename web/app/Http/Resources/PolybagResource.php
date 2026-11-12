<?php

namespace App\Http\Resources;

use App\Models\SensorReading;
use App\Services\ThresholdEvaluator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolybagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $latestReading = $this->whenLoaded('latestReading');
        $evaluation = $latestReading instanceof SensorReading
            ? app(ThresholdEvaluator::class)->evaluate($latestReading)
            : null;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'block' => $this->block,
            'location' => $this->location,
            'is_active' => $this->is_active,
            'status' => $evaluation['status'] ?? 'unknown',
            'violations' => $evaluation['violations'] ?? [],
            'latest_reading' => $latestReading instanceof SensorReading
                ? new SensorReadingResource($latestReading)
                : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
