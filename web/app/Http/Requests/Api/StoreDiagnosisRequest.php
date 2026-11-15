<?php

namespace App\Http\Requests\Api;

use App\Models\Diagnosis;

class StoreDiagnosisRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Diagnosis::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'polybag_id' => ['nullable', 'integer', 'exists:polybags,id'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori,id'],
            'type' => ['required', 'in:leaf,fruit'],
            'label' => ['required', 'string', 'max:255'],
            'confidence' => ['required', 'numeric', 'between:0,100'],
            'severity' => ['required', 'in:ok,watch,action'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'scanned_at' => ['required', 'date', 'before_or_equal:'.now()->addMinutes(5)->toDateTimeString()],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Jenis diagnosis harus leaf atau fruit.',
            'kategori_id.integer' => 'Kategori diagnosis harus berupa ID angka.',
            'kategori_id.exists' => 'Kategori diagnosis yang dipilih tidak ditemukan.',
            'confidence.between' => 'Akurasi harus 0 sampai 100.',
            'severity.in' => 'Tingkat diagnosis harus ok, watch, atau action.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Foto harus berformat JPG atau PNG.',
            'image.max' => 'Ukuran foto maksimal 5 MB.',
            'scanned_at.before_or_equal' => 'Waktu pemindaian tidak boleh lebih dari 5 menit di masa depan.',
        ];
    }
}
