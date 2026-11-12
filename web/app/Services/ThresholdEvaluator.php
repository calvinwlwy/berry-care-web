<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\ClimateReading;
use App\Models\SensorReading;
use App\Models\Threshold;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ThresholdEvaluator
{
    /**
     * @return array{status: string, violations: array<int, array{key: string, value: float, threshold: float, label: string}>}
     */
    public function evaluate(SensorReading $reading): array
    {
        $thresholds = $this->thresholdValues();
        $fields = [
            'soil_moisture' => ['soil_moisture_min', 'Kadar air tanah'],
            'nitrogen' => ['nitrogen_min', 'Nitrogen'],
            'phosphorus' => ['phosphorus_min', 'Fosfor'],
            'potassium' => ['potassium_min', 'Kalium'],
        ];
        $violations = [];

        foreach ($fields as $field => [$key, $label]) {
            $limit = $thresholds[$key] ?? null;
            if ($limit !== null && (float) $reading->{$field} < $limit) {
                $violations[] = [
                    'key' => $key,
                    'value' => (float) $reading->{$field},
                    'threshold' => $limit,
                    'label' => $label,
                ];
            }
        }

        return [
            'status' => $violations === [] ? 'optimal' : 'deficient',
            'violations' => $violations,
        ];
    }

    public function synchronizeReadingAlerts(SensorReading $reading): array
    {
        $evaluation = $this->evaluate($reading);
        $violatingKeys = array_column($evaluation['violations'], 'key');
        $labels = [
            'soil_moisture_min' => 'soil_moisture_low',
            'nitrogen_min' => 'nitrogen_low',
            'phosphorus_min' => 'phosphorus_low',
            'potassium_min' => 'potassium_low',
        ];

        DB::transaction(function () use ($reading, $violatingKeys, $labels, $evaluation): void {
            foreach ($labels as $key => $type) {
                $active = Alert::query()
                    ->where('source', 'system')
                    ->where('status', 'active')
                    ->where('polybag_id', $reading->polybag_id)
                    ->where('type', $type)
                    ->lockForUpdate()
                    ->first();

                if (in_array($key, $violatingKeys, true)) {
                    $violation = collect($evaluation['violations'])->firstWhere('key', $key);
                    $severity = $violation['value'] < $violation['threshold'] * 0.5
                        ? 'critical'
                        : 'warning';
                    $message = "{$violation['label']} pada {$reading->polybag->code} berada di bawah ambang batas.";

                    if ($active === null) {
                        Alert::query()->create([
                            'polybag_id' => $reading->polybag_id,
                            'type' => $type,
                            'severity' => $severity,
                            'message' => $message,
                            'status' => 'active',
                            'source' => 'system',
                        ]);
                    } else {
                        $active->update(['severity' => $severity, 'message' => $message]);
                    }
                } elseif ($active !== null) {
                    $active->update(['status' => 'resolved', 'resolved_at' => now()]);
                }
            }
        });

        return $evaluation;
    }

    public function synchronizeClimateAlert(ClimateReading $reading): void
    {
        $limit = $this->thresholdValues()['humidity_max'] ?? 85.0;
        DB::transaction(function () use ($reading, $limit): void {
            $active = Alert::query()
                ->whereNull('polybag_id')
                ->where('source', 'system')
                ->where('status', 'active')
                ->where('type', 'humidity_high')
                ->lockForUpdate()
                ->first();

            if ((float) $reading->humidity > $limit) {
                if ($active === null) {
                    Alert::query()->create([
                        'polybag_id' => null,
                        'type' => 'humidity_high',
                        'severity' => (float) $reading->humidity >= $limit + 10 ? 'critical' : 'warning',
                        'message' => 'Kelembapan udara melebihi ambang batas dan meningkatkan risiko jamur.',
                        'status' => 'active',
                        'source' => 'system',
                    ]);
                }
            } elseif ($active !== null) {
                $active->update(['status' => 'resolved', 'resolved_at' => now()]);
            }
        });
    }

    /**
     * @return array<string, float>
     */
    public function thresholdValues(): array
    {
        return Threshold::query()->pluck('value', 'key')
            ->map(static fn ($value): float => (float) $value)
            ->all();
    }

    /**
     * @return Collection<string, Threshold>
     */
    public function thresholds(): Collection
    {
        return Threshold::query()->orderBy('id')->get()->keyBy('key');
    }
}
