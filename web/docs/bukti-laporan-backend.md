# Bukti laporan build backend Berry Care

## A. Audit awal dan akhir

| Butir | Status awal | Bukti awal | Status akhir | Bukti akhir |
|---|---|---|---|---|
| 1.1 Project backend siap | Sudah | Laravel 12 di `composer.json`; `php artisan route:list` berjalan | Sudah | Dependensi dan rute domain terdaftar |
| 1.2 Struktur/config awal | Sebagian | README masih template Laravel; `.env.example` belum mengarahkan ke DB proyek | Sudah | README setup lokal, config MySQL example, struktur folder didokumentasikan |
| 1.3 DB dan migrasi | Sebagian | Migrasi users, Sanctum, role dan google_id sudah dijalankan; domain belum ada | Sudah | Migrasi domain aditif menambah 6 tabel dan 5 threshold bawaan |
| 2.1 API sesuai kebutuhan | Sebagian | Auth dan CRUD users saja | Sudah | API polybag, sensor, iklim, diagnosis, alert, threshold dan ringkasan |
| 2.2 CRUD entitas inti | Belum | Tidak ada model/tabel domain atau route CRUD | Sudah | Polybag dan diagnosis CRUD; pembacaan append-only; alert dan threshold sesuai kontrak |
| 2.3 Pengolahan data | Belum | Dashboard web menggunakan data dummy | Sudah | `ThresholdEvaluator` menghasilkan status dan menyinkronkan alert otomatis |
| 2.4 Autentikasi | Sudah | Sanctum API; login/register/logout dan SSO Google | Sudah | Tetap dipertahankan, data pengguna sensitif tidak dimasukkan Resource domain |
| 2.5 Otorisasi | Sebagian | CRUD user dibatasi admin, pembagian role domain belum ada | Sudah | Policy untuk admin/petani per entitas |
| 2.6 Endpoint dan respons teruji | Sebagian | route:list menunjukkan 25 route; baseline 97 lulus, 1 gagal karena assertion sapaan dashboard yang usang | Sudah | Feature tests domain dan seluruh suite lulus; response JSON 401/403/404/422 diverifikasi |

Catatan audit: kegagalan tes dashboard awal adalah ekspektasi teks lama di
`DashboardTest`; assertion diselaraskan dengan sapaan yang sudah ada pada
dashboard, tanpa mengubah tampilan. Tes awal dijalankan dengan database
testing in-memory.

## B. File

File yang dibuat/diubah dapat dilihat melalui `git status --short` pada
workspace. Perubahan mencakup model, factory, policy, Form Request,
Resource, controller API, service evaluator, migrasi domain, seeder demo,
route, exception handler API, rate limiter, feature test, README, dan
dokumentasi.

## C. Cara menjalankan

Dari folder `web/`, siapkan database MySQL `berry_care_web`, salin
`.env.example` menjadi `.env` lalu isi pengaturan lokal secara privat.
Jalankan:

```powershell
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=BerryCareDemoSeeder
php artisan storage:link
php artisan serve
```

Validasi dan dokumentasi:

```powershell
php artisan route:list
php artisan test
vendor\bin\pint --test
```

Impor `docs/postman/Berry_Care_Backend_API.postman_collection.json`, isi
`baseUrl`, `adminEmail`, dan `adminPassword` dengan nilai lokal, lalu jalankan
sepuluh folder secara berurutan. Delapan folder pertama adalah skenario bukti
screenshot; folder kesembilan dan kesepuluh menjalankan CRUD polybag dan
diagnosis (Read daftar, Create, Read detail, Update, Delete). Folder pertama
membuat akun petani dan polybag uji sebagai prasyarat. Pilih file JPG/PNG
pada request diagnosis screenshot yang opsional.

Request CRUD menghapus kembali polybag dan diagnosis CRUD yang dibuatnya.
Akun petani dan polybag prasyarat, pembacaan sensor append-only, serta alert
hasil evaluasi tetap tersimpan; diagnosis dari skenario screenshot juga tetap
ada jika request opsional itu dijalankan. Gunakan database pengujian/disposable
bila tidak ingin menyimpan data demonstrasi.

## D. Bukti untuk presentasi/laporan

### Bagian 1 — Persiapan project

**Screenshot**

1. **Wajib — daftar route.** Jalankan `php artisan route:list --path=api`
   dari `web/`; foto terminal yang menampilkan auth dan kelompok endpoint
   Berry Care.
2. **Wajib — struktur dan dependensi.** Tampilkan `composer.json` pada
   bagian `require` dan Explorer VS Code pada folder `app/`, `routes/`,
   `database/`, serta `tests/`.
3. **Opsional — migrasi.** Jalankan `php artisan migrate:status`; foto
   daftar migrasi tanpa membuka `.env`.

**Kode**

1. **Wajib —** `web/database/migrations/2026_11_12_000000_create_berry_care_domain_tables.php`,
   bagian `up()`: menunjukkan tabel sensor dan FK tanpa mengganti tabel
   akun yang sudah ada.
2. **Wajib —** `web/routes/api.php`, grup `auth:sanctum` dan `throttle:api`:
   membuktikan kontrak rute dan perlindungan endpoint domain.
3. **Opsional —** `web/README.md`, bagian “Persiapan project lokal”:
   membuktikan petunjuk setup dari awal.

### Bagian 2 — Implementasi backend

**Postman collection:** [Berry_Care_Backend_API.postman_collection.json](./postman/Berry_Care_Backend_API.postman_collection.json).
Collection ini memuat delapan skenario screenshot Bagian 2 dan dua folder
tambahan untuk uji CRUD penuh polybag dan diagnosis. Beberapa skenario memuat
request persiapan/prasyarat untuk mendapatkan token atau menyiapkan data.

**Screenshot**

1. **Wajib — akses petani.** Di Postman, login petani lalu GET
   `/api/polybags`; foto status 200 dan daftar/polybag berhalaman.
2. **Wajib — otorisasi admin.** Dengan token petani POST `/api/polybags`;
   foto status 403 dan `Akses ditolak.`.
3. **Wajib — validasi.** POST pembacaan dengan `soil_moisture: 101`; foto
   status 422 dan detail `errors`.
4. **Wajib — pengolahan data.** POST pembacaan di bawah threshold; foto
   response pembacaan dan GET `/api/alerts?status=active` yang menunjukkan
   alert system terkait.
5. **Wajib — auto-resolve.** POST pembacaan normal untuk polybag yang sama,
   kemudian foto alert sebelumnya berstatus resolved.
6. **Wajib — ringkasan dashboard API.** GET `/api/dashboard/summary`; foto
   jumlah status, severity alert, iklim terbaru, diagnosis dan rerata 7 hari.
7. **Opsional — diagnosis/foto.** POST `/api/diagnoses` dengan foto JPG/PNG
   kurang dari 5 MB; foto response `image_path`/`image_url`.
8. **Opsional — tanpa token.** GET `/api/thresholds` tanpa Authorization;
   foto status 401 dan JSON.

**Kode**

1. **Wajib —** `web/app/Services/ThresholdEvaluator.php`, `evaluate()` dan
   `synchronizeReadingAlerts()`: menjelaskan pengolahan nilai sensor dan
   deduplikasi/penyelesaian alert.
2. **Wajib —** `web/app/Policies/PolybagPolicy.php` dan policy entitas lain,
   method `create/update/delete`: membuktikan hak admin/petani.
3. **Wajib —** `web/app/Http/Controllers/Api/SensorReadingController.php`,
   `store()`: menunjukkan transaksi penyimpanan dan pemanggilan evaluator.
4. **Wajib —** `web/app/Http/Controllers/Api/DashboardSummaryController.php`,
   `__invoke()`: menunjukkan ringkasan operasional kebun.
5. **Opsional —** `web/app/Http/Requests/Api/StoreDiagnosisRequest.php`,
   `rules()`: menunjukkan batas ukuran dan tipe file.
6. **Opsional —** `web/tests/Feature/BerryCareApiTest.php`: pilih tes evaluasi
   threshold, otorisasi dan ringkasan untuk membuktikan perilaku teruji.

### Keterbatasan

- Dashboard Blade lama masih memakai `DummyDataService`; endpoint API domain
  sudah memakai database, tetapi belum dihubungkan ke tampilan web.
- Foto disimpan pada disk public lokal; deployment perlu mengatur persistent
  storage/backup dan menjalankan `php artisan storage:link`.
- Pembacaan sensor dan iklim tidak menyediakan endpoint edit/hapus sesuai
  kebijakan append-only; pembacaan iklim yang dikirim Postman tetap tersimpan.
- Data demo tidak menggantikan integrasi sensor IoT atau model inferensi AI;
  pembacaan dan hasil diagnosis saat ini dikirim sebagai payload API.
