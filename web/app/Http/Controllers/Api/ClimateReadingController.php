<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexClimateReadingsRequest;
use App\Http\Requests\Api\StoreClimateReadingRequest;
use App\Http\Resources\ClimateReadingResource;
use App\Models\ClimateReading;
use App\Services\ThresholdEvaluator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ClimateReadingController extends Controller
{
    public function index(IndexClimateReadingsRequest $request): AnonymousResourceCollection
    {
        $query = ClimateReading::query()->orderByDesc('recorded_at');
        $filters = $request->validated();
        if (isset($filters['from'])) {
            $query->where('recorded_at', '>=', $filters['from']);
        }
        if (isset($filters['to'])) {
            $query->where('recorded_at', '<=', $filters['to']);
        }

        return ClimateReadingResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function latest(): JsonResponse
    {
        $reading = ClimateReading::query()->latest('recorded_at')->first();

        return response()->json([
            'data' => $reading ? new ClimateReadingResource($reading) : null,
        ]);
    }

    public function store(
        StoreClimateReadingRequest $request,
        ThresholdEvaluator $evaluator
    ): JsonResponse {
        $reading = DB::transaction(function () use ($request, $evaluator): ClimateReading {
            $reading = ClimateReading::query()->create($request->validated());
            $evaluator->synchronizeClimateAlert($reading);

            return $reading;
        });

        return (new ClimateReadingResource($reading))
            ->additional(['message' => 'Pembacaan iklim berhasil disimpan.'])
            ->response()
            ->setStatusCode(201);
    }
}
