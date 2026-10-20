<?php

namespace App\Services;

class DummyDataService
{
    public function dashboard(string $search = ''): array
    {
        $polybags = $this->polybags();
        $scans = array_slice($this->scans(), 0, 3);

        if ($search !== '') {
            $polybags = array_values(array_filter(
                $polybags,
                fn (array $polybag): bool => str_contains(strtolower($polybag['id']), strtolower($search))
            ));
        }

        return [
            'greeting' => 'Selamat pagi, Pak Dadang',
            'updatedAt' => '2 menit lalu',
            'weather' => ['location' => 'Lembang', 'temperature' => 21, 'humidity' => 84, 'condition' => 'Lembap Basah'],
            'activeAlerts' => $this->activeAlertCount(),
            'systemStatus' => 'Sistem beroperasi normal',
            'lastSync' => '10:54 WIB',
            'warning' => [
                'title' => 'Kelembapan mendekati ambang kritis',
                'description' => 'RH di Blok A bertahan pada 84–87% selama 42 menit. Kondisi ini meningkatkan risiko Botrytis.',
                'resolvedDescription' => 'Catatan penanganan tersimpan. Sistem terus memantau kelembapan area.',
                'recommendation' => 'Buka ventilasi sisi timur dan tunda penyiraman selama 60 menit.',
                'detail' => 'RH di Blok A bertahan pada 84–87% selama 42 menit, mendekati ambang kritis 85% dan meningkatkan risiko Botrytis.',
            ],
            'stats' => [
                ['icon' => 'thermo', 'label' => 'Suhu & Kelembapan Udara', 'value' => '21.4°C', 'detail' => '84% RH', 'meta' => 'DHT22 • Blok A'],
                ['icon' => 'alert', 'label' => 'Status Risiko Jamur', 'value' => 'Waspada Tinggi', 'detail' => 'RH mendekati 85%', 'meta' => 'Butuh pemantauan', 'danger' => true],
                ['icon' => 'sprout', 'label' => 'Total Polybag Aktif', 'value' => '120 Pot', 'detail' => '3 blok kebun', 'meta' => '98% sensor online'],
                ['icon' => 'camera', 'label' => 'Total Scan Hari Ini', 'value' => '38 Tanaman', 'detail' => '5 terindikasi', 'meta' => '+12% dari kemarin'],
            ],
            'polybags' => array_slice($polybags, 0, 4),
            'scans' => $scans,
            'climate' => [
                'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                'temperature' => [18.2, 19.4, 21.1, 23.8, 24.5, 22.6, 21.4],
                'humidity' => [81, 84, 79, 87, 83, 88, 84],
                'threshold' => 85,
            ],
        ];
    }

    public function polybags(): array
    {
        return [
            ['id' => 'POT-01', 'block' => 'Blok A', 'water' => 68, 'n' => 42, 'p' => 29, 'k' => 148, 'status' => 'Optimal', 'time' => '10:48'],
            ['id' => 'POT-02', 'block' => 'Blok A', 'water' => 62, 'n' => 38, 'p' => 27, 'k' => 139, 'status' => 'Optimal', 'time' => '10:43'],
            ['id' => 'POT-03', 'block' => 'Blok A', 'water' => 47, 'n' => 28, 'p' => 18, 'k' => 119, 'status' => 'Defisiensi', 'time' => '10:37'],
            ['id' => 'POT-04', 'block' => 'Blok B', 'water' => 71, 'n' => 45, 'p' => 31, 'k' => 152, 'status' => 'Optimal', 'time' => '10:31'],
            ['id' => 'POT-05', 'block' => 'Blok B', 'water' => 39, 'n' => 19, 'p' => 22, 'k' => 126, 'status' => 'Defisiensi', 'time' => '10:24'],
            ['id' => 'POT-06', 'block' => 'Blok B', 'water' => 59, 'n' => 36, 'p' => 25, 'k' => 141, 'status' => 'Optimal', 'time' => '10:16'],
            ['id' => 'POT-07', 'block' => 'Blok C', 'water' => 65, 'n' => 40, 'p' => 28, 'k' => 146, 'status' => 'Optimal', 'time' => '10:08'],
            ['id' => 'POT-08', 'block' => 'Blok C', 'water' => 44, 'n' => 26, 'p' => 17, 'k' => 115, 'status' => 'Defisiensi', 'time' => '09:56'],
        ];
    }

    public function scans(): array
    {
        $images = [
            'https://images.unsplash.com/photo-1588165171080-c89acfa5ee83?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1646206324367-2fb297ece723?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1582757793483-e407049be006?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1518994603110-1912b3272afd?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1646206319332-8536daa98708?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1655288511427-827ced84b453?auto=format&fit=crop&w=900&q=85',
        ];

        return [
            ['id' => 'SCN-938', 'label' => 'Bercak Daun', 'type' => 'Daun', 'pot' => 'POT-05', 'score' => '94.8%', 'image' => $images[3], 'danger' => true, 'time' => '10:42 WIB'],
            ['id' => 'SCN-937', 'label' => 'Buah Matang', 'type' => 'Buah', 'pot' => 'POT-02', 'score' => '98.2%', 'image' => $images[1], 'danger' => false, 'time' => '10:18 WIB'],
            ['id' => 'SCN-936', 'label' => 'Daun Sehat', 'type' => 'Daun', 'pot' => 'POT-07', 'score' => '97.4%', 'image' => $images[2], 'danger' => false, 'time' => '09:54 WIB'],
            ['id' => 'SCN-935', 'label' => 'Powdery Mildew', 'type' => 'Daun', 'pot' => 'POT-03', 'score' => '91.6%', 'image' => $images[4], 'danger' => true, 'time' => '09:31 WIB'],
            ['id' => 'SCN-934', 'label' => 'Buah Matang', 'type' => 'Buah', 'pot' => 'POT-01', 'score' => '96.9%', 'image' => $images[0], 'danger' => false, 'time' => '09:12 WIB'],
            ['id' => 'SCN-933', 'label' => 'Daun Sehat', 'type' => 'Daun', 'pot' => 'POT-06', 'score' => '95.5%', 'image' => $images[5], 'danger' => false, 'time' => '08:47 WIB'],
        ];
    }

    public function alerts(): array
    {
        return [
            ['id' => 1, 'severity' => 'Kritis', 'title' => 'Kelembapan Udara >85% Mempercepat Spora Botrytis', 'location' => 'Blok A • Sensor DHT22-01', 'time' => '12 menit lalu', 'action' => 'Buka ventilasi sisi timur dan hentikan penyiraman selama 60 menit.'],
            ['id' => 2, 'severity' => 'Waspada', 'title' => 'Polybag POT-05 Kekurangan Nitrogen Akut', 'location' => 'Blok B • POT-05', 'time' => '38 menit lalu', 'action' => 'Aplikasikan larutan nutrisi N sesuai dosis koreksi dan ukur ulang dalam 6 jam.'],
            ['id' => 3, 'severity' => 'Info', 'title' => 'Kadar Air POT-08 Mendekati Batas Minimum', 'location' => 'Blok C • POT-08', 'time' => '1 jam lalu', 'action' => 'Jadwalkan irigasi pendek 8–10 menit pada siklus berikutnya.'],
        ];
    }

    public function activeAlertCount(): int
    {
        return count($this->alerts());
    }

    public function thresholds(): array
    {
        return ['humidity' => 85, 'water' => 45, 'n' => 30, 'p' => 20, 'k' => 120];
    }

    public function users(): array
    {
        return [
            ['name' => 'Dadang Suhendar', 'email' => 'dadang@haifresh.id', 'role' => 'Owner', 'last' => 'Hari ini, 10:52'],
            ['name' => 'Dr. Rina Wulandari', 'email' => 'rina@haifresh.id', 'role' => 'Agronomis', 'last' => 'Hari ini, 09:41'],
            ['name' => 'Asep Mulyana', 'email' => 'asep@haifresh.id', 'role' => 'Petani Lapangan', 'last' => 'Kemarin, 16:28'],
            ['name' => 'Yudi Hermawan', 'email' => 'yudi@haifresh.id', 'role' => 'Petani Lapangan', 'last' => '12 Jan 2026'],
        ];
    }
}
