<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Penyakit Daun',
            'Penyakit Buah',
            'Hama',
            'Defisiensi Nutrisi',
            'Tanaman Sehat',
        ];

        foreach ($categories as $namaKategori) {
            Kategori::query()->updateOrCreate(
                ['nama_kategori' => $namaKategori],
                ['deskripsi' => null],
            );
        }
    }
}
