# Berry Care Backend

Backend Laravel 12 untuk sistem monitoring dan peringatan dini kebun
stroberi Haifresh Lembang. API dipakai oleh aplikasi Flutter dan dashboard.

## Persiapan project lokal

1. Siapkan PHP sesuai `composer.json`, Composer, MySQL, Node.js dan npm.
   Buat database MySQL kosong bernama `berry_care_web`.
2. Dari folder `web/`, salin `.env.example` menjadi `.env`, lalu isi
   konfigurasi koneksi database dan `ADMIN_EMAIL`/`ADMIN_PASSWORD` lokal.
   Jangan membagikan atau commit file `.env`.
3. Jalankan perintah berikut:

   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan db:seed --class=AdminUserSeeder
   php artisan db:seed --class=BerryCareDemoSeeder
   php artisan storage:link
   php artisan serve
   ```

   Migrasi domain menambahkan tabel baru dan ambang batas bawaan; migrasi
   tidak menghapus atau membangun ulang tabel `users`. Seeder demo dapat
   dijalankan berulang dan tidak dipanggil otomatis oleh `DatabaseSeeder`.
   `storage:link` membuat foto diagnosis di disk public dapat diakses.
4. Jalankan `npm install` dan `npm run build` bila akan memakai dashboard
   Blade. Untuk tes backend gunakan:

   ```powershell
   php artisan route:list
   php artisan test
   vendor\bin\pint --test
   ```

5. Impor `docs/postman/Berry_Care_Backend_API.postman_collection.json`,
   isi variabel `baseUrl`, `adminEmail`, dan `adminPassword`, lalu kirim
   request berurutan dari folder collection.

Dokumentasi struktur, ERD, dan kontrak endpoint tersedia di `docs/backend/`.
API domain memerlukan Bearer token Sanctum. Aturan akses admin/petani dan
format request/response dirinci dalam dokumentasi endpoint.

## Master Kategori

API master kategori diagnosis berada di `/api/kategori`. Semua pengguna
terautentikasi dapat membaca dan mencari kategori; hanya admin dapat membuat,
mengubah, atau menghapusnya. Kategori digunakan secara opsional oleh data
diagnosis melalui `kategori_id`. Kategori yang masih dipakai diagnosis tidak
dapat dihapus.

Setelah menjalankan migrasi, isi kategori awal secara eksplisit:

```powershell
php artisan migrate
php artisan db:seed --class=KategoriSeeder
```

Seeder membuat kategori Penyakit Daun, Penyakit Buah, Hama, Defisiensi
Nutrisi, dan Tanaman Sehat. Seeder kategori tidak dipanggil otomatis oleh
`DatabaseSeeder`.

Dokumentasi dan pengujian:

- Kontrak API: `docs/api/kategori.md`
- Desain database: `docs/api/desain-database-kategori.md`
- OpenAPI 3.0: `docs/api/kategori-openapi.yaml`
- Postman: `docs/postman/Berry_Care_Kategori_API.postman_collection.json`
- Tes kategori: `php artisan test --filter=KategoriApiTest`

## Tentang data demo

Dashboard Blade lama masih memakai service data contoh. API domain yang
baru membaca dan menulis tabel polybag, sensor, iklim, diagnosis, peringatan,
dan ambang batas. `BerryCareDemoSeeder` menambahkan contoh untuk API.

## Keamanan dan produksi

Checklist sebelum deployment:

- Salin `.env.production.example` menjadi `.env` di server, lalu isi
  `APP_KEY`, koneksi database, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, dan rahasia
  lain langsung di server. Jangan commit `.env` atau kredensial.
- Pastikan `APP_ENV=production`, `APP_DEBUG=false`, dan `LOG_LEVEL=warning`.
  Aplikasi juga memaksa debug nonaktif saat boot di production.
- Aktifkan HTTPS di web server/load balancer dan gunakan URL HTTPS.
  Jika hosting berada di belakang reverse proxy, isi `TRUSTED_PROXIES`
  dengan alamat IP/CIDR proxy yang benar (dipisahkan koma); jangan percaya
  proxy publik yang tidak dikelola.
- Pertahankan `SESSION_SECURE_COOKIE=true` untuk cookie HTTPS.
- Biarkan `API_REGISTRATION_ENABLED=false` di production. Buat akun admin
  melalui proses administrasi yang aman.
- Jika API dipanggil dari aplikasi web lintas-origin, isi
  `CORS_ALLOWED_ORIGINS` dengan origin frontend production secara persis
  (contoh `https://app.contoh-domain`). Jangan memakai wildcard di production.
- Atur cron server agar Laravel Scheduler berjalan setiap menit:
  `* * * * * cd /path/ke/web && php artisan schedule:run >> /dev/null 2>&1`.
  Scheduler akan menjalankan pembersihan token Sanctum kedaluwarsa setiap
  hari.
- Kirim email/password hanya melalui body JSON pada HTTPS. Jangan menaruh
  kredensial di URL atau query string.
- Batasi akses server dan backup, rotasi kredensial yang pernah terpapar,
  pantau log login gagal, dan tetapkan kebijakan masa berlaku/rotasi token.

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Keamanan dan produksi

Checklist sebelum deployment:

- Salin `.env.production.example` menjadi `.env` di server, lalu isi
  `APP_KEY`, koneksi database, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, dan rahasia
  lain langsung di server. Jangan commit `.env` atau kredensial.
- Pastikan `APP_ENV=production`, `APP_DEBUG=false`, dan `LOG_LEVEL=warning`.
  Aplikasi juga memaksa debug nonaktif saat boot di production.
- Aktifkan HTTPS di web server/load balancer dan gunakan URL HTTPS.
  Jika hosting berada di belakang reverse proxy, isi `TRUSTED_PROXIES`
  dengan alamat IP/CIDR proxy yang benar (dipisahkan koma); jangan percaya
  proxy publik yang tidak dikelola.
- Pertahankan `SESSION_SECURE_COOKIE=true` untuk cookie HTTPS.
- Biarkan `API_REGISTRATION_ENABLED=false` di production. Buat akun admin
  melalui proses administrasi yang aman.
- Jika API dipanggil dari aplikasi web lintas-origin, isi
  `CORS_ALLOWED_ORIGINS` dengan origin frontend production secara persis
  (contoh `https://app.contoh-domain`). Jangan memakai wildcard di production.
- Atur cron server agar Laravel Scheduler berjalan setiap menit:
  `* * * * * cd /path/ke/web && php artisan schedule:run >> /dev/null 2>&1`.
  Scheduler akan menjalankan pembersihan token Sanctum kedaluwarsa setiap
  hari.
- Kirim email/password hanya melalui body JSON pada HTTPS. Jangan menaruh
  kredensial di URL atau query string.
- Batasi akses server dan backup, rotasi kredensial yang pernah terpapar,
  pantau log login gagal, dan tetapkan kebijakan masa berlaku/rotasi token.
