<?php

namespace App\Http\Requests\Api;

use App\Models\Alert;

class IndexAlertRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Alert::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:active,resolved'],
            'severity' => ['sometimes', 'in:info,warning,critical'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status harus active atau resolved.',
            'severity.in' => 'Tingkat peringatan tidak valid.',
            'per_page.between' => 'Jumlah data per halaman harus 1 sampai 100.',
        ];
    }
}
