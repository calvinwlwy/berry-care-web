<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DiagnosisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'polybag_id' => $this->polybag_id,
            'kategori_id' => $this->kategori_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'label' => $this->label,
            'confidence' => $this->confidence,
            'severity' => $this->severity,
            'notes' => $this->notes,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'scanned_at' => $this->scanned_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'kategori' => $this->whenLoaded('kategori', fn () => $this->kategori ? [
                'id' => $this->kategori->id,
                'nama_kategori' => $this->kategori->nama_kategori,
            ] : null),
        ];
    }
}
