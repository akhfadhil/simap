# SIMPEMILU — Sistem Informasi Manajemen Pemilu

## Stack
- Laravel 11, Tailwind CSS v4, Flowbite, MySQL (Laragon)

## Struktur Role
- Admin → akses penuh, CRUD wilayah & user
- PPK → 1 kecamatan, lihat dokumen read only
- PPS → 1 desa, lihat & verifikasi dokumen dari KPPS
- KPPS → 1 TPS, upload 5 dokumen PDF

## Database Tables
- users (id, name, username, role, email, password, kecamatan_id, desa_id, tps_id)
- kecamatans (id, nama)
- desas (id, nama, kecamatan_id)
- tps (id, nama, desa_id)
- dokumens (id, tps_id, uploaded_by, jenis, status, verified_by, verified_at, file_path, file_name, file_size)

## Jenis Dokumen (enum)
PPWP, DPR_RI, DPD, DPRD_PROV, DPRD_KAB

## Status Dokumen
menunggu_verifikasi → terverifikasi (PPS yang verifikasi)

## Fitur Selesai
- Login/logout dengan redirect otomatis berdasarkan role
- Middleware RoleMiddleware untuk guard akses
- Admin: CRUD Kecamatan, Desa, TPS, User Management + assign wilayah
- KPPS: upload/replace PDF per jenis dokumen
- PPS: preview, download, verifikasi dokumen per TPS
- PPK: preview & download dokumen per kecamatan (read only)
- Admin: rekap semua dokumen dengan filter kecamatan & desa

## Fitur Belum Dibuat
- Daftar Hadir
- Lapor Kendala
- Berita Acara
- Export CSV
- DPT

## Struktur Views
resources/views/
├── layouts/ (guest.blade.php, app.blade.php)
├── auth/ (login.blade.php)
├── dashboard/ (admin, ppk, pps, kpps .blade.php)
├── dokumen/ (upload, pps, ppk, admin .blade.php)
└── admin/
    ├── wilayah/ (kecamatan, desa, tps .blade.php)
    └── users/ (index.blade.php)