<?php

namespace App\Console\Commands;

use App\Models\Desa;
use App\Models\RekapHeader;
use App\Models\RekapPpwpCalon;
use App\Models\RekapPpwpSuara;
use App\Models\Tps;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportBangorejoPpwp extends Command
{
    protected $signature = 'import:bangorejo-ppwp
        {file=PPWP - BANGOREJO.xlsx : Path file Excel}
        {--dry-run : Validasi dan tampilkan koreksi tanpa menyimpan ke database}';

    protected $description = 'Import rekap PPWP Kecamatan Bangorejo dari file Excel per TPS.';

    private const KECAMATAN = 'Bangorejo';

    private const PPWP_CALONS = [
        1 => 'H. ANIES RASYID BASWEDAN, Ph.D. - Dr. (H.C.) H. A. MUHAIMIN ISKANDAR',
        2 => 'H. PRABOWO SUBIANTO - GIBRAN RAKABUMING RAKA',
        3 => 'H. GANJAR PRANOWO, S.H., M.I.P. - Prof. Dr. H. M. MAHFUD MD',
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        $path = is_file($path) ? $path : base_path($path);

        if (!is_file($path)) {
            $this->error('File tidak ditemukan: ' . $path);
            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);
        $rows = [];
        $corrections = [];
        $missing = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
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

                $record = $this->recordFromSheet($sheet, $column, $desaNama, strtoupper($tpsNama));
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
            $this->printReport($rows, $corrections, $missing);
            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows, &$missing) {
            $calonIds = $this->syncCalons();

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
                    ['tps_id' => $tps->id, 'jenis' => 'ppwp'],
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
                    RekapPpwpSuara::updateOrCreate(
                        ['rekap_id' => $rekap->id, 'calon_id' => $calonIds[$nomorUrut]],
                        ['suara' => $suara]
                    );
                }

                RekapPpwpSuara::where('rekap_id', $rekap->id)
                    ->whereNotIn('calon_id', array_values($calonIds))
                    ->delete();
            }
        });

        $this->printReport($rows, $corrections, $missing);

        return self::SUCCESS;
    }

    private function recordFromSheet(Worksheet $sheet, string $column, string $desaNama, string $tpsNama): array
    {
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
            'suara' => [
                1 => $this->intCell($sheet, "{$column}45"),
                2 => $this->intCell($sheet, "{$column}46"),
                3 => $this->intCell($sheet, "{$column}47"),
            ],
            'suara_sah_excel' => $this->intCell($sheet, "{$column}50"),
            'suara_tidak_sah' => $this->intCell($sheet, "{$column}51"),
            'suara_total_excel' => $this->intCell($sheet, "{$column}52"),
        ];
    }

    private function normalizeRecord(array &$row, array &$corrections): void
    {
        $label = "{$row['desa']} {$row['tps']}";
        $suaraSah = array_sum($row['suara']);
        $suaraTotal = $suaraSah + $row['suara_tidak_sah'];

        if ($row['suara_sah_excel'] !== $suaraSah) {
            $corrections[] = "{$label}: suara sah Excel {$row['suara_sah_excel']} disesuaikan ke jumlah paslon {$suaraSah}.";
        }

        if ($row['suara_total_excel'] !== $suaraTotal) {
            $newTidakSah = max(0, $row['suara_total_excel'] - $suaraSah);
            $corrections[] = "{$label}: total suara Excel {$row['suara_total_excel']} tidak sama dengan paslon+tidak sah {$suaraTotal}; suara tidak sah disesuaikan {$row['suara_tidak_sah']} -> {$newTidakSah}.";
            $row['suara_tidak_sah'] = $newTidakSah;
            $suaraTotal = $suaraSah + $row['suara_tidak_sah'];
        }

        $penggunaTotal = $row['pengguna_dpt_lk'] + $row['pengguna_dpt_pr']
            + $row['pengguna_dptb_lk'] + $row['pengguna_dptb_pr']
            + $row['pengguna_dpk_lk'] + $row['pengguna_dpk_pr'];

        if ($row['pengguna_total_excel'] !== $penggunaTotal) {
            $corrections[] = "{$label}: total pengguna Excel {$row['pengguna_total_excel']} tidak sama dengan rincian {$penggunaTotal}; rincian dipakai.";
        }

        if ($penggunaTotal !== $suaraTotal) {
            $diff = $suaraTotal - $penggunaTotal;
            $before = $row['pengguna_dpt_pr'];
            $row['pengguna_dpt_pr'] = max(0, $row['pengguna_dpt_pr'] + $diff);
            $corrections[] = "{$label}: pengguna hak pilih {$penggunaTotal} tidak sama dengan sah+tidak sah {$suaraTotal}; pengguna_dpt_pr disesuaikan {$before} -> {$row['pengguna_dpt_pr']}.";
        }

        if ($row['ss_digunakan'] !== $suaraTotal) {
            $corrections[] = "{$label}: surat suara digunakan {$row['ss_digunakan']} disesuaikan ke sah+tidak sah {$suaraTotal}.";
            $row['ss_digunakan'] = $suaraTotal;
        }

        $ssSisa = max(0, $row['ss_diterima'] - $row['ss_digunakan'] - $row['ss_rusak']);
        if ($row['ss_sisa'] !== $ssSisa) {
            $corrections[] = "{$label}: surat suara sisa {$row['ss_sisa']} disesuaikan ke diterima-digunakan-rusak {$ssSisa}.";
            $row['ss_sisa'] = $ssSisa;
        }
    }

    private function syncCalons(): array
    {
        $ids = [];

        foreach (self::PPWP_CALONS as $nomorUrut => $namaPaslon) {
            $calon = RekapPpwpCalon::updateOrCreate(
                ['nomor_urut' => $nomorUrut],
                ['nama_paslon' => $namaPaslon]
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

    private function printReport(array $rows, array $corrections, array $missing): void
    {
        $this->newLine();
        $this->info('Import PPWP Bangorejo selesai.');
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
