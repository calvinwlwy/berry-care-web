<?php

namespace App\Http\Requests\Api;

use App\Models\SensorReading;

class StoreSensorReadingRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SensorReading::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'soil_moisture' => ['required', 'numeric', 'between:0,100'],
            'nitrogen' => ['required', 'numeric', 'between:0,1000'],
            'phosphorus' => ['required', 'numeric', 'between:0,1000'],
            'potassium' => ['required', 'numeric', 'between:0,1000'],
            'recorded_at' => ['required', 'date', 'before_or_equal:'.now()->addMinutes(5)->toDateTimeString()],
        ];
    }

    public function messages(): array
    {
        return [
            'soil_moisture.required' => 'Kadar air tanah wajib diisi.',
            'soil_moisture.between' => 'Kadar air tanah harus 0 sampai 100 persen.',
            'nitrogen.between' => 'Nitrogen harus 0 sampai 1000.',
            'phosphorus.between' => 'Fosfor harus 0 sampai 1000.',
            'potassium.between' => 'Kalium harus 0 sampai 1000.',
            'recorded_at.required' => 'Waktu pembacaan wajib diisi.',
            'recorded_at.before_or_equal' => 'Waktu pembacaan tidak boleh lebih dari 5 menit di masa depan.',
        ];
    }
}
