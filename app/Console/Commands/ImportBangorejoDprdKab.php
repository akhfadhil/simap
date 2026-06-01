<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;

class ImportBangorejoDprdKab extends ImportBangorejoLegislative
{
    protected $signature = 'import:bangorejo-dprd-kab
        {file=DPRD KAB - BANGOREJO.xlsx : Path file Excel}
        {--dry-run : Validasi dan tampilkan koreksi tanpa menyimpan ke database}';

    protected $description = 'Import rekap DPRD Kabupaten Kecamatan Bangorejo dari file Excel per TPS.';

    protected const JENIS = 'dprd_kab';

    protected const LABEL = 'DPRD KAB';

    protected function masterDapilId(): ?int
    {
        $dapilId = DB::table('kecamatans')
            ->where('nama', static::KECAMATAN)
            ->value('dapil_id');

        if (! $dapilId) {
            throw new \RuntimeException('Kecamatan '.static::KECAMATAN.' belum punya dapil.');
        }

        return (int) $dapilId;
    }
}
