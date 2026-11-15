<?php

namespace App\Models;

use Database\Factories\KategoriFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    /** @use HasFactory<KategoriFactory> */
    use HasFactory;

    protected $table = 'kategori';

    protected $fillable = ['nama_kategori', 'deskripsi'];

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }
}
