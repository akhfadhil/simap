<?php

namespace App\Console\Commands;

use App\Models\Desa;
use App\Models\RekapDpdCalon;
use App\Models\RekapDpdSuara;
use App\Models\RekapHeader;
use App\Models\Tps;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportBangorejoDpd extends Command
{
    protected $signature = 'import:bangorejo-dpd
        {file=DPD - BANGOREJO.xlsx : Path file Excel}
        {--dry-run : Validasi dan tampilkan koreksi tanpa menyimpan ke database}';

    protected $description = 'Import rekap DPD Kecamatan Bangorejo dari file Excel per TPS.';

    private const KECAMATAN = 'Bangorejo';

    private const CALON_START_ROW = 45;
    private const CALON_END_ROW = 57;

    public function handle(): int
    {
        $path = $this->argument('file');
        $path = is_file($path) ? $path : base_path($path);

        if (!is_file($path)) {
            $this->error('File tidak ditemukan: ' . $path);
            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);
        $calons = $this->calonsFromWorkbook($spreadsheet);
        if ($calons === []) {
            $this->error('Daftar calon DPD tidak terbaca dari file.');
            return self::FAILURE;
        }

        $rows = [];
        $corrections = [];
        $missing = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if (strtolower((string) $this->cell($sheet, 'D2')) !== strtolower(self::KECAMATAN)) {
                continue;
            }

            $desaNama = $this->titleName((string) $this->cell($sheet, 'D9'));
            if ($desaNama === '') {
                continue;
            }

            for ($col = Coordinate::columnIndexFromString('E'); $col <= Coordinate::columnIndexFromString('AF'); $col++) {
                $column = Coordinate::stringFromColumnIndex($col);
                $tpsNama = trim((string) $this->cell($sheet, "{$column}13"));
                if (!preg_match('/^TPS\s+\d{3}$/i', $tpsNama)) {
                    continue;
                }

                $record = $this->recordFromSheet($sheet, $column, $desaNama, strtoupper($tpsNama), array_keys($calons));
                $this->normalizeRecord($record, $corrections);
                $rows[] = $record;
            }
        }

        if ($rows === []) {
            $this->error('Tidak ada data TPS yang terbaca dari file.');
            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->line('DRY RUN: database tidak diubah.');
            $this->printReport($rows, $corrections, $missing, $calons);
            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows, $calons, &$missing) {
            $calonIds = $this->syncCalons($calons);

            foreach ($rows as $row) {
                $desa = $this->findDesa($row['desa']);
                if (!$desa) {
                    $missing[] = "{$row['desa']}: desa tidak ditemukan.";
                    continue;
                }

                $tps = Tps::firstOrCreate([
                    'desa_id' => $desa->id,
                    'nama' => $row['tps'],
                ]);

                $rekap = RekapHeader::updateOrCreate(
                    ['tps_id' => $tps->id, 'jenis' => 'dpd'],
                    [
                        'dpt_lk' => $row['dpt_lk'],
                        'dpt_pr' => $row['dpt_pr'],
                        'pengguna_dpt_lk' => $row['pengguna_dpt_lk'],
                        'pengguna_dpt_pr' => $row['pengguna_dpt_pr'],
                        'pengguna_dptb_lk' => $row['pengguna_dptb_lk'],
                        'pengguna_dptb_pr' => $row['pengguna_dptb_pr'],
                        'pengguna_dpk_lk' => $row['pengguna_dpk_lk'],
                        'pengguna_dpk_pr' => $row['pengguna_dpk_pr'],
                        'ss_diterima' => $row['ss_diterima'],
                        'ss_digunakan' => $row['ss_digunakan'],
                        'ss_rusak' => $row['ss_rusak'],
                        'ss_sisa' => $row['ss_sisa'],
                        'disabilitas_lk' => $row['disabilitas_lk'],
                        'disabilitas_pr' => $row['disabilitas_pr'],
                        'suara_tidak_sah' => $row['suara_tidak_sah'],
                        'status' => 'final',
                        'difinalisasi_at' => Carbon::now(),
                    ]
                );

                foreach ($row['suara'] as $nomorUrut => $suara) {
                    RekapDpdSuara::updateOrCreate(
                        ['rekap_id' => $rekap->id, 'calon_id' => $calonIds[$nomorUrut]],
                        ['suara' => $suara]
                    );
                }

                RekapDpdSuara::where('rekap_id', $rekap->id)
                    ->whereNotIn('calon_id', array_values($calonIds))
                    ->delete();
            }
        });

        $this->printReport($rows, $corrections, $missing, $calons);

        return self::SUCCESS;
    }

    private function calonsFromWorkbook($spreadsheet): array
    {
        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if (strtolower((string) $this->cell($sheet, 'D2')) !== strtolower(self::KECAMATAN)) {
                continue;
            }

            $calons = [];
            for ($row = self::CALON_START_ROW; $row <= self::CALON_END_ROW; $row++) {
                $nomor = $this->intCell($sheet, "B{$row}");
                $nama = trim((string) $this->cell($sheet, "C{$row}"));
                if ($nomor > 0 && $nama !== '') {
                    $calons[$nomor] = $nama;
                }
            }

            if ($calons !== []) {
                return $calons;
            }
        }

        return [];
    }

    private function recordFromSheet(Worksheet $sheet, string $column, string $desaNama, string $tpsNama, array $nomorUrutCalons): array
    {
        $suara = [];
        foreach ($nomorUrutCalons as $nomorUrut) {
            $suara[$nomorUrut] = $this->intCell($sheet, $column . (44 + $nomorUrut));
        }

        return [
            'desa' => $desaNama,
            'tps' => $tpsNama,
            'dpt_lk' => $this->intCell($sheet, "{$column}14"),
            'dpt_pr' => $this->intCell($sheet, "{$column}15"),
            'pengguna_dpt_lk' => $this->intCell($sheet, "{$column}19"),
            'pengguna_dpt_pr' => $this->intCell($sheet, "{$column}20"),
            'pengguna_dptb_lk' => $this->intCell($sheet, "{$column}22"),
            'pengguna_dptb_pr' => $this->intCell($sheet, "{$column}23"),
            'pengguna_dpk_lk' => $this->intCell($sheet, "{$column}25"),
            'pengguna_dpk_pr' => $this->intCell($sheet, "{$column}26"),
            'pengguna_total_excel' => $this->intCell($sheet, "{$column}30"),
            'ss_diterima' => $this->intCell($sheet, "{$column}33"),
            'ss_digunakan' => $this->intCell($sheet, "{$column}34"),
            'ss_rusak' => $this->intCell($sheet, "{$column}35"),
            'ss_sisa' => $this->intCell($sheet, "{$column}36"),
            'disabilitas_lk' => $this->intCell($sheet, "{$column}39"),
            'disabilitas_pr' => $this->intCell($sheet, "{$column}40"),
            'suara' => $suara,
            'suara_sah_excel' => $this->intCell($sheet, "{$column}60"),
            'suara_tidak_sah' => $this->intCell($sheet, "{$column}61"),
            'suara_total_excel' => $this->intCell($sheet, "{$column}62"),
        ];
    }

    private function normalizeRecord(array &$row, array &$corrections): void
    {
        $label = "{$row['desa']} {$row['tps']}";
        $suaraSah = array_sum($row['suara']);

        if ($row['suara_sah_excel'] !== $suaraSah) {
            $corrections[] = "{$label}: suara sah Excel {$row['suara_sah_excel']} disesuaikan ke jumlah calon {$suaraSah}.";
        }

        $penggunaTotal = $row['pengguna_dpt_lk'] + $row['pengguna_dpt_pr']
            + $row['pengguna_dptb_lk'] + $row['pengguna_dptb_pr']
            + $row['pengguna_dpk_lk'] + $row['pengguna_dpk_pr'];

        if ($row['pengguna_total_excel'] !== $penggunaTotal) {
            $before = $row['pengguna_dpt_pr'];
            $row['pengguna_dpt_pr'] = max(0, $row['pengguna_dpt_pr'] + ($row['pengguna_total_excel'] - $penggunaTotal));
            $corrections[] = "{$label}: total pengguna Excel {$row['pengguna_total_excel']} tidak sama dengan rincian {$penggunaTotal}; pengguna_dpt_pr disesuaikan {$before} -> {$row['pengguna_dpt_pr']}.";
            $penggunaTotal = $row['pengguna_dpt_lk'] + $row['pengguna_dpt_pr']
                + $row['pengguna_dptb_lk'] + $row['pengguna_dptb_pr']
                + $row['pengguna_dpk_lk'] + $row['pengguna_dpk_pr'];
        }

        $authoritativeTotal = $row['ss_digunakan'] > 0 ? $row['ss_digunakan'] : $row['suara_total_excel'];
        if ($authoritativeTotal <= 0) {
            $authoritativeTotal = $suaraSah + $row['suara_tidak_sah'];
        }

        if ($penggunaTotal !== $authoritativeTotal) {
            $before = $row['pengguna_dpt_pr'];
            $row['pengguna_dpt_pr'] = max(0, $row['pengguna_dpt_pr'] + ($authoritativeTotal - $penggunaTotal));
            $corrections[] = "{$label}: pengguna hak pilih {$penggunaTotal} tidak sama dengan surat suara digunakan {$authoritativeTotal}; pengguna_dpt_pr disesuaikan {$before} -> {$row['pengguna_dpt_pr']}.";
            $penggunaTotal = $row['pengguna_dpt_lk'] + $row['pengguna_dpt_pr']
                + $row['pengguna_dptb_lk'] + $row['pengguna_dptb_pr']
                + $row['pengguna_dpk_lk'] + $row['pengguna_dpk_pr'];
        }

        $expectedTidakSah = max(0, $authoritativeTotal - $suaraSah);
        if ($row['suara_tidak_sah'] !== $expectedTidakSah) {
            $corrections[] = "{$label}: suara tidak sah {$row['suara_tidak_sah']} disesuaikan ke surat suara digunakan - suara calon {$expectedTidakSah}.";
            $row['suara_tidak_sah'] = $expectedTidakSah;
        }

        $suaraTotal = $suaraSah + $row['suara_tidak_sah'];
        if ($row['suara_total_excel'] !== $suaraTotal) {
            $corrections[] = "{$label}: total suara Excel {$row['suara_total_excel']} tidak sama dengan hasil koreksi {$suaraTotal}.";
        }

        if ($row['ss_digunakan'] !== $authoritativeTotal) {
            $corrections[] = "{$label}: surat suara digunakan {$row['ss_digunakan']} disesuaikan ke {$authoritativeTotal}.";
            $row['ss_digunakan'] = $authoritativeTotal;
        }

        $ssSisa = max(0, $row['ss_diterima'] - $row['ss_digunakan'] - $row['ss_rusak']);
        if ($row['ss_sisa'] !== $ssSisa) {
            $corrections[] = "{$label}: surat suara sisa {$row['ss_sisa']} disesuaikan ke diterima-digunakan-rusak {$ssSisa}.";
            $row['ss_sisa'] = $ssSisa;
        }
    }

    private function syncCalons(array $calons): array
    {
        $ids = [];

        foreach ($calons as $nomorUrut => $namaCalon) {
            $calon = RekapDpdCalon::updateOrCreate(
                ['nomor_urut' => $nomorUrut],
                ['nama_calon' => $namaCalon]
            );
            $ids[$nomorUrut] = $calon->id;
        }

        return $ids;
    }

    private function findDesa(string $nama): ?Desa
    {
        return Desa::query()
            ->whereRaw('LOWER(nama) = ?', [strtolower($nama)])
            ->whereHas('kecamatan', fn($query) => $query->whereRaw('LOWER(nama) = ?', [strtolower(self::KECAMATAN)]))
            ->first();
    }

    private function printReport(array $rows, array $corrections, array $missing, array $calons): void
    {
        $this->newLine();
        $this->info('Import DPD Bangorejo selesai.');
        $this->line('Calon terbaca: ' . count($calons));
        $this->line('TPS terbaca: ' . count($rows));
        $this->line('Koreksi data: ' . count($corrections));

        $summary = collect($rows)
            ->groupBy('desa')
            ->map(fn($items, $desa) => [
                'Desa' => $desa,
                'TPS' => $items->count(),
                'DPT' => $items->sum(fn($row) => $row['dpt_lk'] + $row['dpt_pr']),
                'Pengguna' => $items->sum(fn($row) => $row['pengguna_dpt_lk'] + $row['pengguna_dpt_pr'] + $row['pengguna_dptb_lk'] + $row['pengguna_dptb_pr'] + $row['pengguna_dpk_lk'] + $row['pengguna_dpk_pr']),
                'Sah' => $items->sum(fn($row) => array_sum($row['suara'])),
                'Tidak Sah' => $items->sum('suara_tidak_sah'),
            ])
            ->values()
            ->all();

        $this->table(['Desa', 'TPS', 'DPT', 'Pengguna', 'Sah', 'Tidak Sah'], $summary);

        $this->line('Daftar calon DPD:');
        foreach ($calons as $nomor => $nama) {
            $this->line("- {$nomor}. {$nama}");
        }

        if ($missing !== []) {
            $this->warn('Data tidak tersimpan:');
            foreach ($missing as $message) {
                $this->line('- ' . $message);
            }
        }

        if ($corrections !== []) {
            $this->warn('Daftar koreksi:');
            foreach ($corrections as $message) {
                $this->line('- ' . $message);
            }
        }
    }

    private function intCell(Worksheet $sheet, string $cell): int
    {
        $value = $this->cell($sheet, $cell);

        if ($value === null || $value === '') {
            return 0;
        }

        return (int) round((float) $value);
    }

    private function cell(Worksheet $sheet, string $cell): mixed
    {
        return $sheet->getCell($cell)->getCalculatedValue();
    }

    private function titleName(string $value): string
    {
        return str($value)->lower()->title()->toString();
    }
}
