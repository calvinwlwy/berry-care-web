# Rancangan Pengujian Postman — Login API Berry Care

Base URL: `http://127.0.0.1:8000/api`  
Endpoint utama: `POST /login`  
Identitas autentikasi adalah **email**, bukan username.

Semua request login memakai `Accept: application/json` dan
`Content-Type: application/json`, kecuali skenario F yang mengirim
`Accept: */*`. Respons token/password aktual bersifat dinamis; contoh
token disamarkan dan koleksi hanya membuat akun uji sintetis.

| ID | Skenario | Endpoint | Metode | Header | Body request | Status dan isi respons yang diharapkan | Hasil aktual audit |
|---|---|---|---|---|---|---|---|
| 0 | Membuat akun uji sintetis lalu memverifikasi login | `/register` (pre-request), `/login` | POST, POST | JSON | Register: `name`, email unik, password `Password123`, konfirmasi sama; login: email dan password sama | Register 201; login 200; respons user tanpa password dan token | Sesuai: register 201, akun ber-role `petani`; login 200 menghasilkan user/token tanpa password |
| A | Login sukses | `/login` | POST | JSON | Email dan password akun uji benar | 200; `message`, `user` berisi id/name/email/role, token; tanpa password | Sesuai: 200, message `Login berhasil.`, data user dan token; password tidak ada |
| B | Password salah, email terdaftar | `/login` | POST | JSON | Email terdaftar, password salah | 401 `{"message":"Email atau password salah"}`; tanpa token | Sesuai: 401 dan JSON tepat seperti yang diharapkan |
| C | Email tidak terdaftar | `/login` | POST | JSON | Email acak yang tidak ada dan password salah | 401 dengan body identik skenario B; tanpa token | Sesuai: 401, body identik persis dengan B |
| D1 | Email kosong | `/login` | POST | JSON | `{"email":"","password":"Password123"}` | 422; message validasi, errors hanya email wajib | Sesuai: 422, `errors.email[0]` = `Email wajib diisi.` |
| D2 | Password kosong | `/login` | POST | JSON | Email valid, password kosong | 422; errors password wajib | Sesuai: 422, `errors.password[0]` = `Password wajib diisi.` |
| D3 | Body tanpa field | `/login` | POST | JSON | `{}` | 422; errors email dan password wajib | Sesuai: 422, kedua error wajib dikembalikan |
| D4 | Format email salah | `/login` | POST | JSON | Email `bukan-email`, password terisi | 422; errors email format tidak valid | Sesuai: 422, `errors.email[0]` = `Format email tidak valid.` |
| E | Profil token valid/tidak ada/palsu | `/user` | GET | Header Authorization token pada request utama; subrequest tanpa token dan token palsu | Tidak ada body | Token valid 200 berisi profil tanpa password; dua lainnya 401 JSON | Sesuai: token valid 200; token tidak ada/palsu 401 `{"message":"Tidak terautentikasi."}` |
| F | Accept wildcard tanpa token | `/user` | GET | `Accept: */*` | Tidak ada body | 401 JSON, bukan redirect/HTML | Sesuai: 401 dengan `Content-Type: application/json` dan message `Tidak terautentikasi.` |
| G | Batas percobaan login | `/login` | POST | JSON | Lima percobaan dengan email sama, atau 20 email berbeda dari IP sama; password salah | Percobaan di atas batas 429, message bahasa Indonesia, `Retry-After` | Audit awal: batas email/IP 5 sudah ada tetapi 429 tanpa header `Retry-After`. Setelah perbaikan: batas 5 per email/IP dan 20 per IP, keduanya mengirim header `Retry-After` |
| H | Logout mencabut token | `/logout`, lalu `/user` | POST, lalu GET | Authorization token yang sama | `{}` untuk logout | Logout 200 `{"message":"Logout berhasil."}`; token lama kemudian 401 | Sesuai: token yang dipakai logout tidak dapat digunakan lagi |
| I | Input berbahaya | `/login` | POST | JSON | Email mirip SQL injection; subrequest memakai password 10.000 karakter dan email akun uji valid | 422 atau 401; tidak pernah 500 | Audit awal: email injection 422 dan password 10.000 karakter tidak menghasilkan 500. Setelah batas panjang: keduanya 422 |
| J | Hash dan kerahasiaan password | `/login`, `/user` | POST, GET | JSON; Authorization token untuk profil | Email dan password akun uji benar | Password tersimpan sebagai hash; respons tidak memuat password/hash/remember token | Sesuai: `Hash::check` benar; field sensitif dan nilai rahasia tidak ada di respons |
| K | Password login 300 karakter | `/login` | POST | JSON | Email uji dan password 300 karakter | 422; `errors.password` menyatakan maksimal 255 karakter; tanpa token | Sesuai setelah pembatasan panjang diterapkan: 422, tanpa token |
| L | Header keamanan dan no-store | `/login` | POST | JSON | Email dan password akun uji benar | 200; `X-Content-Type-Options: nosniff`, `Cache-Control` memuat `no-store`; password tidak ada | Sesuai setelah middleware keamanan diterapkan: 200; header keamanan dan no-store dikirim |

Format error validasi aktual:

```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "email": ["Email wajib diisi."]
  }
}
```

## Langkah menjalankan koleksi

1. Untuk lingkungan lokal, aktifkan API registrasi sementara
   (`API_REGISTRATION_ENABLED=true`) hanya jika request pertama
   membutuhkan pendaftaran akun, lalu jalankan Laravel dari direktori `web`:
   `php artisan serve`.
2. Impor `postman/Berry_Care_Login_API.postman_collection.json` ke Postman.
3. Jalankan request `0` sampai `F`, lalu `H` sampai `L` berurutan. Skenario
   E, H, dan I menjalankan subrequest tambahan dari tab Tests.
4. Jalankan request G paling akhir dan terpisah melalui Collection Runner sebanyak
   **7 iterasi**. Email limiter dibuat unik pada setiap run; jangan
   mengulang run untuk email yang sama sebelum jendela satu menit berakhir.

Request pertama membuat akun uji persisten dengan email unik. Hapus akun uji
setelah tugas selesai menggunakan sarana administrasi yang sesuai.

## Catatan hasil audit dan cakupan

- Audit awal membuktikan request unauthenticated ke `/api/user` tetap
  mendapatkan JSON 401 sekalipun `Accept: */*`.
- Login memakai validasi email wajib/format email serta password wajib.
  Kredensial gagal menghasilkan body generik yang sama, sehingga respons
  tidak membedakan email terdaftar.
- Login berhasil menggunakan status HTTP 200; respons token adalah token
  personal Sanctum, bukan field token yang disimpan di tabel `users`.
- Batas login adalah lima percobaan per menit per pasangan email dan IP,
  serta 20 percobaan per menit per IP. Implementasi awal sudah mengembalikan
  429 tetapi callback respons kustom menghilangkan header `Retry-After`;
  header tersebut kemudian diteruskan dalam perubahan minimal di limiter.
- Feature test mengecek token palsu setelah me-reset cache guard Laravel
  di antara simulasi request; ini meniru request HTTP terpisah dalam satu
  proses test.
