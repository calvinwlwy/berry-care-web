<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClimateReadingResource;
use App\Models\Alert;
use App\Models\ClimateReading;
use App\Models\Diagnosis;
use App\Models\Polybag;
use App\Models\SensorReading;
use App\Services\ThresholdEvaluator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardSummaryController extends Controller
{
    public function __invoke(ThresholdEvaluator $evaluator): JsonResponse
    {
        $polybags = Polybag::query()
            ->with('latestReading')
            ->get();
        $statusCounts = ['optimal' => 0, 'deficient' => 0, 'unknown' => 0];
        foreach ($polybags as $polybag) {
            $latest = $polybag->latestReading;
            $status = $latest === null ? 'unknown' : $evaluator->evaluate($latest)['status'];
            $statusCounts[$status]++;
        }

        $activeAlerts = Alert::query()
            ->where('status', 'active')
            ->select('severity', DB::raw('COUNT(*) as total'))
            ->groupBy('severity')
            ->pluck('total', 'severity');
        $alertCounts = [
            'info' => (int) ($activeAlerts['info'] ?? 0),
            'warning' => (int) ($activeAlerts['warning'] ?? 0),
            'critical' => (int) ($activeAlerts['critical'] ?? 0),
        ];

        $from = Carbon::today()->subDays(6);
        $averages = SensorReading::query()
            ->where('recorded_at', '>=', $from)
            ->selectRaw('DATE(recorded_at) as date, AVG(soil_moisture) as average')
            ->groupBy('date')
            ->pluck('average', 'date');
        $dailyAverage = collect(range(0, 6))->map(function (int $offset) use ($from, $averages): array {
            $date = $from->copy()->addDays($offset);

            return [
                'date' => $date->toDateString(),
                'average_soil_moisture' => isset($averages[$date->toDateString()])
                    ? round((float) $averages[$date->toDateString()], 2)
                    : null,
            ];
        })->all();

        return response()->json([
            'data' => [
                'active_polybags' => $polybags->where('is_active', true)->count(),
                'polybags_by_status' => $statusCounts,
                'active_alerts_by_severity' => $alertCounts,
                'latest_climate' => ($climate = ClimateReading::query()->latest('recorded_at')->first())
                    ? new ClimateReadingResource($climate)
                    : null,
                'diagnoses_today' => Diagnosis::query()
                    ->whereDate('scanned_at', Carbon::today())
                    ->count(),
                'average_soil_moisture_last_7_days' => $dailyAverage,
            ],
        ]);
    }
}
