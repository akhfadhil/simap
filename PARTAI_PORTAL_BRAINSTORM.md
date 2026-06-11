# Brainstorm Portal Partai SIMAP

Catatan ini merangkum ide pengembangan portal partai di SIMAP berdasarkan diskusi perencanaan fitur.

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
