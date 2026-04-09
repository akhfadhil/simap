# SIMAP — Sistem Informasi Manajemen Arsip Pemilu
> KPU Kabupaten Banyuwangi · Aktualisasi Latsar CPNS

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 11 (PHP) |
| Frontend | Blade + Tailwind CSS + Vite |
| Database | MySQL 8 |
| Auth | Laravel Auth + Middleware Role |
| Export | Maatwebsite Excel (Laravel Excel) |
| Chart | Chart.js 4.4.1 |

---

## Role & Akses

| Role | Level | Kewenangan |
|------|-------|------------|
| `admin` | KPU Kabupaten | Kelola semua data, rekap kabupaten, verifikasi dokumen, backup arsip |
| `ppk` | Kecamatan | Rekap kecamatan, upload dokumen kecamatan, monitor desa |
| `pps` | Desa/Kelurahan | Rekap desa, verifikasi dokumen TPS |
| `kpps` | TPS | Input rekap suara, upload dokumen C-Hasil |

---

## Struktur Database (15 tabel)

```
users
kecamatans          → dapil_id (FK ke dapils)
desas               → kecamatan_id
tps                 → desa_id
dapils

dokumens            → tps_id, kecamatan_id, uploaded_by, verified_by
                      status: menunggu_verifikasi | terverifikasi | ditolak
                      is_archived, archived_at, komentar

rekap_headers       → tps_id, jenis, status (draft|final), difinalisasi_at
rekap_ppwp_calons
rekap_dpd_calons
rekap_partais       → jenis, dapil_id (untuk dprd_kab)
rekap_calegs        → partai_id
rekap_ppwp_suaras   → rekap_id, calon_id
rekap_dpd_suaras    → rekap_id, calon_id
rekap_partai_suaras → rekap_id, partai_id
rekap_caleg_suaras  → rekap_id, caleg_id
```

---

## Jenis Pemilu

| Key | Label |
|-----|-------|
| `ppwp` | Presiden & Wakil Presiden |
| `dpd` | DPD |
| `dpr_ri` | DPR RI |
| `dprd_prov` | DPRD Provinsi |
| `dprd_kab` | DPRD Kabupaten |

---

## Fitur & Status

### Autentikasi & Navigasi
- [x] Login multi-role dengan redirect ke dashboard sesuai role
- [x] Middleware guard per halaman
- [x] Dark / light mode toggle (persisted di localStorage)
- [x] Logo KPU di topbar
- [x] Favicon custom
- [x] Toast confirm global (override `window.confirm`) untuk semua delete
- [x] Custom error pages: 404, 403, 419, 500, 503 (dengan pesan error detail saat `APP_DEBUG=true`)

### Manajemen Wilayah (Admin)
- [x] CRUD Kecamatan
- [x] CRUD Desa
- [x] CRUD TPS — bulk add (input jumlah → otomatis buat TPS 001–NNN, skip yang sudah ada)
- [x] Edit nama TPS via modal
- [x] View TPS as KPPS (session-based)
- [x] Assign Dapil ke Kecamatan

### Setup Data Pemilu (Admin)
- [x] Input Paslon PPWP (nomor urut + nama)
- [x] Input Calon DPD
- [x] Input Partai & Caleg (DPR RI, DPRD Prov, DPRD Kab per dapil)
- [x] Kelola Dapil (nama dapil)
- [x] `PartaiSeeder` — auto-seed 18 partai untuk DPR RI, DPRD Prov, dan semua dapil DPRD Kab

### Manajemen Pengguna (Admin)
- [x] CRUD User dengan assign role & wilayah
- [x] Filter pencarian & urut nama

### Input Rekap KPPS
- [x] Form rekap per jenis pemilu (5 jenis)
- [x] Kalkulasi otomatis (total pengguna, suara sah, dll)
- [x] Simpan Draft & Finalisasi (kedua tombol selalu tampil)
- [x] Export Excel per TPS

### Rekap PPS
- [x] Tampilan rekap per desa (accordion TPS → kolom)
- [x] Export Excel

### Rekap PPK
- [x] Tampilan rekap per kecamatan (accordion desa → TPS kolom)
- [x] Export Excel

### Rekap Admin
- [x] Summary cards (total DPT, hadir, suara tidak sah, TPS final)
- [x] Tabel rekap total kabupaten (kolom = kecamatan)
- [x] Accordion per kecamatan → desa → TPS sebagai kolom (Section I–V)
- [x] Status badge per TPS (Kosong / Draft / Final)
- [x] **Tombol Unlock** rekap yang sudah final (admin bisa buka kembali untuk diedit)
- [x] Export Excel (modal pilih level: TPS / Desa / Kecamatan / Kabupaten)

### Grafik & Statistik (Admin)
- [x] Halaman khusus grafik dengan Chart.js
- [x] Filter: jenis pemilu → level → wilayah bertingkat
- [x] **DPRD Kab**: level "Kabupaten" diganti "Dapil" → grafik per kecamatan dalam dapil
- [x] Chart perolehan suara (doughnut untuk 1 wilayah, bar untuk multi-wilayah)
- [x] Chart tingkat partisipasi (DPT vs Hadir)
- [x] Data fetch via AJAX

### Manajemen Dokumen
- [x] Upload PDF per jenis pemilu (KPPS: level TPS, PPK: level kecamatan)
- [x] Verifikasi dokumen berjenjang: KPPS → PPS → Admin
- [x] **Tolak dokumen** dengan komentar/alasan penolakan
- [x] Preview PDF inline (dalam iframe modal)
- [x] Download PDF
- [x] Tampil komentar penolakan di bawah dokumen

### Backup & Arsip Dokumen
- [x] Artisan command `backup:dokumen --days=N` — move PDF ke folder backup, mirror struktur folder
- [x] Artisan command `restore:dokumen {id}` — restore via CLI
- [x] Scheduler otomatis harian jam 01:00 (`Kernel.php`)
- [x] Restore via UI admin (tombol di halaman archived)
- [x] Halaman `dokumen/archived.blade.php` — tampil saat file sudah diarsipkan, dengan info dokumen & tombol restore (toast confirm warna amber)
- [x] Konfigurasi path backup via `.env`: `BACKUP_DOKUMEN_PATH=E:\Backup\SIMAP`
- [x] Download/preview file diarsipkan → tidak error, tampil halaman archived

---

## Seeder

| Seeder | Isi |
|--------|-----|
| `WilayahSeeder` | 25 kecamatan + 217 desa Kabupaten Banyuwangi |
| `PartaiSeeder` | 18 partai × (DPR RI + DPRD Prov + semua dapil DPRD Kab). Aman dijalankan berulang (skip yang sudah ada) |

```bash
# Jalankan semua seeder
php artisan db:seed

# Jalankan setelah dapil ditambah
php artisan db:seed --class=PartaiSeeder
```

---

## Artisan Commands

```bash
# Backup dokumen (move PDF ke storage backup)
php artisan backup:dokumen               # default --days=0
php artisan backup:dokumen --days=30     # hanya dokumen >30 hari
php artisan backup:dokumen --dry-run     # simulasi tanpa memindah file

# Restore dokumen via CLI
php artisan restore:dokumen {id}
```

---

## Key Files

```
app/
├── Console/
│   ├── Kernel.php                          # Scheduler backup harian 01:00
│   └── Commands/
│       ├── BackupDokumen.php
│       └── RestoreDokumen.php
├── Http/Controllers/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── DokumenController.php               # preview, download, restore, verifikasi
│   ├── Admin/
│   │   ├── SetupController.php             # master data pemilu & dapil
│   │   ├── TpsController.php               # bulk add TPS
│   │   ├── DesaController.php
│   │   ├── KecamatanController.php
│   │   └── UserManagementController.php
│   └── Rekap/
│       ├── KppsController.php
│       ├── PpsController.php
│       ├── PpkController.php
│       └── AdminController.php             # show, unlock, chart, export
├── Exports/
│   ├── RekapExport.php
│   ├── RekapSheetExport.php
│   └── RekapTotalSheetExport.php
└── Models/
    ├── User.php
    ├── Kecamatan.php                       # fillable: nama, dapil_id
    ├── Desa.php
    ├── Tps.php
    ├── Dapil.php
    ├── Dokumen.php                         # fillable: is_archived, archived_at, komentar
    ├── RekapHeader.php
    ├── RekapPartai.php
    └── ... (calon, suara models)

resources/views/
├── layouts/app.blade.php                   # topbar, dark mode, toast confirm global, PDF modal
├── errors/
│   ├── 404.blade.php
│   ├── 403.blade.php
│   ├── 419.blade.php
│   ├── 500.blade.php
│   └── 503.blade.php
├── dashboard/
│   ├── admin.blade.php
│   ├── ppk.blade.php
│   ├── pps.blade.php
│   └── kpps.blade.php
├── admin/
│   ├── tps/index.blade.php                 # bulk add + edit modal
│   ├── desa/index.blade.php
│   ├── kecamatan/index.blade.php
│   └── users/index.blade.php
├── dokumen/
│   ├── upload.blade.php                    # KPPS
│   ├── pps.blade.php                       # verifikasi + tolak
│   ├── ppk.blade.php
│   ├── admin.blade.php                     # verifikasi + tolak + restore
│   └── archived.blade.php                  # file diarsipkan + toast restore
└── rekap/
    ├── kpps/form.blade.php
    ├── pps/show.blade.php
    ├── ppk/show.blade.php
    └── admin/
        ├── index.blade.php
        ├── show.blade.php                  # unlock rekap final
        └── chart.blade.php                 # grafik + filter dapil (dprd_kab)

database/
├── migrations/                             # 15 file konsolidasi
└── seeders/
    ├── DatabaseSeeder.php
    ├── WilayahSeeder.php
    └── PartaiSeeder.php
```

---

## Routes Penting

```php
// Dokumen
POST /dokumen/{dokumen}/verifikasi          dokumen.verifikasi      (pps, ppk, admin)
POST /dokumen/{dokumen}/verifikasi-admin    dokumen.verifikasi.admin (admin)
POST /dokumen/{dokumen}/restore             dokumen.restore          (admin)
GET  /dokumen/{dokumen}/preview             dokumen.preview
GET  /dokumen/{dokumen}/download            dokumen.download

// Rekap Admin
GET  admin/rekap/chart                      admin.rekap.chart
GET  admin/rekap/chart/data                 admin.rekap.chart.data
GET  admin/rekap/export/download            admin.rekap.export.download
POST admin/rekap/{jenis}/unlock             admin.rekap.unlock
GET  admin/rekap/{jenis}/export             admin.rekap.export
GET  admin/rekap/{jenis}                    admin.rekap.show

// TPS
POST admin/tps                              admin.tps.store   (bulk)
GET  admin/tps/{tps}/view                   admin.tps.view
```

---

## Environment Variables

```env
APP_DEBUG=true|false

# Path folder backup dokumen PDF
BACKUP_DOKUMEN_PATH=E:\Backup\SIMAP
```

```php
// config/filesystems.php
'backup_path' => env('BACKUP_DOKUMEN_PATH', storage_path('app/backup')),
```

---

## Aktivasi Scheduler (Windows)

Task Scheduler Windows → Create Basic Task:
- Trigger: Daily, repeat every 1 minute
- Action: Program `php`, Arguments `artisan schedule:run`, Start in: `C:\path\project`

---

## Progress Aktualisasi

| Kegiatan | Judul | Status |
|----------|-------|--------|
| Kegiatan 1 | Konsep & Perencanaan Sistem | ✅ Selesai |
| Kegiatan 2 | Desain Sistem & Database | ✅ Selesai |
| Kegiatan 3 — Minggu 2 | Pengembangan Prototype | ✅ Selesai (laporan dibuat) |
| Kegiatan 3 — Minggu 3 | Uji Coba Sistem | ⏳ Belum |
| Kegiatan 3 — Minggu 4 | Evaluasi & Perbaikan | ⏳ Belum |
| Kegiatan 4 | — | ⏳ Belum |
| Kegiatan 5 | — | ⏳ Belum |
