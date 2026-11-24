<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'role' => ['sometimes', 'nullable', Rule::in(['admin', 'petani'])],
        ];
    }

    public function messages(): array
    {
        return [
            'q.string' => 'Pencarian harus berupa teks.',
            'q.max' => 'Pencarian maksimal 255 karakter.',
            'role.in' => 'Peran yang dipilih tidak valid.',
        ];
    }
}
