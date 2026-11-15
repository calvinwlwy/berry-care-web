<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexKategoriRequest;
use App\Http\Requests\Api\StoreKategoriRequest;
use App\Http\Requests\Api\UpdateKategoriRequest;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class KategoriController extends Controller
{
    public function index(IndexKategoriRequest $request): AnonymousResourceCollection
    {
        $query = Kategori::query()->withCount('diagnoses')->orderBy('nama_kategori');
        $filters = $request->validated();

        if (isset($filters['q'])) {
            $query->where('nama_kategori', 'like', '%'.$filters['q'].'%');
        }

        return KategoriResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function store(StoreKategoriRequest $request): JsonResponse
    {
        $kategori = Kategori::query()->create($request->validated())->loadCount('diagnoses');

        return (new KategoriResource($kategori))
            ->additional(['message' => 'Kategori berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Kategori $kategori): KategoriResource
    {
        $this->authorize('view', $kategori);

        return new KategoriResource($kategori->loadCount('diagnoses'));
    }

    public function update(UpdateKategoriRequest $request, Kategori $kategori): JsonResponse
    {
        $kategori->update($request->validated());

        return (new KategoriResource($kategori->fresh()->loadCount('diagnoses')))
            ->additional(['message' => 'Kategori berhasil diperbarui.'])
            ->response();
    }

    public function destroy(Kategori $kategori): Response|JsonResponse
    {
        $this->authorize('delete', $kategori);

        if ($kategori->diagnoses()->exists()) {
            return response()->json([
                'message' => 'Kategori masih dipakai oleh data diagnosis dan tidak dapat dihapus.',
            ], 409);
        }

        $kategori->delete();

        return response()->noContent();
    }
}
