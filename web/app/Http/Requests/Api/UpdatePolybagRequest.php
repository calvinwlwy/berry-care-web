<?php

namespace App\Http\Requests\Api;

use App\Models\Polybag;
use Illuminate\Validation\Rule;

class UpdatePolybagRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('polybag')) ?? false;
    }

    public function rules(): array
    {
        /** @var Polybag $polybag */
        $polybag = $this->route('polybag');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:50', 'regex:/^POT-[A-Z0-9-]+$/', Rule::unique('polybags', 'code')->ignore($polybag->id)],
            'block' => ['sometimes', 'required', Rule::in(['A', 'B', 'C'])],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'required', 'boolean'],
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
            'is_active.boolean' => 'Status aktif harus berupa boolean.',
        ];
    }
}
