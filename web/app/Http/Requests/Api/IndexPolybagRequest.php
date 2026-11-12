<?php

namespace App\Http\Requests\Api;

use App\Models\Polybag;
use Illuminate\Validation\Rule;

class IndexPolybagRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Polybag::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:100'],
            'block' => ['sometimes', Rule::in(['A', 'B', 'C'])],
            'is_active' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'block.in' => 'Blok harus A, B, atau C.',
            'per_page.between' => 'Jumlah data per halaman harus 1 sampai 100.',
        ];
    }
}
