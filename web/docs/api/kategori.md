# Web Service Master Kategori Diagnosis

## Ringkasan

API master kategori mengelompokkan diagnosis tanaman Berry Care. Semua route
memerlukan token Sanctum. Pengguna `admin` dapat membuat, mengubah, dan
menghapus kategori; pengguna `petani` dan `admin` dapat membaca kategori.

Base URL lokal:

```text
http://127.0.0.1:8000/api
```

## Autentikasi

1. Login melalui `POST /api/login` dengan email dan password.
2. Salin nilai `token` dari respons.
3. Sertakan header pada request kategori:

```http
Authorization: Bearer <token>
Accept: application/json
```

## Endpoint

| Metode | URL | Peran | Fungsi |
|---|---|---|---|
| GET | `/api/kategori` | Admin, petani | Daftar, pencarian, dan paginasi kategori |
| GET | `/api/kategori/{id}` | Admin, petani | Detail kategori dan jumlah diagnosis |
| POST | `/api/kategori` | Admin | Membuat kategori |
| PUT/PATCH | `/api/kategori/{id}` | Admin | Memperbarui kategori |
| DELETE | `/api/kategori/{id}` | Admin | Menghapus kategori yang belum dipakai |

### Query daftar

| Parameter | Tipe | Aturan | Default |
|---|---|---|---|
| `q` | string | Mencari substring pada `nama_kategori`, maksimum 100 karakter | Tidak difilter |
| `per_page` | integer | 1–100 | 15 |
| `page` | integer | Nomor halaman paginator Laravel | 1 |

Hasil diurutkan menaik berdasarkan `nama_kategori`. Respons list mengikuti
format resource Laravel: `data`, `links`, dan `meta`. Item berisi
`diagnoses_count`.

## Contoh request dan respons

### Daftar dan cari kategori

```http
GET /api/kategori?q=Penyakit&per_page=10
Authorization: Bearer <token>
Accept: application/json
```

```json
{
  "data": [
    {
      "id": 1,
      "nama_kategori": "Penyakit Daun",
      "deskripsi": null,
      "diagnoses_count": 2,
      "created_at": "2026-11-15T04:00:00.000000Z",
      "updated_at": "2026-11-15T04:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/kategori?page=1",
    "last": "http://127.0.0.1:8000/api/kategori?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://127.0.0.1:8000/api/kategori",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

### Detail kategori

```http
GET /api/kategori/1
Authorization: Bearer <token>
Accept: application/json
```

```json
{
  "data": {
    "id": 1,
    "nama_kategori": "Penyakit Daun",
    "deskripsi": null,
    "diagnoses_count": 2,
    "created_at": "2026-11-15T04:00:00.000000Z",
    "updated_at": "2026-11-15T04:00:00.000000Z"
  }
}
```

### Tambah kategori

```http
POST /api/kategori
Authorization: Bearer <admin-token>
Content-Type: application/json
Accept: application/json
```

```json
{
  "nama_kategori": "Penyakit Daun",
  "deskripsi": "Gangguan yang menyerang daun tanaman."
}
```

Respons `201 Created`:

```json
{
  "data": {
    "id": 1,
    "nama_kategori": "Penyakit Daun",
    "deskripsi": "Gangguan yang menyerang daun tanaman.",
    "diagnoses_count": 0,
    "created_at": "2026-11-15T04:00:00.000000Z",
    "updated_at": "2026-11-15T04:00:00.000000Z"
  },
  "message": "Kategori berhasil ditambahkan."
}
```

### Ubah kategori

`PUT` dan `PATCH` menerima format yang sama. Nama kategori wajib disertakan.

```http
PATCH /api/kategori/1
Authorization: Bearer <admin-token>
Content-Type: application/json
Accept: application/json
```

```json
{
  "nama_kategori": "Penyakit Daun Stroberi",
  "deskripsi": "Kategori diagnosis untuk gangguan daun."
}
```

Respons `200 OK` berisi resource di bawah `data` dan pesan
`Kategori berhasil diperbarui.`.

### Hapus kategori

```http
DELETE /api/kategori/1
Authorization: Bearer <admin-token>
Accept: application/json
```

Respons `204 No Content` jika kategori tidak dipakai diagnosis. Kategori yang
masih direferensikan menolak penghapusan dengan `409 Conflict`.

## Hubungan diagnosis

`kategori_id` opsional pada `POST /api/diagnoses` dan `PUT/PATCH
/api/diagnoses/{id}`. Nilainya harus `null` atau ID kategori yang ada.
`GET /api/diagnoses` dapat difilter dengan `kategori_id`. Resource diagnosis
memuat `kategori_id` dan objek `kategori` berisi `id` serta `nama_kategori`
bila relasinya dimuat.

```json
{
  "kategori_id": 1,
  "type": "leaf",
  "label": "Bercak daun",
  "confidence": 92,
  "severity": "watch",
  "scanned_at": "2026-11-15T04:00:00Z"
}
```

## Kode status dan error

| Status | Makna |
|---:|---|
| 200 | Berhasil membaca atau memperbarui |
| 201 | Kategori berhasil dibuat |
| 204 | Kategori berhasil dihapus, tanpa body |
| 401 | Token hilang atau tidak valid |
| 403 | Pengguna login tidak memiliki peran admin |
| 404 | ID tidak ditemukan atau format ID tidak cocok |
| 405 | Metode HTTP tidak diizinkan |
| 409 | Kategori masih direferensikan oleh diagnosis |
| 422 | Input tidak lolos validasi |
| 500 | Kesalahan server; detail dicatat di log, tidak dikirim ke klien |

Contoh respons:

```json
{"message":"Tidak terautentikasi."}
```

```json
{"message":"Akses ditolak."}
```

```json
{"message":"Data tidak ditemukan."}
```

```json
{"message":"Kategori masih dipakai oleh data diagnosis dan tidak dapat dihapus."}
```

```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "nama_kategori": ["Nama kategori wajib diisi."]
  }
}
```

## Validasi dan otorisasi

- `nama_kategori`: wajib, string, setelah trim 2–100 karakter, unik di tabel
  `kategori`. Update mengabaikan kategori yang sedang diperbarui.
- `deskripsi`: opsional/null, string maksimal 1000 karakter.
- Spasi di awal dan akhir `nama_kategori` serta `deskripsi` dipangkas sebelum
  validasi dan penyimpanan.
- `q`: string maksimal 100 karakter; `per_page`: integer 1–100.
- Semua pengguna terautentikasi dapat membaca. Hanya role `admin` dapat
  menambah, mengubah, dan menghapus kategori.
- Field role tidak diterima sebagai field yang dapat diubah pada API kategori.
- Penghapusan kategori yang masih dipakai diagnosis ditolak; relasi database
  juga menggunakan foreign key `RESTRICT`.

## Menjalankan dokumentasi dan tes

Collection Postman: `docs/postman/Berry_Care_Kategori_API.postman_collection.json`.
Kontrak OpenAPI: `docs/api/kategori-openapi.yaml`. Jalankan tes dengan
`php artisan test --filter=KategoriApiTest`.
