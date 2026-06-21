# Panduan Ekspor Snapshot Partai - SIMAP Utama

Dokumen ini menjelaskan cara menggunakan command Artisan `export:party-snapshot` untuk mengekspor data wilayah, master partai, data caleg, dan hasil perolehan suara legislatif partai politik tertentu dari **SIMAP Utama** ke dalam file JSON snapshot. File ekspor ini siap digunakan untuk inisialisasi data pada proyek khusus/mandiri partai (seperti `simap-garuda` atau template proyek partai).

---

## Cara Menjalankan Command

Jalankan perintah berikut di terminal/command prompt pada direktori root proyek `simap` (Utama):

```bash
php artisan export:party-snapshot {slug}
```

### Parameter:
*   `{slug}`: Slug dari profil partai politik yang terdaftar di sistem (misalnya: `pkb`, `pdi-p`, `gerindra`, `golkar`, dll.).
    *   *Catatan*: Slug ini bersifat case-insensitive (huruf besar/kecil tidak berpengaruh).

### Contoh Penggunaan:

```bash
php artisan export:party-snapshot pkb
```

Output sukses yang akan muncul di terminal:
```text
Memulai ekspor snapshot untuk partai: Partai Kebangkitan Bangsa (PKB)...
✓ Ekspor selesai! Snapshot disimpan ke: C:\laragon\www\simap\storage\app/private\exports/party-snapshot-pkb-20260621-164910.json
```

---

## Lokasi File Output

File ekspor akan disimpan di folder storage lokal proyek:
*   **Path Relatif**: `storage/app/private/exports/`
*   **Format Nama File**: `party-snapshot-{slug}-{tahun}{bulan}{tanggal}-{jam}{menit}{detik}.json`
    *   Contoh: `party-snapshot-pkb-20260621-164910.json`

---

## Isi Data Snapshot JSON

File JSON yang dihasilkan berisi struktur data terisolasi yang aman sebagai berikut:

*   `exported_at`: Waktu ekspor data.
*   `source_app`: Nama aplikasi asal (`SIMAP Utama`).
*   `party_profile`: Profil lengkap partai yang diekspor (nama lengkap, nama singkat/akronim, logo, warna visual, dan riwayat nomor urut).
*   `dapils`, `kecamatans`, `desas`, `tps`: Master data wilayah lengkap Banyuwangi untuk dasar navigasi di proyek partai.
*   `rekap_partais`: Rekap data partai legislatif untuk tingkat DPR RI, DPRD Provinsi, dan DPRD Kabupaten.
*   `rekap_calegs`: Daftar calon legislatif dari partai yang diekspor.
*   `rekap_headers`: Data header rekapitulasi (angka partisipasi pemilih, surat suara sah/tidak sah) dari semua TPS.
*   `rekap_partai_suaras`: Perolehan suara khusus partai target per TPS (suara partai kompetitor disaring keluar secara otomatis).
*   `rekap_caleg_suaras`: Perolehan suara khusus caleg dari partai target per TPS (suara caleg partai kompetitor disaring keluar secara otomatis).
