<?php

namespace App\Console\Commands;

class ImportBangorejoDprdProv extends ImportBangorejoDprRi
{
    protected $signature = 'import:bangorejo-dprd-prov
        {file=DPRD PROV - BANGOREJO.xlsx : Path file Excel}
        {--dry-run : Validasi dan tampilkan koreksi tanpa menyimpan ke database}';

    protected $description = 'Import rekap DPRD Provinsi Kecamatan Bangorejo dari file Excel per TPS.';

    protected const JENIS = 'dprd_prov';
    protected const LABEL = 'DPRD PROV';
}
