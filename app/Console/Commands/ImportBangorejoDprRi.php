<?php

namespace App\Console\Commands;

use App\Models\Desa;
use App\Models\RekapCaleg;
use App\Models\RekapCalegSuara;
use App\Models\RekapHeader;
use App\Models\RekapPartai;
use App\Models\RekapPartaiSuara;
use App\Models\Tps;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportBangorejoDprRi extends Command
{
    protected $signature = 'import:bangorejo-dpr-ri
        {file=DPR RI - BANGOREJO.xlsx : Path file Excel}
        {--dry-run : Validasi dan tampilkan koreksi tanpa menyimpan ke database}';

    protected $description = 'Import rekap DPR RI Kecamatan Bangorejo dari file Excel per TPS.';

    protected const KECAMATAN = 'Bangorejo';
    protected const JENIS = 'dpr_ri';
    protected const LABEL = 'DPR RI';

    public function handle(): int
    {
        $path = $this->argument('file');
        $path = is_file($path) ? $path : base_path($path);

        if (!is_file($path)) {
            $this->error('File tidak ditemukan: ' . $path);
            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);
        $masterSheet = $this->firstDetailSheet($spreadsheet);
        if (!$masterSheet) {
            $this->error('Sheet detail ' . static::LABEL . ' Bangorejo tidak ditemukan.');
            return self::FAILURE;
        }

        $partais = $this->partaisFromSheet($masterSheet);
        if ($partais === []) {
            $this->error('Data partai/caleg tidak terbaca dari file.');
            return self::FAILURE;
        }

        $rows = [];
        $corrections = [];
        $missing = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if (strtolower((string) $this->cell($sheet, 'D2')) !== strtolower(static::KECAMATAN)) {
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

                $record = $this->recordFromSheet($sheet, $column, $desaNama, strtoupper($tpsNama), $partais);
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
            $this->printReport($rows, $corrections, $missing, $partais);
            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows, $partais, &$missing) {
            $masterIds = $this->syncMaster($partais);

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
                    ['tps_id' => $tps->id, 'jenis' => static::JENIS],
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

                foreach ($row['partai_suara'] as $nomorPartai => $suara) {
                    RekapPartaiSuara::updateOrCreate(
                        ['rekap_id' => $rekap->id, 'partai_id' => $masterIds['partais'][$nomorPartai]],
                        ['suara' => $suara]
                    );
                }

                foreach ($row['caleg_suara'] as $nomorPartai => $calegSuaras) {
                    foreach ($calegSuaras as $nomorCaleg => $suara) {
                        RekapCalegSuara::updateOrCreate(
                            ['rekap_id' => $rekap->id, 'caleg_id' => $masterIds['calegs'][$nomorPartai][$nomorCaleg]],
                            ['suara' => $suara]
                        );
                    }
                }

                RekapPartaiSuara::where('rekap_id', $rekap->id)
                    ->whereNotIn('partai_id', array_values($masterIds['partais']))
                    ->delete();

                $calegIds = collect($masterIds['calegs'])->flatten()->values()->all();
                RekapCalegSuara::where('rekap_id', $rekap->id)
                    ->whereNotIn('caleg_id', $calegIds)
                    ->delete();
            }
        });

        $this->printReport($rows, $corrections, $missing, $partais);

        return self::SUCCESS;
    }

    private function firstDetailSheet($spreadsheet): ?Worksheet
    {
        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if (strtolower((string) $this->cell($sheet, 'D2')) === strtolower(static::KECAMATAN)) {
                return $sheet;
            }
        }

        return null;
    }

    private function partaisFromSheet(Worksheet $sheet): array
    {
        $partais = [];
        $currentPartai = null;

        for ($row = 43; $row <= 260; $row++) {
            $section = trim((string) $this->cell($sheet, "A{$row}"));
            $nomor = $this->intCell($sheet, "B{$row}");
            $name = trim((string) $this->cell($sheet, "C{$row}"));

            if ($section === 'A.1' && $nomor > 0 && $name !== '') {
                $currentPartai = $nomor;
                $partais[$currentPartai] = [
                    'row' => $row,
                    'nama' => $name,
                    'total_row' => null,
                    'calegs' => [],
                ];
                continue;
            }

            if ($section === 'B' && $currentPartai !== null) {
                $partais[$currentPartai]['total_row'] = $row;
                $currentPartai = null;
                continue;
            }

            if ($currentPartai !== null && $name !== '') {
                $nomorCaleg = $nomor > 0 ? $nomor : $this->candidateNumberFromName($name);
                if ($nomorCaleg <= 0) {
                    continue;
                }

                $partais[$currentPartai]['calegs'][$nomorCaleg] = [
                    'row' => $row,
                    'nama' => $this->cleanCandidateName($name),
                ];
            }
        }

        return $partais;
    }

    private function recordFromSheet(Worksheet $sheet, string $column, string $desaNama, string $tpsNama, array $partais): array
    {
        $partaiSuara = [];
        $calegSuara = [];
        $partaiTotalsExcel = [];
        $suaraRows = $this->suaraRows($sheet);

        foreach ($partais as $nomorPartai => $partai) {
            $partaiSuara[$nomorPartai] = $this->intCell($sheet, $column . $partai['row']);
            $partaiTotalsExcel[$nomorPartai] = $partai['total_row'] ? $this->intCell($sheet, $column . $partai['total_row']) : null;
            foreach ($partai['calegs'] as $nomorCaleg => $caleg) {
                $calegSuara[$nomorPartai][$nomorCaleg] = $this->intCell($sheet, $column . $caleg['row']);
            }
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
            'partai_suara' => $partaiSuara,
            'caleg_suara' => $calegSuara,
            'partai_totals_excel' => $partaiTotalsExcel,
            'suara_sah_excel' => $this->intCell($sheet, $column . $suaraRows['sah']),
            'suara_tidak_sah' => $this->intCell($sheet, $column . $suaraRows['tidak_sah']),
            'suara_total_excel' => $this->intCell($sheet, $column . $suaraRows['total']),
        ];
    }

    private function normalizeRecord(array &$row, array &$corrections): void
    {
        $label = "{$row['desa']} {$row['tps']}";
        $suaraSah = $this->sumSah($row);

        foreach ($row['partai_suara'] as $nomorPartai => $suaraPartai) {
            $total = $suaraPartai + array_sum($row['caleg_suara'][$nomorPartai] ?? []);
            $excelTotal = $row['partai_totals_excel'][$nomorPartai] ?? null;
            if ($excelTotal !== null && $excelTotal !== $total) {
                $corrections[] = "{$label}: total partai {$nomorPartai} Excel {$excelTotal} tidak sama dengan partai+caleg {$total}; rincian dipakai.";
            }
        }

        if ($row['suara_sah_excel'] !== $suaraSah) {
            $corrections[] = "{$label}: suara sah Excel {$row['suara_sah_excel']} disesuaikan ke jumlah partai+caleg {$suaraSah}.";
        }

        $penggunaTotal = $this->sumPengguna($row);
        if ($row['pengguna_total_excel'] !== $penggunaTotal) {
            $before = $row['pengguna_dpt_pr'];
            $row['pengguna_dpt_pr'] = max(0, $row['pengguna_dpt_pr'] + ($row['pengguna_total_excel'] - $penggunaTotal));
            $corrections[] = "{$label}: total pengguna Excel {$row['pengguna_total_excel']} tidak sama dengan rincian {$penggunaTotal}; pengguna_dpt_pr disesuaikan {$before} -> {$row['pengguna_dpt_pr']}.";
            $penggunaTotal = $this->sumPengguna($row);
        }

        $authoritativeTotal = $row['ss_digunakan'] > 0 ? $row['ss_digunakan'] : $row['suara_total_excel'];
        if ($authoritativeTotal <= 0) {
            $authoritativeTotal = $suaraSah + $row['suara_tidak_sah'];
        }

        if ($penggunaTotal !== $authoritativeTotal) {
            $before = $row['pengguna_dpt_pr'];
            $row['pengguna_dpt_pr'] = max(0, $row['pengguna_dpt_pr'] + ($authoritativeTotal - $penggunaTotal));
            $corrections[] = "{$label}: pengguna hak pilih {$penggunaTotal} tidak sama dengan surat suara digunakan {$authoritativeTotal}; pengguna_dpt_pr disesuaikan {$before} -> {$row['pengguna_dpt_pr']}.";
        }

        $expectedTidakSah = max(0, $authoritativeTotal - $suaraSah);
        if ($row['suara_tidak_sah'] !== $expectedTidakSah) {
            $corrections[] = "{$label}: suara tidak sah {$row['suara_tidak_sah']} disesuaikan ke surat suara digunakan - suara sah {$expectedTidakSah}.";
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

    private function syncMaster(array $partais): array
    {
        $partaiIds = [];
        $calegIds = [];
        $dapilId = $this->masterDapilId();

        foreach ($partais as $nomorPartai => $partaiData) {
            $partai = RekapPartai::updateOrCreate(
                ['jenis' => static::JENIS, 'nomor_urut' => $nomorPartai, 'dapil_id' => $dapilId],
                ['nama_partai' => $partaiData['nama']]
            );
            $partaiIds[$nomorPartai] = $partai->id;
            $calegIds[$nomorPartai] = [];

            foreach ($partaiData['calegs'] as $nomorCaleg => $calegData) {
                $caleg = RekapCaleg::updateOrCreate(
                    ['partai_id' => $partai->id, 'nomor_urut' => $nomorCaleg],
                    ['nama_caleg' => $calegData['nama']]
                );
                $calegIds[$nomorPartai][$nomorCaleg] = $caleg->id;
            }

            RekapCaleg::where('partai_id', $partai->id)
                ->whereNotIn('nomor_urut', array_keys($partaiData['calegs']))
                ->delete();
        }

        $stalePartaiQuery = RekapPartai::where('jenis', static::JENIS)
            ->whereNotIn('nomor_urut', array_keys($partais));

        $dapilId === null
            ? $stalePartaiQuery->whereNull('dapil_id')
            : $stalePartaiQuery->where('dapil_id', $dapilId);

        $stalePartaiQuery->delete();

        return ['partais' => $partaiIds, 'calegs' => $calegIds];
    }

    protected function masterDapilId(): ?int
    {
        return null;
    }

    private function printReport(array $rows, array $corrections, array $missing, array $partais): void
    {
        $this->newLine();
        $this->info('Import ' . static::LABEL . ' Bangorejo selesai.');
        $this->line('Partai terbaca: ' . count($partais));
        $this->line('Caleg terbaca: ' . collect($partais)->sum(fn($partai) => count($partai['calegs'])));
        $this->line('TPS terbaca: ' . count($rows));
        $this->line('Koreksi data: ' . count($corrections));

        $summary = collect($rows)
            ->groupBy('desa')
            ->map(fn($items, $desa) => [
                'Desa' => $desa,
                'TPS' => $items->count(),
                'DPT' => $items->sum(fn($row) => $row['dpt_lk'] + $row['dpt_pr']),
                'Pengguna' => $items->sum(fn($row) => $this->sumPengguna($row)),
                'Sah' => $items->sum(fn($row) => $this->sumSah($row)),
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

    private function findDesa(string $nama): ?Desa
    {
        return Desa::query()
            ->whereRaw('LOWER(nama) = ?', [strtolower($nama)])
            ->whereHas('kecamatan', fn($query) => $query->whereRaw('LOWER(nama) = ?', [strtolower(static::KECAMATAN)]))
            ->first();
    }

    private function sumSah(array $row): int
    {
        return array_sum($row['partai_suara'])
            + collect($row['caleg_suara'])->sum(fn($calegs) => array_sum($calegs));
    }

    private function sumPengguna(array $row): int
    {
        return $row['pengguna_dpt_lk'] + $row['pengguna_dpt_pr']
            + $row['pengguna_dptb_lk'] + $row['pengguna_dptb_pr']
            + $row['pengguna_dpk_lk'] + $row['pengguna_dpk_pr'];
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

    private function cleanCandidateName(string $name): string
    {
        return preg_replace('/^\s*\d+\.\s*/', '', $name) ?? $name;
    }

    private function candidateNumberFromName(string $name): int
    {
        if (preg_match('/^\s*(\d+)\./', $name, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    private function suaraRows(Worksheet $sheet): array
    {
        for ($row = 43; $row <= 320; $row++) {
            $label = strtoupper(trim((string) $this->cell($sheet, "C{$row}")));

            if (str_contains($label, 'JUMLAH SELURUH SUARA SAH')) {
                return [
                    'sah' => $row,
                    'tidak_sah' => $row + 1,
                    'total' => $row + 2,
                ];
            }
        }

        throw new \RuntimeException('Baris suara sah/tidak sah tidak ditemukan.');
    }
}
