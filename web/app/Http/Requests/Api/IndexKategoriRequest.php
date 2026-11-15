<?php

namespace App\Http\Requests\Api;

use App\Models\Kategori;

class IndexKategoriRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Kategori::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.string' => 'Pencarian kategori harus berupa teks.',
            'q.max' => 'Pencarian kategori maksimal 100 karakter.',
            'per_page.integer' => 'Jumlah data per halaman harus berupa angka.',
            'per_page.between' => 'Jumlah data per halaman harus 1 sampai 100.',
        ];
    }
}
