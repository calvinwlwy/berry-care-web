# Berry Care Field App

## Menjalankan aplikasi Android

Atur `API_URL` ke alamat dasar API Laravel yang dapat dijangkau perangkat.
Untuk emulator Android lokal, gunakan `http://10.0.2.2:8000/api`. Nilai
`GOOGLE_SERVER_CLIENT_ID` harus sama dengan OAuth Web client ID yang disetel
sebagai `GOOGLE_CLIENT_ID` di konfigurasi Laravel.

```powershell
cd mobile
flutter pub get
flutter run -d <android-device-id> `
  --dart-define=API_URL=http://10.0.2.2:8000/api `
  --dart-define=GOOGLE_SERVER_CLIENT_ID=<web-client-id>
```

Tombol SSO tetap ditampilkan di Android, tetapi akan memberi pesan
“SSO belum dikonfigurasi.” jika `GOOGLE_SERVER_CLIENT_ID` kosong.

## Menjalankan di Chrome

Gunakan OAuth Web client ID yang sama untuk backend dan Flutter. Tambahkan
`http://localhost` dan origin port tetap `http://localhost:7357` sebagai
Authorized JavaScript origins di Google Cloud Console. API lokal mengizinkan
origin localhost; untuk deployment, atur `CORS_ALLOWED_ORIGINS` di Laravel.

```powershell
cd mobile
flutter run -d chrome --web-hostname localhost --web-port 7357 `
  --dart-define=API_URL=http://localhost:8000/api `
  --dart-define=GOOGLE_SERVER_CLIENT_ID=<web-client-id>
```

Tombol di Chrome dirender oleh Google Identity Services SDK dan akan menerima
ID token melalui event autentikasi sebelum dikirim ke Laravel. Jangan gunakan
`serverClientId` pada web; aplikasi memakai nilai define yang sama sebagai
OAuth `clientId` web. Lihat [panduan SSO](../docs/sso-google.md) untuk
konfigurasi Google Cloud.
