<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexReadingsRequest;
use App\Http\Requests\Api\StoreSensorReadingRequest;
use App\Http\Resources\SensorReadingResource;
use App\Models\Polybag;
use App\Models\SensorReading;
use App\Services\ThresholdEvaluator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class SensorReadingController extends Controller
{
    public function index(IndexReadingsRequest $request, Polybag $polybag): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SensorReading::class);
        $query = $polybag->readings()->orderByDesc('recorded_at');
        $filters = $request->validated();

        if (isset($filters['from'])) {
            $query->where('recorded_at', '>=', $filters['from']);
        }
        if (isset($filters['to'])) {
            $query->where('recorded_at', '<=', $filters['to']);
        }

        return SensorReadingResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function store(
        StoreSensorReadingRequest $request,
        Polybag $polybag,
        ThresholdEvaluator $evaluator
    ): JsonResponse {
        $reading = DB::transaction(function () use ($request, $polybag, $evaluator): SensorReading {
            $reading = $polybag->readings()->create($request->validated());
            $evaluator->synchronizeReadingAlerts($reading->load('polybag'));

            return $reading;
        });

        return (new SensorReadingResource($reading))
            ->additional(['message' => 'Pembacaan sensor berhasil disimpan.'])
            ->response()
            ->setStatusCode(201);
    }
}
