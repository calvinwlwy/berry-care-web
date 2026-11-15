# Laporan Tugas Pertemuan 12 — Web Service Master Kategori

## A. Audit awal dan akhir

| Butir audit | Status awal | Bukti awal | Status akhir | Bukti akhir |
|---|---|---|---|---|
| 1. Tabel kategori sesuai materi | Belum | Tidak ada migrasi/tabel `kategori`; `diagnoses` belum punya `kategori_id` | Sudah | Dua migrasi aditif membuat tabel master dan FK nullable/indexed dengan `RESTRICT` |
| 2. Model Kategori | Belum | Model dan factory kategori tidak ada | Sudah | `Kategori`, relasi `diagnoses()`, dan `KategoriFactory` tersedia |
| 3. Controller dan lima route CRUD | Belum | `php artisan route:list --path=api/kategori` tidak menemukan route | Sudah | Lima route GET list/detail, POST, PUT/PATCH, DELETE terdaftar |
| 4. Validasi nama/deskripsi | Belum | Tidak ada Form Request kategori; diagnosis belum menerima kategori | Sudah | Store/update trim, validasi nama unik 2–100 karakter dan deskripsi nullable maksimal 1000; validasi `kategori_id` pada diagnosis |
| 5. Status dan error API | Sebagian | Handler API telah memberi JSON 401/403/404; format 405/500 belum diseragamkan dan 409 kategori belum ada | Sudah | 200/201/204, 401/403/404/405/409/422/500 JSON; respons 500 tanpa detail exception |
| 6. Autentikasi dan otorisasi | Sebagian | Sanctum tersedia untuk API; policy kategori belum ada | Sudah | Route kategori di grup Sanctum; semua user terautentikasi boleh membaca, admin saja menulis |
| 7. Relasi kategori-diagnosis | Belum | Diagnosis tidak memiliki kategori | Sudah | FK nullable, model belongsTo/hasMany, filter, kategori resource, dan `diagnoses_count` |
| 8. Dokumentasi API | Belum | Tidak ada dokumen kategori/OpenAPI/Postman khusus | Sudah | Markdown API, desain database, OpenAPI 3.0, dan Postman collection v2.1 |
| 9. Tes otomatis | Belum | Tidak ada feature test kategori | Sudah | 9 tes kategori (83 assertions) dan suite lama tetap lulus |

Audit awal dijalankan sebelum implementasi: route-list kategori kosong dan
`php artisan test` lulus 108 tes (815 assertions). Pada validasi antara,
penanganan generic 500 sempat mengubah respons throttle menjadi 500; handler
`HttpResponseException` diperbaiki dan tes rate-limit API/Google lulus lagi.

## B. Implementasi dan file

**File baru**

- `database/migrations/2026_11_15_000000_create_kategori_table.php`
- `database/migrations/2026_11_15_000001_add_kategori_id_to_diagnoses_table.php`
- `app/Models/Kategori.php`
- `app/Policies/KategoriPolicy.php`
- `app/Http/Controllers/Api/KategoriController.php`
- `app/Http/Requests/Api/IndexKategoriRequest.php`
- `app/Http/Requests/Api/StoreKategoriRequest.php`
- `app/Http/Requests/Api/UpdateKategoriRequest.php`
- `app/Http/Resources/KategoriResource.php`
- `database/factories/KategoriFactory.php`
- `database/seeders/KategoriSeeder.php`
- `tests/Feature/KategoriApiTest.php`
- `docs/api/kategori.md`
- `docs/api/desain-database-kategori.md`
- `docs/api/kategori-openapi.yaml`
- `docs/postman/Berry_Care_Kategori_API.postman_collection.json`
- `docs/bukti-laporan-kategori.md`

**File yang diubah**

- `routes/api.php`
- `bootstrap/app.php`
- `app/Models/Diagnosis.php`
- `app/Http/Controllers/Api/DiagnosisController.php`
- `app/Http/Requests/Api/IndexDiagnosisRequest.php`
- `app/Http/Requests/Api/StoreDiagnosisRequest.php`
- `app/Http/Requests/Api/UpdateDiagnosisRequest.php`
- `app/Http/Resources/DiagnosisResource.php`
- `app/Http/Controllers/Api/PolybagController.php`
- `README.md`
- `docs/backend/endpoint-api.md`
- `docs/backend/erd.md`

Tidak ada perubahan pada aplikasi mobile maupun dashboard Blade.

## C. Cara menjalankan

Dari folder `web/`:

```powershell
php artisan migrate
php artisan db:seed --class=KategoriSeeder
php artisan serve
php artisan route:list --path=api/kategori
php artisan test
vendor\bin\pint --test
```

Migrasi kategori sudah diterapkan dengan `php artisan migrate`; migrasi tidak
mereset tabel atau menghapus data. `KategoriSeeder` idempotent dan tidak
dijalankan otomatis. Untuk Postman, impor
`docs/postman/Berry_Care_Kategori_API.postman_collection.json`, isi `baseUrl`
serta `adminEmail`/`adminPassword` lokal melalui environment/collection
variables, lalu jalankan request berurutan. Kredensial jangan disimpan di
file collection atau screenshot.

Validasi akhir: `php artisan route:list --path=api/kategori` menampilkan lima
route; kedua migrasi kategori berstatus `Ran`; `php artisan test` lulus
117 tes (898 assertions); Pint lulus untuk seluruh file PHP yang dibuat atau
diubah pada tugas ini. Postman JSON dan script ter-parse, kredensial admin
collection kosong, dan OpenAPI YAML 3.0.3 berhasil diparse.

## D. Bukti laporan berdasarkan komponen penilaian

### 1. Desain Database — 20%

**Screenshot**

1. **Wajib — migrasi diterapkan.** Jalankan `php artisan migrate:status` di
   terminal dari folder `web/`. Tampilkan kedua migrasi `create_kategori`
   dan `add_kategori_id_to_diagnoses` berstatus `Ran`; jangan membuka `.env`.
2. **Wajib — diagram relasi.** Buka
   `docs/api/desain-database-kategori.md` pada bagian ERD Mermaid. Tampilkan
   `kategori` satu-ke-banyak ke `diagnoses` dan field `kategori_id` nullable.
3. **Opsional — kamus data.** Tampilkan tabel kamus `kategori` dengan panjang
   nama 100, unique key, deskripsi nullable, timestamps, dan aturan FK.

**Kode**

1. **Wajib —** `database/migrations/2026_11_15_000000_create_kategori_table.php`,
   method `up()`: membuktikan nama tabel serta tipe dan constraint kolom.
2. **Wajib —** `database/migrations/2026_11_15_000001_add_kategori_id_to_diagnoses_table.php`,
   method `up()`: membuktikan FK nullable, indeks, dan `restrictOnDelete()`.
3. **Opsional —** `database/seeders/KategoriSeeder.php`, method `run()`:
   membuktikan lima kategori awal dan operasi idempotent.

### 2. API CRUD Berfungsi — 40%

**Screenshot**

1. **Wajib — daftar route.** Jalankan
   `php artisan route:list --path=api/kategori`. Tampilkan semua lima operasi
   kategori, termasuk PUT/PATCH pada route update.
2. **Wajib — Create dan Read.** Jalankan folder Postman
   `01 - Login admin`, `02 - Buat dan login akun petani untuk tes otorisasi`,
   `03 - Baca dan cari kategori`, dan request tambah/detail di
   `04 - Tambah kategori dan validasi`. Ambil screenshot respons 201 berisi
   `data` dan pesan, lalu GET detail 200.
3. **Wajib — Update.** Jalankan `[200] Ubah kategori (200)` di folder
   `05 - Ubah kategori dan validasi`. Tampilkan status 200, nama baru, dan
   pesan berhasil.
4. **Wajib — Delete.** Jalankan `[204] Hapus kategori yang tidak dipakai`
   pada folder `07 - Hapus kategori`. Tampilkan status 204.
5. **Wajib — relasi diagnosis.** Jalankan folder `06 - Hubungkan kategori ke
   diagnosis`. Tampilkan diagnosis dengan `kategori_id`/objek `kategori`, lalu
   daftar diagnosis terfilter.
6. **Opsional — jumlah diagnosis.** Tampilkan `diagnoses_count` pada GET
   kategori setelah data diagnosis dibuat.

**Kode**

1. **Wajib —** `app/Http/Controllers/Api/KategoriController.php`, methods
   `index`, `store`, `show`, `update`, `destroy`: membuktikan lima operasi
   CRUD, pagination, dan konflik saat kategori terpakai.
2. **Wajib —** `routes/api.php`, grup `auth:sanctum` dan `apiResource('kategori')`:
   membuktikan route kategori dilindungi autentikasi.
3. **Wajib —** `app/Http/Controllers/Api/DiagnosisController.php`, methods
   `index`, `store`, `show`, `update`: menunjukkan filter, penyimpanan, dan
   pemuatan relasi kategori.
4. **Opsional —** `app/Http/Resources/KategoriResource.php` dan
   `app/Http/Resources/DiagnosisResource.php`: menunjukkan bentuk JSON API.

### 3. Validasi dan Error Handling — 20%

**Screenshot**

1. **Wajib — validasi input.** Di folder Postman
   `04 - Tambah kategori dan validasi`, jalankan request kategori kosong,
   lebih dari 100 karakter, dan duplikat. Tampilkan status 422 beserta
   `message` dan `errors`.
2. **Wajib — otorisasi.** Jalankan `[403] Petani mencoba menambah kategori`
   di folder `08 - Error resource dan autentikasi`. Tampilkan JSON
   `Akses ditolak.`.
3. **Wajib — kategori terpakai.** Jalankan POST diagnosis di folder 06,
   kemudian `[409] Hapus kategori yang masih dipakai diagnosis` di folder 07.
   Tampilkan pesan konflik yang menjelaskan relasi.
4. **Wajib — resource dan autentikasi.** Di folder 08, tampilkan GET ID tidak
   ada dengan 404 dan GET tanpa token dengan 401.
5. **Opsional — status metode/server.** Gunakan hasil Feature test untuk
   menunjukkan 405 JSON dan 500 JSON generik tanpa stack trace.

**Kode**

1. **Wajib —** `app/Http/Requests/Api/StoreKategoriRequest.php` dan
   `UpdateKategoriRequest.php`, methods `prepareForValidation`, `rules`,
   `messages`: membuktikan trim, validasi, serta pesan Indonesia.
2. **Wajib —** `app/Policies/KategoriPolicy.php`, methods `viewAny`, `view`,
   `create`, `update`, `delete`: membuktikan semua user login dapat membaca
   dan hanya admin dapat menulis.
3. **Wajib —** `bootstrap/app.php`, registrasi renderer exception API:
   menunjukkan format 401/403/404/405/500 yang konsisten.
4. **Opsional —** `app/Http/Requests/Api/StoreDiagnosisRequest.php` dan
   `UpdateDiagnosisRequest.php`: menunjukkan validasi kategori diagnosis.

### 4. Dokumentasi API — 20%

**Screenshot**

1. **Wajib — dokumentasi endpoint.** Buka `docs/api/kategori.md`, tampilkan
   autentikasi, tabel endpoint, contoh request/response, status, dan aturan
   validasi.
2. **Wajib — OpenAPI.** Buka `docs/api/kategori-openapi.yaml`, tampilkan
   `openapi: 3.0.3`, operasi kategori, skema request/resource, dan security
   Bearer.
3. **Wajib — Postman.** Impor collection dan tampilkan folder urutan tes,
   variabel `baseUrl` serta kredensial kosong, dan hasil Runner dengan
   assertion status/body lulus.
4. **Opsional — dokumentasi database.** Tampilkan
   `docs/api/desain-database-kategori.md` dan penjelasan nama unik,
   `RESTRICT`, serta `kategori_id` nullable.

**Kode/dokumen**

1. **Wajib —** `docs/api/kategori.md`, bagian Endpoint, Kode status, dan
   Validasi: menjadi kontrak penggunaan API berbahasa Indonesia.
2. **Wajib —** `docs/api/kategori-openapi.yaml`, `paths` dan `components`:
   memberi spesifikasi yang dapat diimpor ke tooling OpenAPI.
3. **Wajib —** `docs/postman/Berry_Care_Kategori_API.postman_collection.json`,
   collection `event.test` dan folder skenario: menyediakan pengujian
   client-server yang dapat diulang.
4. **Opsional —** `README.md`, bagian “Master Kategori”: memberi langkah
   migrasi, seeder, dokumentasi, dan tes.

## E. Keterbatasan dan lingkup

- API kategori hanya backend; integrasi ke aplikasi mobile dan dashboard
  Blade sengaja tidak dikerjakan sesuai instruksi Pertemuan 13.
- `kategori_id` nullable agar diagnosis lama dan diagnosis tanpa klasifikasi
  tetap valid.
- Perubahan skema selanjutnya harus dibuat sebagai migrasi aditif baru.
- Postman menggunakan akun admin lokal untuk membuat akun petani uji. Jalankan
  collection pada database pengujian; request pembersihan menghapus akun
  petani dan data uji yang dibuat, tetapi tidak menghapus kategori awal.
- Tidak ada kredensial, isi `.env`, atau stack trace internal di dokumentasi
  maupun collection.
