<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class UpdateDiagnosisRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('diagnosis')) ?? false;
    }

    public function rules(): array
    {
        return [
            'polybag_id' => ['sometimes', 'nullable', 'integer', 'exists:polybags,id'],
            'kategori_id' => ['sometimes', 'nullable', 'integer', 'exists:kategori,id'],
            'type' => ['sometimes', 'required', Rule::in(['leaf', 'fruit'])],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'confidence' => ['sometimes', 'required', 'numeric', 'between:0,100'],
            'severity' => ['sometimes', 'required', Rule::in(['ok', 'watch', 'action'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'scanned_at' => ['sometimes', 'required', 'date', 'before_or_equal:'.now()->addMinutes(5)->toDateTimeString()],
        ];
    }

    public function messages(): array
    {
        return [
            'confidence.between' => 'Akurasi harus 0 sampai 100.',
            'kategori_id.integer' => 'Kategori diagnosis harus berupa ID angka.',
            'kategori_id.exists' => 'Kategori diagnosis yang dipilih tidak ditemukan.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Foto harus berformat JPG atau PNG.',
            'image.max' => 'Ukuran foto maksimal 5 MB.',
            'scanned_at.before_or_equal' => 'Waktu pemindaian tidak boleh lebih dari 5 menit di masa depan.',
        ];
    }
}
