<?php

namespace App\Console\Commands;

class ImportDprdProvFolder extends ImportDprRiFolder
{
    /*
     * CARA PAKAI IMPORTER DPRD PROV FOLDER
     * -------------------------------------------------------------------------
     * Aturan sama seperti importer DPR RI folder:
     *
     * - Satu folder berisi banyak file Excel.
     * - Satu file Excel mewakili satu kecamatan.
     * - Nama file mengikuti pola "DPRD PROV - NAMAKECAMATAN.xlsx".
     * - Di dalam satu file, tiap sheet mewakili satu desa.
     * - Nama sheet dipakai sebagai nama desa utama.
     * - Sheet pembuka seperti "DPRD PROV" dilewati otomatis kalau tidak punya TPS.
     *
     * Wajib cek dulu tanpa mengubah database:
     *
     *   php artisan import:dprd-prov-folder "storage/import/DPRD PROV" --dry-run
     *
     * Cek satu kecamatan saja:
     *
     *   php artisan import:dprd-prov-folder "storage/import/DPRD PROV" --only=Bangorejo --dry-run
     *
     * Cek satu desa/sheet saja di dalam kecamatan:
     *
     *   php artisan import:dprd-prov-folder "storage/import/DPRD PROV" --only=Bangorejo --desa=Sambirejo --dry-run
     *
     * Import asli setelah dry-run bersih:
     *
     *   php artisan import:dprd-prov-folder "storage/import/DPRD PROV"
     */
    protected $signature = 'import:dprd-prov-folder
        {path=storage/import/DPRD PROV : Folder berisi file DPRD PROV per kecamatan atau satu file Excel DPRD PROV}
        {--dry-run : Validasi dan tampilkan ringkasan tanpa menyimpan ke database}
        {--only=* : Batasi import ke nama kecamatan tertentu}
        {--desa=* : Batasi import ke nama desa/sheet tertentu}';

    protected $description = 'Import rekap DPRD Provinsi dari folder Excel per kecamatan dan sheet per desa.';

    protected const JENIS = 'dprd_prov';

    protected const LABEL = 'DPRD PROV';

    protected const FILE_PREFIX_REGEX = '/^DPRD\s+PROV(?:INSI)?\s*-\s*/i';
}
