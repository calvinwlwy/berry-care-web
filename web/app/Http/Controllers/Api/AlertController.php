<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexAlertRequest;
use App\Http\Requests\Api\StoreAlertRequest;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AlertController extends Controller
{
    public function index(IndexAlertRequest $request): AnonymousResourceCollection
    {
        $query = Alert::query()->with('polybag:id,code')->latest();
        $filters = $request->validated();
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        return AlertResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function store(StoreAlertRequest $request): JsonResponse
    {
        $alert = Alert::query()->create($request->validated() + [
            'status' => 'active',
            'source' => 'manual',
        ])->load('polybag:id,code');

        return (new AlertResource($alert))
            ->additional(['message' => 'Peringatan berhasil dibuat.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Alert $alert): AlertResource
    {
        $this->authorize('view', $alert);

        return new AlertResource($alert->load('polybag:id,code'));
    }

    public function resolve(Alert $alert): JsonResponse
    {
        $this->authorize('resolve', $alert);

        if ($alert->status === 'active') {
            $alert->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => request()->user()->id,
            ]);
        }

        return response()->json([
            'message' => 'Peringatan ditandai selesai.',
            'data' => new AlertResource($alert->fresh('polybag:id,code')),
        ]);
    }

    public function destroy(Alert $alert): Response
    {
        $this->authorize('delete', $alert);
        $alert->delete();

        return response()->noContent();
    }
}
