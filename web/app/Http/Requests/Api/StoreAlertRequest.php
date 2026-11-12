<?php

namespace App\Http\Requests\Api;

use App\Models\Alert;

class StoreAlertRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Alert::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'polybag_id' => ['nullable', 'integer', 'exists:polybags,id'],
            'type' => ['required', 'string', 'max:100'],
            'severity' => ['required', 'in:info,warning,critical'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'severity.in' => 'Tingkat peringatan harus info, warning, atau critical.',
        ];
    }
}
