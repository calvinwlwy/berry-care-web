<?php

namespace App\Http\Requests\Api;

use App\Models\SensorReading;
use Illuminate\Support\Carbon;

class IndexReadingsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', SensorReading::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('from') || ! $this->filled('to')
                || $validator->errors()->has('from') || $validator->errors()->has('to')) {
                return;
            }

            if (Carbon::parse($this->input('to'))->lt(Carbon::parse($this->input('from')))) {
                $validator->errors()->add('to', 'Tanggal akhir harus sama atau setelah tanggal awal.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'to.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'per_page.between' => 'Jumlah data per halaman harus 1 sampai 100.',
        ];
    }
}
