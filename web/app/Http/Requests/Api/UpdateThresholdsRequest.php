<?php

namespace App\Http\Requests\Api;

use App\Models\Threshold;

class UpdateThresholdsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', Threshold::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'thresholds' => ['required', 'array', 'min:1'],
            'thresholds.humidity_max' => ['sometimes', 'numeric', 'between:0,100'],
            'thresholds.soil_moisture_min' => ['sometimes', 'numeric', 'between:0,100'],
            'thresholds.nitrogen_min' => ['sometimes', 'numeric', 'between:0,1000'],
            'thresholds.phosphorus_min' => ['sometimes', 'numeric', 'between:0,1000'],
            'thresholds.potassium_min' => ['sometimes', 'numeric', 'between:0,1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $values = $this->input('thresholds');
            $allowed = ['humidity_max', 'soil_moisture_min', 'nitrogen_min', 'phosphorus_min', 'potassium_min'];
            if (is_array($values) && array_diff(array_keys($values), $allowed) !== []) {
                $validator->errors()->add('thresholds', 'Kunci ambang batas tidak dikenal.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'thresholds.required' => 'Nilai ambang batas wajib diisi.',
            'thresholds.array' => 'Ambang batas harus berupa objek nilai.',
            'thresholds.min' => 'Minimal satu ambang batas harus diubah.',
            'thresholds.*.numeric' => 'Nilai ambang batas harus berupa angka.',
        ];
    }
}
