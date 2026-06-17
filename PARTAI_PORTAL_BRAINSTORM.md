# Brainstorm Portal Partai SIMAPy
# https://satuin.tech/home, https://t.me/Ns_Autoorder_bot, https://kprem.web.id/#products, https://zuxop.com/id

Catatan ini merangkum ide pengembangan portal partai di SIMAP berdasarkan diskusi perencanaan fitur.

## Catatan Keputusan Terbaru: Project Baru Khusus Partai

Setelah diskusi lanjutan, arah yang diminta adalah bukan lagi menambah fitur partai di project SIMAP utama, tetapi membuat project baru khusus partai.

Project baru ini sebaiknya diposisikan sebagai aplikasi mandiri berbasis fork dari SIMAP, bukan sekadar fitur tambahan di sistem utama. Artinya, kode awal boleh mengambil banyak pola dari SIMAP agar pengembangan cepat, tetapi database, role, istilah, akses, dan alur kerja disesuaikan untuk kebutuhan partai.

Prinsip utama keputusan ini:

- Project partai berdiri sendiri.
- Database partai terpisah dari database SIMAP utama.
- Partai tidak membaca langsung database SIMAP utama.
- Data yang masuk ke project partai berasal dari import, export, atau input mandiri.
- Struktur wilayah dan hierarki pengguna tetap bisa mirip PPK, PPS, dan KPPS, tetapi nama role dan konteksnya diganti sesuai organisasi partai.
- Fitur internal KPU/SIMAP yang tidak relevan sebaiknya dihapus, disembunyikan, atau dinonaktifkan.

Pendekatan yang paling sehat adalah membuat fork resmi, misalnya:

```text
simap-partai
```

atau nama lain sesuai identitas produk yang diinginkan.

### Phase 1 Locked: SIMAP Garuda

Project pertama yang akan dibuat adalah project khusus Partai Garuda.

Keputusan Phase 1:

```text
Project: SIMAP Garuda
Folder: simap-garuda
Database: simap_garuda
Partai: Partai Garuda
Slug permanen: garuda
Model aplikasi: satu project untuk satu partai
Role: admin_partai, korcam, kordes, saksi_tps
Data MVP: input manual TPS terlebih dahulu
Import: fase berikutnya setelah struktur role dan rekap stabil
```

Nomor urut partai tidak boleh dijadikan identitas utama Partai Garuda karena nomor urut dapat berubah pada pemilu berikutnya.

Untuk data historis Pemilu 2024, nomor urut Garuda di master SIMAP saat ini adalah:

```text
GARUDA = nomor_urut 11
```

Fungsi `nomor_urut` dalam konteks project Garuda:

- Kunci bantu untuk ekstrak atau matching data Pemilu 2024 dari database SIMAP utama.
- Referensi historis pada laporan dan arsip.
- Alat validasi saat import data suara, partai, atau caleg dari sumber eksternal.
- Bahan rekonsiliasi jika suatu hari perlu mencocokkan data Garuda dengan data SIMAP/KPU.

Yang tidak boleh dilakukan:

- Jangan memakai `nomor_urut` sebagai identitas permanen Partai Garuda.
- Jangan membuat project Garuda bergantung runtime ke database SIMAP utama.
- Jangan menganggap nomor urut 2024 akan tetap sama pada pemilu berikutnya.

Alur yang benar:

```text
SIMAP utama -> export/sanitasi data Garuda berdasarkan nomor_urut 11 -> import ke simap_garuda
```

Setelah data masuk ke database `simap_garuda`, hubungan dengan database SIMAP utama putus. Project Garuda berjalan sebagai aplikasi mandiri yang dipegang Partai Garuda. Jika ada sengketa atau selisih angka, data Garuda dapat dibandingkan dengan data SIMAP/KPU berdasarkan pemilu, jenis pemilihan, wilayah, TPS, caleg, partai, dan nomor urut pada pemilu tersebut.

### Kenapa Project Baru Masuk Akal

Arah project baru masuk akal jika kebutuhan atasan adalah membuat produk khusus partai, bukan sekadar memberi akun partai untuk melihat data di SIMAP.

Keuntungannya:

- Data internal SIMAP/KPU tetap aman karena tidak dibuka ke sistem partai.
- Partai punya aplikasi sendiri dengan database sendiri.
- Risiko akses silang ke data admin, operator, dokumen internal, catatan verifikasi, atau fitur koreksi SIMAP utama menjadi jauh lebih kecil.
- Istilah, role, menu, dashboard, dan laporan bisa disesuaikan penuh untuk kebutuhan partai.
- Tiap partai bisa memiliki data, branding, akun, dan kebijakan akses sendiri.
- Cocok jika nantinya partai membutuhkan sistem jangka panjang, bukan hanya akses sementara.

Namun project baru tidak boleh hanya berupa copy mentah yang dibiarkan berkembang tanpa kendali. Lebih aman jika dianggap sebagai fork resmi dari SIMAP dengan scope yang jelas.

### Risiko Yang Harus Disadari

Risiko utama dari project baru adalah maintenance menjadi dua jalur.

Hal yang perlu dijaga:

- Bug fix penting di SIMAP utama mungkin perlu diterapkan juga ke project partai.
- Perubahan format export, chart, rekap, atau struktur wilayah bisa perlu disinkronkan.
- Jika terlalu banyak modifikasi liar, project partai bisa sulit di-maintain.
- Jika database dan importer tidak dirancang jelas, angka bisa berbeda dari sumber data yang diharapkan.
- Jika role baru tidak dipetakan dengan rapi, akses wilayah bisa bocor atau membingungkan.

Karena itu, project partai sebaiknya dibuat dengan scope tegas:

- Fokus pada kebutuhan partai.
- Database sendiri.
- Read-only atau input terbatas sesuai kebutuhan partai.
- Tidak membawa fitur internal KPU yang tidak diperlukan.
- Import data harus punya format dan validasi jelas.

### Struktur Role Yang Disarankan

Role SIMAP utama seperti `admin`, `komisioner`, `partai`, `ppk`, `pps`, dan `kpps` sebaiknya tidak dipakai mentah. Untuk project partai, istilah role bisa diganti agar sesuai struktur partai.

Contoh struktur role:

```text
admin_partai
korcam
kordes
saksi_tps
```

Alternatif nama lain:

```text
operator_partai
koordinator_kecamatan
koordinator_desa
koordinator_tps
```

Pola akses yang disarankan:

- `admin_partai` melihat semua data partai.
- `korcam` melihat data di satu kecamatan.
- `kordes` melihat data di satu desa atau kelurahan.
- `saksi_tps` atau `kortps` melihat dan/atau menginput data TPS miliknya.

Hierarki ini mirip PPK, PPS, dan KPPS di SIMAP, tetapi konteksnya bukan struktur penyelenggara pemilu. Konteksnya adalah struktur internal partai atau tim saksi.

### Fitur Yang Bisa Dipertahankan Dari SIMAP

Beberapa modul SIMAP masih berguna untuk project partai:

- Login dan session auth.
- Manajemen user.
- Master kecamatan, desa, dan TPS.
- Relasi user ke wilayah.
- Rekap suara per TPS.
- Agregasi desa, kecamatan, dan kabupaten.
- Dashboard ringkasan.
- Grafik dan peta.
- Export Excel/PDF.
- Import data dari Excel/CSV jika diperlukan.

Modul ini bisa dipakai sebagai fondasi, tetapi perlu disesuaikan agar tidak membawa istilah dan akses KPU yang tidak relevan.

### Fitur Yang Sebaiknya Dihapus atau Dinonaktifkan

Fitur internal SIMAP utama yang sebaiknya tidak ikut ke project partai:

- Role `komisioner` jika tidak dibutuhkan.
- Login partai sebagai fitur tambahan, karena project ini sendiri sudah khusus partai.
- Fitur verifikasi dokumen internal KPU jika tidak relevan.
- Catatan penolakan/verifikasi dokumen petugas.
- Unlock rekap final oleh admin KPU.
- Koreksi inline internal admin jika tidak dibutuhkan.
- Penanda cell koreksi manual internal.
- Backup/restore dokumen internal SIMAP.
- Tool setup pemilu yang terlalu luas jika data partai bersifat snapshot atau import.
- Akses ke data partai lain jika project dibuat untuk satu partai.

Jika ada fitur yang tetap diperlukan, fitur tersebut harus diberi konteks baru untuk partai, bukan dibawa apa adanya.

### Model Data Project Partai

Karena database berdiri sendiri, project partai bisa memiliki struktur data yang lebih sederhana.

Data minimal:

- Wilayah: kecamatan, desa/kelurahan, TPS.
- User dan role hierarchy.
- Master partai jika project mendukung lebih dari satu partai, atau konfigurasi identitas partai jika hanya satu partai.
- Caleg partai.
- Rekap suara partai.
- Rekap suara caleg.
- Status data masuk per TPS.
- Riwayat import atau sinkronisasi data jika diperlukan.

Jika project hanya untuk satu partai, tabel master partai bisa dibuat sebagai konfigurasi aplikasi, bukan data utama yang kompleks.

Jika project akan dipakai banyak partai dengan database masing-masing, struktur tetap bisa sama, tetapi setiap deployment hanya berisi data partai tersebut.

### Alur Data Yang Disarankan

Karena project partai tidak mengambil langsung database SIMAP utama, alur data harus dibuat eksplisit.

Opsi alur data:

1. Import dari Excel atau CSV

   Admin partai mengupload file hasil rekap, lalu sistem membaca data TPS, desa, kecamatan, partai, dan caleg.

2. Import dari JSON hasil export SIMAP

   SIMAP utama menghasilkan file export yang sudah disanitasi, lalu project partai mengimport file tersebut.

3. Input manual oleh saksi atau koordinator

   `saksi_tps` menginput data TPS, lalu `kordes`, `korcam`, dan `admin_partai` memantau progres.

4. Snapshot final

   Setelah data final, project partai hanya berisi data akhir untuk dashboard, grafik, dan laporan.

Untuk tahap awal, opsi paling aman adalah import file yang sudah jelas formatnya. Jangan langsung koneksi antar database agar batas keamanan tetap bersih.

### Dashboard Project Partai

Dashboard project partai sebaiknya fokus pada kebutuhan partai, bukan kebutuhan operator KPU.

Isi dashboard yang relevan:

- Total suara partai.
- Total suara caleg partai.
- Ranking caleg internal.
- Wilayah kuat dan lemah.
- Perbandingan suara per kecamatan.
- Perbandingan suara per desa.
- Progress data TPS masuk.
- TPS belum masuk.
- TPS bermasalah atau perlu verifikasi internal.
- Peta kekuatan suara.
- Export laporan untuk pengurus partai.

Jika project hanya untuk satu partai, dashboard tidak perlu terlalu banyak menampilkan data partai lain. Data kompetitor hanya ditampilkan jika memang dibutuhkan dan diizinkan oleh kebijakan data.

### Rekomendasi Eksekusi Besok

Tahapan eksekusi yang disarankan:

1. Duplikasi/fork project SIMAP ke folder project baru.
2. Tentukan nama project, nama database, dan identitas aplikasi partai.
3. Tentukan istilah role final: misalnya `admin_partai`, `korcam`, `kordes`, dan `saksi_tps`.
4. Bersihkan role lama yang tidak diperlukan.
5. Sesuaikan middleware role dan redirect dashboard.
6. Sesuaikan menu sidebar/topbar agar hanya menampilkan fitur partai.
7. Pertahankan struktur wilayah kecamatan, desa, dan TPS.
8. Sesuaikan manajemen user agar mengikuti hierarchy partai.
9. Tentukan apakah data akan diinput manual, diimport dari Excel/CSV, atau diimport dari export JSON.
10. Buat dashboard awal khusus partai.
11. Sesuaikan grafik dan export agar fokus pada suara partai/caleg.
12. Jalankan test dasar login, akses role, scope wilayah, input/import data, agregasi, dan export.

Keputusan penting: setuju dengan arah project baru, tetapi implementasinya sebaiknya berupa fork SIMAP yang dirapikan, bukan copy-paste total tanpa batas. Project baru harus punya database sendiri, role hierarchy baru, dan hanya membawa fitur yang memang berguna untuk kebutuhan partai.

## Kesimpulan Utama

SIMAP bisa mendukung login khusus untuk setiap partai. Pendekatan yang paling efektif untuk masa pemilu berjalan adalah tetap memakai satu sistem SIMAP multi-partai, lalu membatasi tampilan berdasarkan akun partai yang login.

Partai cukup menerima:

```text
URL login partai
username
password
```

Contoh:

```text
https://simap.example.com/partai/golkar/login
username: golkar
password: ********
```

Setelah login, dashboard otomatis mengikuti partai tersebut. Golkar melihat dashboard Golkar, PKB melihat dashboard PKB, dan seterusnya.

## Login Page Per Partai

Secara teknis bisa dibuat satu template login yang dinamis, bukan banyak file login terpisah.

Contoh pola URL:

```text
/partai/{slug}/login
```

Contoh:

```text
/partai/golkar/login
/partai/pkb/login
/partai/gerindra/login
/partai/pdip/login
```

Halaman login mengambil profil partai dari `slug`, lalu menampilkan logo, nama, dan warna visual partai.

## Perubahan Yang Disarankan

Supaya lebih rapi dan mudah dikelola, perlu identitas partai yang tidak bergantung langsung pada baris `rekap_partais`, karena data partai bisa berulang per jenis pemilu atau dapil.

Opsi yang disarankan adalah membuat profil partai tersendiri:

```text
partai_profiles
- id
- nomor_urut
- nama
- slug
- logo_path
- warna_utama
```

Akun `users` role `partai` dapat dihubungkan ke profil partai tersebut. Filter data rekap tetap bisa memakai `nomor_urut` atau mapping yang sesuai.

## Dashboard Partai Yang Disarankan

Dashboard partai sebaiknya read-only dan scoped ketat ke partai yang login.

Isi yang aman dan menarik untuk partai:

- Identitas partai: logo, nama, nomor urut, dan sapaan khusus.
- Ringkasan suara partai untuk DPR RI, DPRD Provinsi, dan DPRD Kabupaten.
- Total suara partai per kabupaten, kecamatan, desa, dan TPS jika data TPS memang boleh dibuka.
- Ranking partai di tiap jenis legislatif.
- Daftar caleg partai sendiri beserta suara.
- Top caleg partai sendiri.
- Peta atau grafik kekuatan suara partai per kecamatan/desa.
- Progress data masuk: TPS final, TPS belum final, dan persentase data masuk.
- Export Excel/PDF untuk data partai sendiri.
- Dokumen atau laporan final yang sudah diverifikasi dan memang boleh diakses partai.

Struktur dashboard ideal:

1. Ringkasan suara, ranking, dan progress data.
2. Tab legislatif: DPR RI, DPRD Provinsi, DPRD Kabupaten.
3. Tabel wilayah: kecamatan/desa/TPS, suara, persentase, TPS masuk, dan status finalisasi.
4. Grafik dan peta kekuatan suara.
5. Unduhan laporan.

## Data Yang Sebaiknya Tidak Dibagikan Ke Partai

Untuk menjaga keamanan dan batas akses, data berikut sebaiknya tidak dipublish ke dashboard partai:

- Data akun admin, PPK, PPS, KPPS, atau user partai lain.
- Nomor HP, email internal, atau identitas operator.
- Dokumen mentah yang belum diverifikasi.
- Catatan verifikasi internal atau komentar operasional petugas.
- Fitur koreksi, unlock, inline update, atau penanda cell internal.
- Log import, backup, error, cache, atau audit internal.
- Data draft yang belum final, kecuali ada kebijakan eksplisit bahwa partai boleh melihat data sementara.
- Data partai lain sampai level terlalu granular jika belum final atau belum disepakati.

## Satu Sistem Multi-Partai Saat Pemilu Berjalan

Untuk hari pemilu dan masa rekap berjalan, satu sistem multi-partai lebih efektif daripada membuat project terpisah per partai.

Alasannya:

- Data tetap satu sumber.
- Perbaikan bug cukup dilakukan sekali.
- Perubahan aturan, tampilan, grafik, atau export cepat diterapkan ke semua partai.
- Kontrol akses lebih mudah diaudit.
- Risiko angka berbeda antar versi lebih kecil.
- Maintenance server, cache, backup, dan deployment lebih sederhana.

Model ini tetap bisa terasa personal untuk partai lewat URL, logo, warna, dan dashboard khusus.

Contoh:

```text
simap.example.com/partai/golkar/login
golkar.simap.example.com
```

Keduanya tetap bisa memakai project dan database SIMAP yang sama.

## Ekstrak Project Khusus Partai Setelah Data Final

Setelah pemilu selesai dan data sudah final, ekstrak khusus satu partai bisa dilakukan dengan lebih aman.

Namun ekstrak sebaiknya berbentuk snapshot read-only, bukan clone penuh sistem operasional.

Yang aman diekstrak:

- Data suara partai tersebut.
- Data caleg partai tersebut.
- Ranking dan agregasi wilayah yang memang boleh dibuka.
- Grafik dan peta.
- Export laporan.
- Status final atau data masuk sebagai konteks historis.

Yang tidak sebaiknya ikut diekstrak:

- User admin, PPK, PPS, KPPS.
- Password atau hash user dari sistem utama.
- Dokumen mentah/internal.
- Log import, backup, cache, error, atau audit.
- Fitur setup, import, verifikasi, koreksi, unlock, dan inline update.
- Data draft atau data yang belum final.

## Bentuk Ekstrak Yang Disarankan

Ada dua opsi aman:

1. Mini dashboard statis

   Data final diexport ke JSON, SQLite, atau CSV, lalu dibuat dashboard read-only. Ini paling aman untuk arsip, presentasi, atau portal privat sederhana.

2. Project Laravel ringan khusus partai

   Tetap ada login dan dashboard, tetapi database hanya berisi subset data final untuk satu partai. Cocok jika partai membutuhkan akses privat jangka panjang.

## Rekomendasi Arah Implementasi

Tahap awal yang paling masuk akal:

1. Buat profil partai berisi slug, logo, warna, dan nomor urut.
2. Tambah login dinamis `/partai/{slug}/login`.
3. Pastikan login role `partai` hanya bisa masuk ke partai yang cocok.
4. Bangun dashboard partai read-only dengan scope ketat.
5. Tambah export laporan khusus partai.
6. Setelah data final, siapkan tool export snapshot per partai jika diperlukan.

Prinsip utamanya: saat pemilu berjalan, gunakan satu SIMAP multi-partai. Setelah data final, ekstrak per partai boleh dilakukan sebagai snapshot read-only yang sudah disanitasi.

## Update Arah Setelah SIMAP Garuda

Setelah project `simap-garuda` berjalan, arah pengembangan berubah dari sekadar portal partai di SIMAP utama menjadi pola pemecahan project per partai.

Artinya:

- SIMAP utama tetap menjadi sistem induk dan sumber pola teknis.
- SIMAP Garuda menjadi pilot project aplikasi partai mandiri.
- Project partai berikutnya sebaiknya dibuat dari template resmi, bukan fork manual yang berkembang sendiri-sendiri.
- `partai_profiles` di SIMAP utama tidak lagi menjadi prioritas utama untuk operasional partai, kecuali SIMAP utama tetap ingin menyediakan portal read-only multi-partai.
- Fokus strategis berikutnya adalah membuat standar `simap-partai-template` agar `simap-garuda`, `simap-golkar`, `simap-pkb`, dan project partai lain punya struktur yang konsisten.

Keputusan ini tidak membatalkan ide login `/partai/{slug}/login`, tetapi menurunkan prioritasnya. Login dinamis multi-partai tetap berguna jika SIMAP utama ingin memberi akses read-only cepat untuk banyak partai dari satu sistem. Namun untuk kebutuhan aplikasi operasional internal partai, pola project terpisah lebih aman.

## Checklist SIMAP Utama

Peran SIMAP utama setelah ada project partai mandiri:

- Sistem induk data pemilu dan rekap lengkap.
- Sumber pola fitur umum: wilayah, TPS, rekap, dashboard, export, import, dan validasi.
- Sumber data untuk export/snapshot per partai.
- Referensi bugfix dan perubahan aturan yang perlu diport ke project partai.

Yang sudah cukup tepat:

- Struktur wilayah kecamatan, desa, TPS sudah menjadi fondasi yang bisa dipakai semua project.
- Master rekap lengkap masih tersedia untuk semua jenis pemilihan.
- Role internal SIMAP utama tetap lengkap untuk kebutuhan operator.
- Akun role `partai` sudah ada sebagai konsep awal akses partai.
- Rekap legislatif sudah punya master partai/caleg yang dapat dipakai sebagai sumber export per partai.

Yang masih kurang atau perlu diputuskan:

- [ ] Tentukan apakah SIMAP utama tetap akan menyediakan portal partai read-only multi-partai.
- [ ] Jika portal read-only tetap dipakai, buat `partai_profiles`.
- [ ] Jika portal read-only tidak diprioritaskan, jangan bongkar besar `users.partai_id` dulu.
- [ ] Definisikan SIMAP utama sebagai `core/template source` untuk project partai.
- [ ] Buat format export resmi dari SIMAP utama ke project partai.
- [ ] Tentukan format snapshot: JSON, Excel, CSV, SQLite, atau kombinasi.
- [ ] Buat command export per partai, misalnya `export:party-snapshot {slug}`.
- [ ] Pastikan export per partai hanya membawa data yang boleh dibagikan.
- [ ] Jangan export user internal, password, log, dokumen mentah, catatan verifikasi, atau fitur koreksi.
- [ ] Dokumentasikan mapping partai berdasarkan identitas stabil, bukan hanya `nomor_urut`.
- [ ] Siapkan strategi sync bugfix dari SIMAP utama ke template/project partai.
- [ ] Pisahkan catatan fitur yang hanya milik SIMAP utama dari fitur yang boleh masuk template partai.

Jika tetap membuat `partai_profiles`, struktur minimal yang disarankan:

```text
partai_profiles
- id
- slug
- nama
- nama_singkat
- logo_path
- warna_utama
- warna_aksen
- nomor_urut_aktif
- nomor_urut_historis_json
- is_active
```

Catatan penting:

- `slug` menjadi identitas permanen aplikasi/partai.
- `nomor_urut` hanya metadata pemilu tertentu.
- `rekap_partais` tetap menjadi master teknis rekap per jenis pemilu/dapil.
- Relasi akun partai idealnya ke `partai_profiles`, bukan langsung ke satu baris `rekap_partais`.

## Checklist SIMAP Garuda

Peran SIMAP Garuda:

- Pilot project aplikasi partai mandiri.
- Bukti bahwa fork SIMAP bisa dibersihkan menjadi aplikasi internal satu partai.
- Contoh struktur role partai: Admin Partai, Korcam, Kordes, Saksi TPS.
- Kandidat awal untuk diekstrak menjadi template project partai.

Yang sudah tepat:

- Project berdiri sendiri di folder `simap-garuda`.
- Database terpisah dari SIMAP utama.
- Branding Partai Garuda sudah menjadi identitas aplikasi.
- Login dan UI sudah diarahkan ke konteks Partai Garuda.
- Role internal partai sudah dipakai di UI.
- Input suara manual TPS tersedia.
- Kordes dan Korcam sudah bisa ikut input/edit sesuai scope wilayah.
- Admin Partai bisa koreksi lintas wilayah.
- Data fokus ke Partai Garuda dan caleg Garuda.
- Dashboard dan export sudah fokus ke suara Garuda.
- Banyak fitur internal SIMAP utama sudah dibersihkan.

Yang masih kurang atau perlu dilanjutkan:

- [x] Putuskan apakah nilai role database tetap kompatibel (`admin/ppk/pps/kpps`) atau dimigrasi penuh ke `admin_partai/korcam/kordes/saksi_tps`; SIMAP Garuda sudah dimigrasi penuh.
- [x] Rename URI teknis `ppk/pps/kpps` menjadi istilah partai jika sudah siap.
- [x] Tambahkan backward redirect sementara jika URI lama diganti.
- [x] Audit aman kolom legacy seperti `users.partai_id`; hasilnya kolom user-partai tidak dipakai runtime SIMAP Garuda dan sudah dibersihkan lewat migration.
- [ ] Hapus relasi/model/tabel legacy non-partai lain jika sudah benar-benar tidak dipakai runtime.
- [ ] Pastikan semua tampilan publik tidak membawa istilah KPU/internal SIMAP utama yang tidak relevan.
- [ ] Finalisasi format import jika SIMAP Garuda nanti menerima snapshot dari SIMAP utama.
- [ ] Tambahkan dokumentasi operasional untuk Admin Partai, Korcam, Kordes, dan Saksi TPS.
- [ ] Tambahkan seed/demo data khusus Garuda untuk testing internal.
- [ ] Siapkan daftar perubahan yang perlu dipromosikan ke template partai.

Hal yang perlu dijaga:

- SIMAP Garuda jangan kembali bergantung runtime ke database SIMAP utama.
- Nomor urut Garuda jangan dijadikan identitas permanen.
- Perubahan fitur jangan terlalu spesifik Garuda jika sebenarnya berguna untuk template partai.
- Semua cleanup besar schema harus lewat migration dan test.

## Checklist SIMAP Partai Template

Peran `simap-partai-template`:

- Blueprint resmi untuk membuat project partai berikutnya.
- Sumber standar agar `simap-garuda`, `simap-golkar`, `simap-pkb`, dan project lain tidak berbeda liar.
- Tempat menaruh fitur generik partai tanpa branding partai tertentu.

Struktur yang disarankan:

```text
simap-partai-template
config/party.php
resources/images/party-logo.*
database/migrations
database/seeders
app/Services/PartyScopeService.php
app/Services/PartyImportService.php
app/Services/PartyExportService.php
```

Checklist awal template:

- [ ] Buat `config/party.php` sebagai sumber identitas partai.
- [ ] Isi config dengan `slug`, `name`, `short_name`, `logo_path`, `primary_color`, `accent_color`, dan `historical_numbers`.
- [ ] Buat `.env.example` yang jelas untuk nama app, database, dan party slug.
- [ ] Buat command setup awal project partai, misalnya `party:install`.
- [ ] Buat seeder akun Admin Partai awal.
- [ ] Buat seeder/config wilayah opsional jika project tidak import dari SIMAP utama.
- [ ] Standarkan role: `admin_partai`, `korcam`, `kordes`, `saksi_tps`.
- [ ] Standarkan middleware scope wilayah.
- [ ] Standarkan dashboard partai.
- [ ] Standarkan form input suara TPS.
- [ ] Standarkan export laporan partai.
- [ ] Standarkan status internal TPS: draft, perlu dicek, final.
- [ ] Standarkan catatan internal TPS.
- [ ] Standarkan import snapshot dari SIMAP utama.
- [ ] Standarkan validasi agar data hanya masuk untuk partai yang sesuai config.
- [ ] Buat test wajib: login, role scope, input TPS, update TPS, finalisasi, export, dashboard, dan guard data partai.
- [ ] Buat dokumentasi cara membuat project partai baru dari template.

Data minimal yang harus dimiliki tiap project partai:

- Wilayah: kecamatan, desa, TPS.
- Dapil jika DPRD Kabupaten dipakai.
- Identitas partai dari config.
- Master caleg partai.
- Rekap suara partai.
- Rekap suara caleg.
- User internal partai.
- Status input TPS.

Data yang tidak boleh ikut secara default:

- User admin/operator SIMAP utama.
- Password atau hash user dari SIMAP utama.
- Dokumen mentah internal.
- Catatan verifikasi internal KPU/SIMAP.
- Log import, backup, cache, dan audit internal.
- Data partai lain kecuali memang ada kebutuhan kompetitor dan sudah disetujui.

## Pola Membuat Project Partai Baru

Urutan kerja yang disarankan untuk partai berikutnya:

1. Clone dari `simap-partai-template`, bukan langsung dari SIMAP utama.
2. Set `APP_NAME`, database, dan `config/party.php`.
3. Pasang logo dan warna partai.
4. Jalankan migration dan seeder admin awal.
5. Import wilayah dari SIMAP utama atau seed wilayah manual.
6. Import/susun master caleg partai.
7. Import snapshot suara atau aktifkan input manual TPS.
8. Jalankan test template.
9. Review UI agar tidak ada branding/istilah partai lain.
10. Deploy sebagai project mandiri.

Contoh naming:

```text
simap-garuda
simap-golkar
simap-pkb
simap-gerindra
```

## Pembagian Tanggung Jawab Jangka Panjang

SIMAP utama:

- Menjadi sumber data penuh.
- Menjadi sumber export/snapshot.
- Menjadi tempat perubahan aturan umum.
- Menjadi referensi perbaikan bug inti.

SIMAP partai template:

- Menjadi standar aplikasi partai.
- Menjadi tempat fitur generik partai dikembangkan.
- Menjadi sumber fork untuk partai baru.

SIMAP Garuda dan project partai lain:

- Menjadi deployment mandiri.
- Menyimpan branding dan data partai masing-masing.
- Menerima bugfix/template update secara terkontrol.
- Tidak mengubah fitur core secara liar tanpa dipromosikan balik ke template jika berguna umum.

## Keputusan Roadmap Terbaru

Prioritas yang disarankan setelah SIMAP Garuda:

1. Selesaikan hardening SIMAP Garuda sampai benar-benar layak jadi acuan.
2. Ekstrak bagian generik SIMAP Garuda menjadi `simap-partai-template`.
3. Di SIMAP utama, buat export/snapshot per partai.
4. Buat dokumentasi membuat project partai baru.
5. Baru pertimbangkan `partai_profiles` di SIMAP utama jika masih ingin portal read-only multi-partai dari satu sistem.

Dengan arah ini, `partai_profiles` bukan dibuang, tetapi menjadi opsi untuk SIMAP utama. Untuk aplikasi mandiri per partai, identitas partai lebih sederhana dan lebih aman diletakkan di `config/party.php` masing-masing project.

## Checklist Eksekusi Multi-Project Partai

Bagian ini dipakai sebagai quick reference saat membuka session baru. Checklist dibagi per project supaya pekerjaan SIMAP utama, SIMAP Garuda, dan SIMAP Partai Template tidak tercampur.

### A. SIMAP Utama

Tujuan: SIMAP utama tetap menjadi core data, sumber export/snapshot, dan referensi bugfix.

- [ ] Tetapkan SIMAP utama sebagai sumber data penuh dan core teknis.
- [ ] Putuskan apakah portal read-only multi-partai tetap dibutuhkan di SIMAP utama.
- [ ] Jika portal read-only tetap dibutuhkan, rancang `partai_profiles` berisi identitas partai seperti `party_key`, nama, nomor urut, warna, logo, dan status aktif.
- [ ] Jangan migrasi besar ke `users.partai_id` sampai keputusan portal read-only jelas.
- [ ] Audit data apa saja yang boleh keluar ke project partai mandiri.
- [ ] Definisikan format snapshot per partai untuk hasil TPS, status TPS, wilayah, saksi, dan metadata pemilu.
- [ ] Buat command export snapshot seperti `export:party-snapshot {party}`.
- [ ] Pastikan export tidak membawa user internal, password, dokumen sensitif, log verifikasi, dan catatan koreksi internal.
- [ ] Dokumentasikan mapping partai dari SIMAP utama ke project partai: slug, nama, nomor urut historis, dan relasi ke data rekap.
- [ ] Tambahkan test export/snapshot per partai.
- [ ] Dokumentasikan proses porting bugfix dari SIMAP utama ke SIMAP Partai Template atau project partai mandiri.

### B. SIMAP Garuda

Tujuan: SIMAP Garuda menjadi pilot project matang dan blueprint nyata sebelum dibuat template partai.

- [x] Project sudah mandiri dari SIMAP utama.
- [x] Branding dasar Garuda sudah masuk ke login dan identitas aplikasi.
- [x] Input manual suara partai sudah fokus ke Garuda.
- [x] Role desa dan kecamatan sudah bisa ikut edit suara sesuai kebutuhan project partai.
- [x] Dashboard dan export sudah diarahkan ke kebutuhan Garuda.
- [x] Audit sisa penggunaan `users.partai_id` dan pastikan tidak ada scope multi-partai yang tidak perlu; kolom legacy ini sudah dihapus dari schema user SIMAP Garuda.
- [x] Putuskan role teknis final untuk aplikasi partai: admin kabupaten, kecamatan, desa, dan TPS.
- [x] Rename URI, label, dan teks yang masih terasa generik SIMAP utama jika mengganggu identitas Garuda.
- [ ] Audit model, controller, view, route, dan menu legacy non-party yang sudah tidak dipakai.
- [ ] Dokumentasikan cara setup SIMAP Garuda dari fresh clone sampai siap dipakai.
- [ ] Tandai fitur generik Garuda yang layak dipromosikan ke template.
- [ ] Tandai fitur spesifik Garuda yang tidak boleh masuk template.
- [ ] Siapkan compatibility dengan format snapshot/export dari SIMAP utama.

### C. SIMAP Partai Template

Tujuan: membuat blueprint resmi untuk membuat project partai lain setelah SIMAP Garuda stabil.

- [ ] Tentukan sumber awal template dari SIMAP Garuda setelah hardening selesai.
- [ ] Buat repo atau folder `simap-partai-template`.
- [ ] Jadikan `config/party.php` sebagai standar identitas partai.
- [ ] Standarkan isi identitas partai: slug, nama, nomor urut, warna utama, warna aksen, logo, dan label aplikasi.
- [ ] Buat command setup seperti `party:install` atau dokumentasi setup manual yang setara.
- [ ] Standarkan role dan permission untuk admin kabupaten, kecamatan, desa, dan TPS.
- [ ] Standarkan middleware scope supaya data selalu terbatas ke satu partai.
- [ ] Standarkan form input TPS, status pengisian, koreksi, dan audit trail.
- [ ] Standarkan dashboard, export, dan laporan yang umum dipakai semua partai.
- [ ] Standarkan import snapshot dari SIMAP utama.
- [ ] Tambahkan test wajib untuk input suara, role wilayah, export, dan import snapshot.
- [ ] Dokumentasikan langkah membuat project baru seperti `simap-{slug}` dari template.
- [ ] Pastikan tidak ada nama, logo, warna, atau teks Garuda yang hardcoded di template.

### D. Urutan Eksekusi Yang Disarankan

1. Lanjutkan SIMAP Garuda sampai checklist hardening pilot selesai.
2. Ekstrak SIMAP Partai Template dari SIMAP Garuda yang sudah bersih.
3. Buat export/snapshot di SIMAP utama setelah format kebutuhan template jelas.
4. Project partai berikutnya dibuat dari SIMAP Partai Template, bukan dari SIMAP utama langsung.
5. SIMAP utama hanya menambahkan `partai_profiles` jika portal read-only multi-partai memang masih dibutuhkan.

### E. Yang Ditunda Dulu

- [ ] Jangan prioritaskan `partai_profiles` kalau fokus utama masih project partai mandiri.
- [ ] Jangan pecah project partai baru langsung dari SIMAP utama sebelum template siap.
- [ ] Jangan masukkan fitur spesifik Garuda ke template tanpa alasan generik.
- [ ] Jangan export data sensitif dari SIMAP utama ke project partai.
