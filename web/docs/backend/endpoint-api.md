# Endpoint API

Base URL lokal: `http://127.0.0.1:8000/api`. Kecuali register/login/SSO,
endpoint API memerlukan `Authorization: Bearer <token>` Sanctum. Semua
endpoint domain dibatasi 60 request per menit per pengguna. Respons list
menggunakan `data`, `links`, dan `meta`; validasi memakai status 422 dengan
`message` serta `errors`.

| Metode | URL | Token | Akses | Fungsi dan contoh body | Respons sukses |
|---|---|---:|---|---|---|
| POST | `/register` | Tidak | Publik bila flag aktif | Buat akun petani; `{ "name":"A", "email":"a@example.test", "password":"...", "password_confirmation":"..." }` | 201, user dan token |
| POST | `/login` | Tidak | Semua akun API | Login email/password | 200, user dan token |
| POST | `/auth/google` | Tidak | Google SSO | Kirim `{ "id_token":"..." }` | 200, user dan token |
| GET | `/user` | Ya | Semua pengguna | Profil akun | 200, user |
| POST | `/logout` | Ya | Semua pengguna | Cabut token saat ini | 200, message |
| GET | `/getuser` | Ya | Admin | Daftar pengguna | 200, data |
| POST | `/adduser` | Ya | Admin | Buat petani; name, email, password | 201, data |
| PUT | `/updateuser/{id}` | Ya | Admin | Ubah data petani | 200, data |
| DELETE | `/deleteuser/{id}` | Ya | Admin | Hapus pengguna selain diri sendiri | 200, message |
| GET | `/polybags?q=&block=&is_active=&per_page=` | Ya | Semua | Cari/filter polybag dan status sensor terakhir | 200, paginasi |
| POST | `/polybags` | Ya | Admin | `{ "code":"POT-09", "block":"A", "location":"Bedeng 9" }` | 201, polybag |
| GET | `/polybags/{id}` | Ya | Semua | Detail, pembacaan terakhir, status evaluasi | 200, polybag |
| PUT/PATCH | `/polybags/{id}` | Ya | Admin | Ubah code/block/location/is_active | 200, polybag |
| DELETE | `/polybags/{id}` | Ya | Admin | Hapus polybag dan pembacaan terkait | 204 |
| GET | `/polybags/{id}/readings?from=&to=&per_page=` | Ya | Semua | Riwayat pembacaan sensor | 200, paginasi |
| POST | `/polybags/{id}/readings` | Ya | Semua | `{ "soil_moisture":60,"nitrogen":40,"phosphorus":25,"potassium":140,"recorded_at":"2026-11-12T08:00:00+07:00" }` | 201, pembacaan |
| GET | `/climate?from=&to=&per_page=` | Ya | Semua | Riwayat suhu/kelembapan | 200, paginasi |
| GET | `/climate/latest` | Ya | Semua | Pembacaan iklim terbaru | 200, `data` atau null |
| POST | `/climate` | Ya | Semua | `{ "temperature":22.5,"humidity":80,"recorded_at":"2026-11-12T08:00:00+07:00" }` | 201, pembacaan |
| GET | `/diagnoses?type=&polybag_id=&kategori_id=&per_page=` | Ya | Semua | Daftar diagnosis, termasuk filter kategori | 200, paginasi |
| POST | `/diagnoses` | Ya | Semua | type, label, confidence, severity, scanned_at, optional kategori_id dan image JPG/PNG | 201, diagnosis; user dari token |
| GET | `/diagnoses/{id}` | Ya | Semua | Detail diagnosis | 200, diagnosis |
| PUT/PATCH | `/diagnoses/{id}` | Ya | Admin | Ubah data diagnosis/foto | 200, diagnosis |
| DELETE | `/diagnoses/{id}` | Ya | Admin | Hapus diagnosis/foto | 204 |
| GET | `/kategori?q=&per_page=` | Ya | Admin, petani | Cari kategori, pagination dan diagnoses_count | 200, paginasi |
| POST | `/kategori` | Ya | Admin | `{ "nama_kategori":"Penyakit Daun","deskripsi":"..." }` | 201, kategori |
| GET | `/kategori/{id}` | Ya | Admin, petani | Detail kategori dan diagnoses_count | 200, kategori |
| PUT/PATCH | `/kategori/{id}` | Ya | Admin | Ubah nama/deskripsi kategori | 200, kategori |
| DELETE | `/kategori/{id}` | Ya | Admin | Hapus jika belum digunakan diagnosis | 204 atau 409 |
| GET | `/alerts?status=&severity=&per_page=` | Ya | Semua | Daftar peringatan | 200, paginasi |
| POST | `/alerts` | Ya | Admin | `{ "type":"manual","severity":"warning","message":"..." }` | 201, peringatan manual |
| GET | `/alerts/{id}` | Ya | Semua | Detail peringatan | 200, alert |
| PATCH | `/alerts/{id}/resolve` | Ya | Semua | Tandai selesai; resolver dari token | 200, alert resolved |
| DELETE | `/alerts/{id}` | Ya | Admin | Hapus peringatan | 204 |
| GET | `/thresholds` | Ya | Semua | Daftar ambang batas | 200, data |
| PUT | `/thresholds` | Ya | Admin | `{ "thresholds":{"humidity_max":85,"soil_moisture_min":45} }` | 200, data |
| GET | `/dashboard/summary` | Ya | Semua | Ringkasan status kebun, alert, iklim, diagnosis, rerata air 7 hari | 200, data |

Validasi sensor: kadar air dan kelembapan 0–100; N/P/K 0–1000; suhu
-10–60 °C; timestamp maksimal lima menit di masa depan. Endpoint yang
memerlukan entitas mengembalikan 404 JSON `{"message":"Data tidak ditemukan."}`.
Autentikasi gagal 401, akses peran ditolak 403 `{"message":"Akses ditolak."}`.
Kategori yang masih direferensikan oleh diagnosis mengembalikan 409. Detail
kontrak dan error API kategori tersedia di `docs/api/kategori.md`.
