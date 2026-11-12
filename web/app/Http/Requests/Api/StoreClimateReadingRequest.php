<?php

namespace App\Http\Requests\Api;

use App\Models\ClimateReading;

class StoreClimateReadingRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ClimateReading::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'temperature' => ['required', 'numeric', 'between:-10,60'],
            'humidity' => ['required', 'numeric', 'between:0,100'],
            'recorded_at' => ['required', 'date', 'before_or_equal:'.now()->addMinutes(5)->toDateTimeString()],
        ];
    }

    public function messages(): array
    {
        return [
            'temperature.between' => 'Suhu harus -10 sampai 60 derajat Celsius.',
            'humidity.between' => 'Kelembapan harus 0 sampai 100 persen.',
            'recorded_at.before_or_equal' => 'Waktu pembacaan tidak boleh lebih dari 5 menit di masa depan.',
        ];
    }
}
