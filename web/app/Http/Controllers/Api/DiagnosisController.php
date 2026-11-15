<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexDiagnosisRequest;
use App\Http\Requests\Api\StoreDiagnosisRequest;
use App\Http\Requests\Api\UpdateDiagnosisRequest;
use App\Http\Resources\DiagnosisResource;
use App\Models\Diagnosis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class DiagnosisController extends Controller
{
    public function index(IndexDiagnosisRequest $request): AnonymousResourceCollection
    {
        $query = Diagnosis::query()->with(['user:id,name', 'polybag:id,code', 'kategori:id,nama_kategori'])->latest('scanned_at');
        $filters = $request->validated();
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['polybag_id'])) {
            $query->where('polybag_id', $filters['polybag_id']);
        }
        if (isset($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }

        return DiagnosisResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function store(StoreDiagnosisRequest $request): JsonResponse
    {
        $data = Arr::except($request->validated(), ['image']);
        $data['user_id'] = $request->user()->id;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('diagnoses', 'public');
        }

        $diagnosis = Diagnosis::query()->create($data)->load([
            'user:id,name',
            'polybag:id,code',
            'kategori:id,nama_kategori',
        ]);

        return (new DiagnosisResource($diagnosis))
            ->additional(['message' => 'Diagnosis berhasil disimpan.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Diagnosis $diagnosis): DiagnosisResource
    {
        $this->authorize('view', $diagnosis);

        return new DiagnosisResource($diagnosis->load([
            'user:id,name',
            'polybag:id,code',
            'kategori:id,nama_kategori',
        ]));
    }

    public function update(UpdateDiagnosisRequest $request, Diagnosis $diagnosis): JsonResponse
    {
        $data = Arr::except($request->validated(), ['image']);
        $previousImage = $diagnosis->image_path;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('diagnoses', 'public');
        }

        $diagnosis->update($data);
        if ($request->hasFile('image') && $previousImage !== null) {
            Storage::disk('public')->delete($previousImage);
        }

        return (new DiagnosisResource($diagnosis->fresh([
            'user:id,name',
            'polybag:id,code',
            'kategori:id,nama_kategori',
        ])))
            ->additional(['message' => 'Diagnosis berhasil diperbarui.'])
            ->response();
    }

    public function destroy(Diagnosis $diagnosis): Response
    {
        $this->authorize('delete', $diagnosis);
        $imagePath = $diagnosis->image_path;
        $diagnosis->delete();
        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return response()->noContent();
    }
}
