# Struktur folder backend

```text
web/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/       # Endpoint JSON: auth dan domain Berry Care
│   │   ├── Middleware/            # Security headers, admin, dan cache web
│   │   ├── Requests/Api/           # Validasi dan otorisasi request API
│   │   └── Resources/              # Bentuk JSON resource API
│   ├── Models/                     # User dan entitas monitoring
│   ├── Policies/                   # Hak akses admin/petani per entitas
│   ├── Providers/AppServiceProvider.php # Rate limiter dan boot aplikasi
│   └── Services/                   # Evaluasi threshold dan layanan bersama
├── bootstrap/app.php               # Routing, middleware, dan respons exception API
├── config/
│   ├── auth.php                    # Guard, user provider, admin
│   ├── database.php                # Koneksi database Laravel
│   └── services.php                # Konfigurasi Google/API
├── database/
│   ├── factories/                  # Factory model untuk pengujian
│   ├── migrations/                 # Skema additif dan threshold bawaan
│   └── seeders/                    # Seeder admin dan BerryCareDemoSeeder
├── routes/
│   ├── api.php                     # API Sanctum dan autentikasi
│   └── web.php                     # Rute dashboard Blade
└── tests/Feature/                  # Tes API, autentikasi, dan dashboard
```

`resources/views/` dan `resources/js/` tetap menjadi bagian dashboard web;
perubahan pada fitur backend ini tidak mengubah tampilan Blade.

