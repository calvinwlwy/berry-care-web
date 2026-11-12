<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\ClimateReading;
use App\Models\Diagnosis;
use App\Models\Polybag;
use App\Models\SensorReading;
use App\Models\Threshold;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BerryCareDemoSeeder extends Seeder
{
    public function run(): void
    {
        $thresholds = [
            'humidity_max' => ['Kelembapan udara maksimum', 85, '%'],
            'soil_moisture_min' => ['Kadar air tanah minimum', 45, '%'],
            'nitrogen_min' => ['Nitrogen minimum', 30, 'ppm'],
            'phosphorus_min' => ['Fosfor minimum', 20, 'ppm'],
            'potassium_min' => ['Kalium minimum', 120, 'ppm'],
        ];
        foreach ($thresholds as $key => [$label, $value, $unit]) {
            Threshold::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $label, 'value' => $value, 'unit' => $unit]
            );
        }

        $blocks = ['A', 'A', 'A', 'B', 'B', 'B', 'C', 'C'];
        $readings = [
            [68, 42, 29, 148],
            [62, 38, 27, 139],
            [47, 28, 18, 119],
            [71, 45, 31, 152],
            [39, 19, 22, 126],
            [59, 36, 25, 141],
            [65, 40, 28, 146],
            [44, 26, 17, 115],
        ];
        $polybags = [];
        for ($number = 1; $number <= 8; $number++) {
            $code = sprintf('POT-%02d', $number);
            $polybag = Polybag::query()->updateOrCreate(
                ['code' => $code],
                ['block' => $blocks[$number - 1], 'location' => 'Bedeng '.$blocks[$number - 1], 'is_active' => true]
            );
            $polybags[$code] = $polybag;

            foreach ([2, 1] as $hoursAgo) {
                $recordedAt = now()->startOfDay()
                    ->addHours($hoursAgo === 2 ? 8 : 9)
                    ->addMinutes($number);
                [$moisture, $nitrogen, $phosphorus, $potassium] = $readings[$number - 1];
                SensorReading::query()->updateOrCreate(
                    ['polybag_id' => $polybag->id, 'recorded_at' => $recordedAt],
                    [
                        'soil_moisture' => $moisture,
                        'nitrogen' => $nitrogen,
                        'phosphorus' => $phosphorus,
                        'potassium' => $potassium,
                    ]
                );
            }
        }

        foreach ([['08:00', 20.5, 76], ['12:00', 23.2, 87], ['16:00', 21.8, 82]] as [$time, $temperature, $humidity]) {
            $recordedAt = now()->startOfDay()->setTimeFromTimeString($time);
            ClimateReading::query()->updateOrCreate(
                ['recorded_at' => $recordedAt],
                ['temperature' => $temperature, 'humidity' => $humidity]
            );
        }

        $operator = User::query()->where('role', 'petani')->first()
            ?? User::query()->first();
        if ($operator === null) {
            $operator = User::query()->create([
                'name' => 'Operator Demo Berry Care',
                'email' => 'operator.demo@berrycare.test',
                'password' => Hash::make(Str::random(48)),
                'role' => 'petani',
            ]);
        }

        $diagnoses = [
            ['POT-05', 'leaf', 'Bercak Daun', 94.8, 'action'],
            ['POT-02', 'fruit', 'Buah Matang', 98.2, 'ok'],
            ['POT-07', 'leaf', 'Daun Sehat', 97.4, 'ok'],
        ];
        foreach ($diagnoses as $index => [$code, $type, $label, $confidence, $severity]) {
            $scannedAt = now()->startOfDay()->addHours(10)->addMinutes($index);
            Diagnosis::query()->updateOrCreate(
                ['user_id' => $operator->id, 'polybag_id' => $polybags[$code]->id, 'scanned_at' => $scannedAt],
                [
                    'type' => $type,
                    'label' => $label,
                    'confidence' => $confidence,
                    'severity' => $severity,
                    'notes' => 'Data contoh untuk demonstrasi API.',
                    'image_path' => null,
                ]
            );
        }

        Alert::query()->updateOrCreate(
            ['type' => 'demo_humidity_risk', 'source' => 'manual', 'status' => 'active'],
            [
                'polybag_id' => null,
                'severity' => 'warning',
                'message' => 'Data contoh: kelembapan area kebun perlu dipantau.',
                'resolved_at' => null,
                'resolved_by' => null,
            ]
        );
        Alert::query()->updateOrCreate(
            ['type' => 'demo_nitrogen_low', 'source' => 'manual', 'status' => 'active'],
            [
                'polybag_id' => $polybags['POT-05']->id,
                'severity' => 'warning',
                'message' => 'Data contoh: kadar nitrogen perlu diperiksa.',
                'resolved_at' => null,
                'resolved_by' => null,
            ]
        );
    }
}
