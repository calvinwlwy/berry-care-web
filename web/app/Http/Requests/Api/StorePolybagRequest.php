<?php

namespace App\Http\Requests\Api;

use App\Models\Polybag;
use Illuminate\Validation\Rule;

class StorePolybagRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Polybag::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^POT-[A-Z0-9-]+$/', 'unique:polybags,code'],
            'block' => ['required', Rule::in(['A', 'B', 'C'])],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode polybag wajib diisi.',
            'code.regex' => 'Format kode harus diawali POT-.',
            'code.unique' => 'Kode polybag sudah digunakan.',
            'block.required' => 'Blok wajib diisi.',
            'block.in' => 'Blok harus A, B, atau C.',
            'location.string' => 'Lokasi harus berupa teks.',
            'is_active.boolean' => 'Status aktif harus berupa boolean.',
        ];
    }
}
