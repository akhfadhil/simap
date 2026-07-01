<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\PartaiProfile;
use App\Models\RekapPartai;
use App\Models\RekapCaleg;
use App\Models\Dapil;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Tps;
use App\Models\RekapHeader;
use App\Models\RekapPartaiSuara;
use App\Models\RekapCalegSuara;

class ExportPartySnapshot extends Command
{
    protected $signature = 'export:party-snapshot {slug : Slug profil partai}';
    protected $description = 'Ekspor data wilayah, data partai, caleg, dan perolehan suara legislatif partai tertentu ke file JSON snapshot.';

    public function handle(): int
    {
        ini_set('memory_limit', '-1');
        $slug = $this->argument('slug');
        $profile = PartaiProfile::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->first();

        if (!$profile) {
            $this->error("Profil partai dengan slug '{$slug}' tidak ditemukan.");
            return 1;
        }

        $this->info("Memulai ekspor snapshot untuk partai: {$profile->nama} ({$profile->nama_singkat})...");

        // 1. Dapatkan semua RekapPartai
        $partaiMatches = RekapPartai::all();
        $partaiIds = $partaiMatches->pluck('id')->toArray();

        // 2. Dapatkan semua RekapCaleg
        $calegs = RekapCaleg::all();
        $calegIds = $calegs->pluck('id')->toArray();

        // 3. Muat data wilayah secara lengkap
        $dapils = Dapil::all();
        $kecamatans = Kecamatan::all();
        $desas = Desa::all();
        $tps = Tps::all();

        // 4. Muat RekapHeader untuk jenis pemilihan legislatif saja
        $rekapHeaders = RekapHeader::whereIn('jenis', ['dpr_ri', 'dprd_prov', 'dprd_kab'])->get();
        $rekapHeaderIds = $rekapHeaders->pluck('id')->toArray();

        // 5. Muat suara partai yang cocok saja
        $partaiSuaras = RekapPartaiSuara::whereIn('rekap_id', $rekapHeaderIds)
            ->whereIn('partai_id', $partaiIds)
            ->get();

        // 6. Muat suara caleg yang cocok saja
        $calegSuaras = RekapCalegSuara::whereIn('rekap_id', $rekapHeaderIds)
            ->whereIn('caleg_id', $calegIds)
            ->get();

        // 7. Susun data snapshot
        $snapshot = [
            'exported_at' => now()->toIso8601String(),
            'source_app' => 'SIMAP Utama',
            'party_profile' => [
                'id' => $profile->id,
                'slug' => $profile->slug,
                'nama' => $profile->nama,
                'nama_singkat' => $profile->nama_singkat,
                'logo_path' => $profile->logo_path,
                'warna_utama' => $profile->warna_utama,
                'warna_aksen' => $profile->warna_aksen,
                'nomor_urut_aktif' => $profile->nomor_urut_aktif,
                'nomor_urut_historis_json' => $profile->nomor_urut_historis_json,
            ],
            'dapils' => $dapils->map(fn($item) => $item->only(['id', 'nama'])),
            'kecamatans' => $kecamatans->map(fn($item) => $item->only(['id', 'nama', 'dapil_id'])),
            'desas' => $desas->map(fn($item) => $item->only(['id', 'kecamatan_id', 'nama'])),
            'tps' => $tps->map(fn($item) => $item->only(['id', 'desa_id', 'nama'])),
            'rekap_partais' => $partaiMatches->map(fn($item) => $item->only(['id', 'jenis', 'nomor_urut', 'nama_partai', 'dapil_id'])),
            'rekap_calegs' => $calegs->map(fn($item) => $item->only(['id', 'partai_id', 'nomor_urut', 'nama_caleg'])),
            'rekap_headers' => $rekapHeaders->map(fn($item) => $item->only([
                'id', 'tps_id', 'jenis',
                'dpt_lk', 'dpt_pr',
                'pengguna_dpt_lk', 'pengguna_dpt_pr',
                'pengguna_dptb_lk', 'pengguna_dptb_pr',
                'pengguna_dpk_lk', 'pengguna_dpk_pr',
                'ss_diterima', 'ss_digunakan', 'ss_rusak', 'ss_sisa',
                'disabilitas_lk', 'disabilitas_pr',
                'suara_sah', 'suara_tidak_sah', 'status', 'difinalisasi_at'
            ])),
            'rekap_partai_suaras' => $partaiSuaras->map(fn($item) => $item->only(['id', 'rekap_id', 'partai_id', 'suara'])),
            'rekap_caleg_suaras' => $calegSuaras->map(fn($item) => $item->only(['id', 'rekap_id', 'caleg_id', 'suara'])),
        ];

        // 8. Tulis data ke storage Laravel
        $filename = "exports/party-snapshot-{$profile->slug}-" . date('Ymd-His') . ".json";
        
        if (!Storage::exists('exports')) {
            Storage::makeDirectory('exports');
        }

        Storage::put($filename, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $fullPath = Storage::path($filename);
        $this->info("✓ Ekspor selesai! Snapshot disimpan ke: {$fullPath}");

        return 0;
    }
}
