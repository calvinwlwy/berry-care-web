<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexPolybagRequest;
use App\Http\Requests\Api\StorePolybagRequest;
use App\Http\Requests\Api\UpdatePolybagRequest;
use App\Http\Resources\PolybagResource;
use App\Models\Polybag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PolybagController extends Controller
{
    public function index(IndexPolybagRequest $request): AnonymousResourceCollection
    {
        $query = Polybag::query()->with('latestReading')->orderBy('code');
        $filters = $request->validated();

        if (isset($filters['q'])) {
            $query->where('code', 'like', '%'.$filters['q'].'%');
        }
        if (isset($filters['block'])) {
            $query->where('block', $filters['block']);
        }
        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', $filters['is_active']);
        }

        return PolybagResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function store(StorePolybagRequest $request): JsonResponse
    {
        $polybag = Polybag::query()->create($request->validated())->refresh();

        return (new PolybagResource($polybag))
            ->additional(['message' => 'Polybag berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Polybag $polybag): PolybagResource
    {
        $this->authorize('view', $polybag);

        return new PolybagResource($polybag->load('latestReading'));
    }

    public function update(UpdatePolybagRequest $request, Polybag $polybag): JsonResponse
    {
        $polybag->update($request->validated());

        return (new PolybagResource($polybag->fresh('latestReading')))
            ->additional(['message' => 'Polybag berhasil diperbarui.'])
            ->response();
    }

    public function destroy(Polybag $polybag): Response
    {
        $this->authorize('delete', $polybag);
        $polybag->delete();

        return response()->noContent();
    }
}
