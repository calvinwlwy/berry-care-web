<?php

namespace App\Http\Requests\Api;

use App\Models\Kategori;

class StoreKategoriRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_kategori' => is_string($this->input('nama_kategori'))
                ? trim($this->input('nama_kategori'))
                : $this->input('nama_kategori'),
            'deskripsi' => is_string($this->input('deskripsi'))
                ? trim($this->input('deskripsi'))
                : $this->input('deskripsi'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('create', Kategori::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'min:2', 'max:100', 'unique:kategori,nama_kategori'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.string' => 'Nama kategori harus berupa teks.',
            'nama_kategori.min' => 'Nama kategori minimal 2 karakter.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
            'nama_kategori.unique' => 'Nama kategori sudah digunakan.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
        ];
    }
}
