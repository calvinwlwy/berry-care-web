<?php

namespace App\Http\Requests\Api;

use App\Models\Diagnosis;

class IndexDiagnosisRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Diagnosis::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'in:leaf,fruit'],
            'polybag_id' => ['sometimes', 'integer', 'exists:polybags,id'],
            'kategori_id' => ['sometimes', 'integer', 'exists:kategori,id'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Jenis diagnosis harus leaf atau fruit.',
            'kategori_id.integer' => 'Kategori diagnosis harus berupa ID angka.',
            'kategori_id.exists' => 'Kategori diagnosis yang dipilih tidak ditemukan.',
            'per_page.between' => 'Jumlah data per halaman harus 1 sampai 100.',
        ];
    }
}
