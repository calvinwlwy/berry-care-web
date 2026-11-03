<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

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
