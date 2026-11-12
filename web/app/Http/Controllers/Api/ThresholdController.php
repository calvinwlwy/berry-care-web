<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateThresholdsRequest;
use App\Http\Resources\ThresholdResource;
use App\Models\Threshold;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ThresholdController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ThresholdResource::collection(Threshold::query()->orderBy('id')->get());
    }

    public function update(UpdateThresholdsRequest $request): JsonResponse
    {
        $thresholds = DB::transaction(function () use ($request) {
            foreach ($request->validated('thresholds') as $key => $value) {
                Threshold::query()->where('key', $key)->update(['value' => $value]);
            }

            return Threshold::query()->orderBy('id')->get();
        });

        return response()->json([
            'message' => 'Ambang batas berhasil diperbarui.',
            'data' => ThresholdResource::collection($thresholds),
        ]);
    }
}
