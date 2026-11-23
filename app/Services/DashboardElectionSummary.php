<?php

namespace App\Services;

use App\Models\Dapil;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\PemiluSetting;
use App\Models\RekapHeader;
use App\Models\RekapPartai;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DashboardElectionSummary
{
    public function forUser(User $user): array
    {
        $scope = $this->scopeForUser($user);
        $activeJenis = PemiluSetting::aktif();

        return RekapAdminCache::rememberDashboardSummary($this->cacheParts($user, $scope, $activeJenis), function () use ($activeJenis, $scope) {
            $sections = [];

            foreach ($activeJenis as $jenis) {
                if (in_array($jenis, ['ppwp', 'gubernur', 'bupati', 'dpd'], true)) {
                    $sections[] = $this->candidateSection($jenis, $scope);

                    continue;
                }

                if (in_array($jenis, ['dpr_ri', 'dprd_prov'], true)) {
                    $sections[] = $this->calegSection($jenis, $scope);

                    continue;
                }

                if ($jenis === 'dprd_kab') {
                    foreach ($this->dprdKabSections($scope) as $section) {
                        $sections[] = $section;
                    }
                }
            }

            $filteredSections = array_values(array_filter($sections));

            $widgets = [];
            foreach ($activeJenis as $jenis) {
                if (in_array($jenis, ['ppwp', 'gubernur', 'bupati'], true)) {
                    $widgets[$jenis] = $this->candidateWidget($jenis, $scope);
                } elseif ($jenis === 'dpd') {
                    $widgets[$jenis] = $this->dpdWidget($scope);
                } elseif (in_array($jenis, ['dpr_ri', 'dprd_prov'], true)) {
                    $widgets[$jenis] = $this->legislativeWidget($jenis, $scope);
                } elseif ($jenis === 'dprd_kab') {
                    $widgets[$jenis] = $this->dprdKabGridWidget($scope);
                }
            }

            return [
                'scope_label' => $scope['label'],
                'active_jenis' => array_values($activeJenis),
                'documentsSummary' => $this->documentsSummary($scope, $activeJenis),
                'topPartiesDprdKab' => $this->topPartiesDprdKab($scope),
                'widgets' => $widgets,
                'sections' => $filteredSections,
                'summaries' => $filteredSections,
            ];
        });
    }

    private function scopeForUser(User $user): array
    {
        if (session('admin_view_tps_id')) {
            $tps = Tps::with('desa.kecamatan')->find(session('admin_view_tps_id'));

            if ($tps && $this->canAccessTps($user, $tps)) {
                return [
                    'type' => 'tps',
                    'id' => $tps->id,
                    'label' => $tps->nama.' - '.($tps->desa?->nama ?? '-'),
                    'dapil_id' => $tps->desa?->kecamatan?->dapil_id,
                ];
            }
        }

        if (session('admin_view_desa_id')) {
            $desa = Desa::with('kecamatan')->find(session('admin_view_desa_id'));

            if ($desa && $this->canAccessDesa($user, $desa)) {
                return [
                    'type' => 'desa',
                    'id' => $desa->id,
                    'label' => 'Desa '.($desa->nama ?? '-'),
                    'dapil_id' => $desa->kecamatan?->dapil_id,
                ];
            }
        }

        if (session('admin_view_kecamatan_id')) {
            $kecamatan = Kecamatan::find(session('admin_view_kecamatan_id'));

            if ($kecamatan && $this->canAccessKecamatan($user, $kecamatan)) {
                return [
                    'type' => 'kecamatan',
                    'id' => $kecamatan->id,
                    'label' => 'Kecamatan '.($kecamatan->nama ?? '-'),
                    'dapil_id' => $kecamatan->dapil_id,
                ];
            }
        }

        if ($user->role === 'ppk') {
            return [
                'type' => 'kecamatan',
                'id' => $user->kecamatan_id,
                'label' => 'Kecamatan '.($user->kecamatan?->nama ?? '-'),
                'dapil_id' => $user->kecamatan?->dapil_id,
            ];
        }

        if ($user->role === 'pps') {
            return [
                'type' => 'desa',
                'id' => $user->desa_id,
                'label' => 'Desa '.($user->desa?->nama ?? '-'),
                'dapil_id' => $user->desa?->kecamatan?->dapil_id,
            ];
        }

        if ($user->role === 'kpps') {
            return [
                'type' => 'tps',
                'id' => $user->tps_id,
                'label' => $user->tps?->nama.' - '.($user->tps?->desa?->nama ?? '-'),
                'dapil_id' => $user->tps?->desa?->kecamatan?->dapil_id,
            ];
        }

        return [
            'type' => 'kabupaten',
            'id' => null,
            'label' => 'Kabupaten Banyuwangi',
            'dapil_id' => null,
        ];
    }

    private function candidateSection(string $jenis, array $scope): array
    {
        $config = $this->candidateConfig($jenis);
        $masters = DB::table($config['master'])
            ->orderBy('nomor_urut')
            ->get(['id', 'nomor_urut', $config['label'].' as label']);

        $totals = $this->applyScope(
            DB::table($config['table'].' as s')
                ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->where('h.jenis', $jenis),
            $scope
        )
            ->select('s.calon_id', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('s.calon_id')
            ->pluck('total_suara', 'calon_id');

        $allRows = $masters
            ->map(fn ($calon) => [
                'rank' => 0,
                'label' => $calon->label,
                'meta' => 'No. '.$calon->nomor_urut,
                'suara' => (int) ($totals[$calon->id] ?? 0),
            ])
            ->sortByDesc('suara')
            ->values();
        $totalSuara = $allRows->sum('suara');
        $rows = $allRows
            ->take($jenis === 'dpd' ? 5 : $masters->count())
            ->values()
            ->map(function ($row, $index) use ($totalSuara) {
                $row['rank'] = $index + 1;
                $row['persentase'] = $totalSuara > 0 ? round(($row['suara'] / $totalSuara) * 100, 2) : 0;

                return $row;
            })
            ->toArray();

        return [
            'jenis' => $jenis,
            'title' => RekapHeader::JENIS_LABELS[$jenis] ?? strtoupper($jenis),
            'subtitle' => $jenis === 'dpd' ? '5 calon teratas' : 'Semua paslon',
            'display' => $jenis === 'dpd' ? 'top' : 'all',
            'scope' => $scope['label'],
            'total_suara' => $totalSuara,
            'rows' => $rows,
        ];
    }

    private function canAccessKecamatan(User $user, Kecamatan $kecamatan): bool
    {
        return match ($user->role) {
            'admin' => true,
            'ppk' => $user->kecamatan_id === $kecamatan->id,
            default => false,
        };
    }

    private function canAccessDesa(User $user, Desa $desa): bool
    {
        return match ($user->role) {
            'admin' => true,
            'ppk' => $user->kecamatan_id === $desa->kecamatan_id,
            'pps' => $user->desa_id === $desa->id,
            default => false,
        };
    }

    private function canAccessTps(User $user, Tps $tps): bool
    {
        return match ($user->role) {
            'admin' => true,
            'ppk' => $user->kecamatan_id === $tps->desa?->kecamatan_id,
            'pps' => $user->desa_id === $tps->desa_id,
            'kpps' => $user->tps_id === $tps->id,
            default => false,
        };
    }

    private function partySection(string $jenis, array $scope, ?int $dapilId = null, ?string $dapilName = null): array
    {
        $partais = RekapPartai::query()
            ->where('jenis', $jenis)
            ->when($jenis === 'dprd_kab' && $dapilId, fn ($query) => $query->where('dapil_id', $dapilId))
            ->orderBy('nomor_urut')
            ->get(['id', 'nomor_urut', 'nama_partai']);

        if ($partais->isEmpty()) {
            return [
                'jenis' => $jenis,
                'title' => $this->partyTitle($jenis, $dapilName),
                'subtitle' => '5 partai teratas',
                'scope' => $scope['label'],
                'rows' => [],
            ];
        }

        $partaiIds = $partais->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $totals = array_fill_keys($partaiIds, 0);

        $partaiRows = $this->applyScope(
            DB::table('rekap_partai_suaras as s')
                ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->where('h.jenis', $jenis),
            $scope
        )
            ->whereIn('s.partai_id', $partaiIds)
            ->select('s.partai_id', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('s.partai_id')
            ->get();

        foreach ($partaiRows as $row) {
            $totals[(int) $row->partai_id] += (int) $row->total_suara;
        }

        $calegRows = $this->applyScope(
            DB::table('rekap_caleg_suaras as s')
                ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->join('rekap_calegs as c', 'c.id', '=', 's.caleg_id')
                ->where('h.jenis', $jenis),
            $scope
        )
            ->whereIn('c.partai_id', $partaiIds)
            ->select('c.partai_id', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('c.partai_id')
            ->get();

        foreach ($calegRows as $row) {
            $totals[(int) $row->partai_id] += (int) $row->total_suara;
        }

        $allRows = $partais
            ->map(fn ($partai) => [
                'rank' => 0,
                'label' => $partai->nama_partai,
                'meta' => 'No. '.$partai->nomor_urut,
                'suara' => (int) ($totals[(int) $partai->id] ?? 0),
            ])
            ->sortByDesc('suara')
            ->values();
        $totalSuara = $allRows->sum('suara');
        $rows = $allRows
            ->take(5)
            ->values()
            ->map(function ($row, $index) use ($totalSuara) {
                $row['rank'] = $index + 1;
                $row['persentase'] = $totalSuara > 0 ? round(($row['suara'] / $totalSuara) * 100, 2) : 0;

                return $row;
            })
            ->toArray();

        return [
            'jenis' => $jenis,
            'title' => $this->partyTitle($jenis, $dapilName),
            'subtitle' => '5 partai teratas',
            'scope' => $scope['label'],
            'total_suara' => $totalSuara,
            'rows' => $rows,
        ];
    }

    private function dprdKabSections(array $scope): array
    {
        $dapils = Dapil::query()
            ->when($scope['dapil_id'], fn ($query) => $query->where('id', $scope['dapil_id']))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return $dapils
            ->map(fn ($dapil) => $this->calegSection('dprd_kab', $scope, (int) $dapil->id, $dapil->nama))
            ->toArray();
    }

    private function calegSection(string $jenis, array $scope, ?int $dapilId = null, ?string $dapilName = null): array
    {
        $partais = RekapPartai::with('calegs')
            ->where('jenis', $jenis)
            ->when($jenis === 'dprd_kab' && $dapilId, fn ($query) => $query->where('dapil_id', $dapilId))
            ->orderBy('nomor_urut')
            ->get();

        $calegs = $partais->flatMap(fn ($partai) => $partai->calegs->map(fn ($caleg) => [
            'id' => (int) $caleg->id,
            'label' => trim($caleg->nama_caleg.' No. '.$caleg->nomor_urut),
            'meta' => $partai->nama_partai,
        ]));

        if ($calegs->isEmpty()) {
            return [
                'jenis' => $jenis,
                'title' => $this->partyTitle($jenis, $dapilName),
                'subtitle' => '5 caleg teratas',
                'scope' => $scope['label'],
                'rows' => [],
            ];
        }

        $calegIds = $calegs->pluck('id')->values()->all();
        $totals = $this->applyScope(
            DB::table('rekap_caleg_suaras as s')
                ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->where('h.jenis', $jenis),
            $scope
        )
            ->whereIn('s.caleg_id', $calegIds)
            ->select('s.caleg_id', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('s.caleg_id')
            ->pluck('total_suara', 'caleg_id');

        $allRows = $calegs
            ->map(fn ($caleg) => [
                'rank' => 0,
                'label' => $caleg['label'],
                'meta' => $caleg['meta'],
                'suara' => (int) ($totals[$caleg['id']] ?? 0),
            ])
            ->sortByDesc('suara')
            ->values();
        $totalSuara = $allRows->sum('suara');
        $rows = $allRows
            ->take(5)
            ->values()
            ->map(function ($row, $index) use ($totalSuara) {
                $row['rank'] = $index + 1;
                $row['persentase'] = $totalSuara > 0 ? round(($row['suara'] / $totalSuara) * 100, 2) : 0;

                return $row;
            })
            ->toArray();

        return [
            'jenis' => $jenis,
            'title' => $this->partyTitle($jenis, $dapilName),
            'subtitle' => '5 caleg teratas',
            'scope' => $scope['label'],
            'total_suara' => $totalSuara,
            'rows' => $rows,
        ];
    }

    private function applyScope(Builder $query, array $scope): Builder
    {
        return match ($scope['type']) {
            'kecamatan' => $query->where('k.id', $scope['id']),
            'desa' => $query->where('d.id', $scope['id']),
            'tps' => $query->where('t.id', $scope['id']),
            default => $query,
        };
    }

    private function applyDocumentScope(Builder $query, array $scope): Builder
    {
        return match ($scope['type']) {
            'kecamatan' => $query->where(function ($q) use ($scope) {
                $q->where('k.id', $scope['id'])
                    ->orWhere('doc.kecamatan_id', $scope['id']);
            }),
            'desa' => $query->where('d.id', $scope['id']),
            'tps' => $query->where('t.id', $scope['id']),
            default => $query,
        };
    }

    private function candidateConfig(string $jenis): array
    {
        return match ($jenis) {
            'ppwp' => ['table' => 'rekap_ppwp_suaras', 'master' => 'rekap_ppwp_calons', 'label' => 'nama_paslon'],
            'gubernur' => ['table' => 'rekap_gubernur_suaras', 'master' => 'rekap_gubernur_calons', 'label' => 'nama_paslon'],
            'bupati' => ['table' => 'rekap_bupati_suaras', 'master' => 'rekap_bupati_calons', 'label' => 'nama_paslon'],
            'dpd' => ['table' => 'rekap_dpd_suaras', 'master' => 'rekap_dpd_calons', 'label' => 'nama_calon'],
        };
    }

    private function partyTitle(string $jenis, ?string $dapilName): string
    {
        if ($jenis === 'dprd_kab') {
            return 'DPRD Kab - '.($dapilName ?: 'Dapil');
        }

        return RekapHeader::JENIS_LABELS[$jenis] ?? strtoupper($jenis);
    }

    private function documentsSummary(array $scope, array $activeJenis = []): array
    {
        $totalTpsQuery = DB::table('tps as t')
            ->join('desas as d', 'd.id', '=', 't.desa_id')
            ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id');
        $totalTps = $this->applyScope($totalTpsQuery, $scope)->count('t.id');

        $activeJenisList = array_values(array_filter($activeJenis));
        $activeJenisCount = count($activeJenisList);

        $finalizedTps = 0;
        if ($activeJenisCount > 0) {
            $finalizedTpsQuery = DB::table('rekap_headers as h')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->where('h.status', 'final')
                ->whereIn('h.jenis', $activeJenisList);

            $finalizedTps = $this->applyScope($finalizedTpsQuery, $scope)
                ->select('h.tps_id')
                ->groupBy('h.tps_id')
                ->havingRaw('COUNT(DISTINCT h.jenis) >= ?', [$activeJenisCount])
                ->get()
                ->count();
        }

        $tpsPercent = $totalTps > 0 ? round(($finalizedTps / $totalTps) * 100, 1) : 0;

        $baseDocQuery = DB::table('dokumens as doc')
            ->leftJoin('tps as t', 't.id', '=', 'doc.tps_id')
            ->leftJoin('desas as d', 'd.id', '=', 't.desa_id')
            ->leftJoin('kecamatans as k', 'k.id', '=', 'd.kecamatan_id');
        $baseDocQuery = $this->applyDocumentScope($baseDocQuery, $scope);

        $verifiedCount = (clone $baseDocQuery)->where('doc.status', 'terverifikasi')->count('doc.id');
        $uploadedCount = (clone $baseDocQuery)->where('doc.status', 'menunggu_verifikasi')->count('doc.id');
        $rejectedCount = (clone $baseDocQuery)->where('doc.status', 'ditolak')->count('doc.id');

        return [
            'total_tps' => $totalTps,
            'finalized_tps' => $finalizedTps,
            'tps_percent' => $tpsPercent,
            'active_jenis_count' => $activeJenisCount,
            'verified_count' => $verifiedCount,
            'uploaded_count' => $uploadedCount,
            'rejected_count' => $rejectedCount,
        ];
    }

    private function topPartiesDprdKab(array $scope): array
    {
        $partais = RekapPartai::query()
            ->where('jenis', 'dprd_kab')
            ->get(['id', 'nomor_urut', 'nama_partai']);

        if ($partais->isEmpty()) {
            return [
                'total_suara' => 0,
                'rows' => [],
            ];
        }

        $partaiMap = [];
        foreach ($partais as $p) {
            $partaiMap[$p->nama_partai] = [
                'nomor_urut' => $p->nomor_urut,
                'nama_partai' => $p->nama_partai,
                'suara' => 0,
            ];
        }

        $partaiIds = $partais->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $partaiRows = $this->applyScope(
            DB::table('rekap_partai_suaras as s')
                ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->join('rekap_partais as p', 'p.id', '=', 's.partai_id')
                ->where('h.jenis', 'dprd_kab'),
            $scope
        )
            ->whereIn('s.partai_id', $partaiIds)
            ->select('p.nama_partai', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('p.nama_partai')
            ->get();

        foreach ($partaiRows as $row) {
            if (isset($partaiMap[$row->nama_partai])) {
                $partaiMap[$row->nama_partai]['suara'] += (int) $row->total_suara;
            }
        }

        $calegRows = $this->applyScope(
            DB::table('rekap_caleg_suaras as s')
                ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->join('rekap_calegs as c', 'c.id', '=', 's.caleg_id')
                ->join('rekap_partais as p', 'p.id', '=', 'c.partai_id')
                ->where('h.jenis', 'dprd_kab'),
            $scope
        )
            ->whereIn('c.partai_id', $partaiIds)
            ->select('p.nama_partai', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('p.nama_partai')
            ->get();

        foreach ($calegRows as $row) {
            if (isset($partaiMap[$row->nama_partai])) {
                $partaiMap[$row->nama_partai]['suara'] += (int) $row->total_suara;
            }
        }

        $sortedParties = collect($partaiMap)
            ->sortByDesc('suara')
            ->values();

        $totalSuara = $sortedParties->sum('suara');

        $rows = $sortedParties
            ->take(6)
            ->values()
            ->map(function ($row, $index) use ($totalSuara) {
                $row['rank'] = $index + 1;
                $row['persentase'] = $totalSuara > 0 ? round(($row['suara'] / $totalSuara) * 100, 2) : 0;

                return $row;
            })
            ->toArray();

        return [
            'total_suara' => $totalSuara,
            'rows' => $rows,
        ];
    }

    private function candidateWidget(string $jenis, array $scope): array
    {
        $section = $this->candidateSection($jenis, $scope);
        $winner = $section['rows'][0] ?? null;

        return [
            'jenis' => $jenis,
            'title' => $section['title'],
            'total_suara' => $section['total_suara'],
            'winner' => $winner ? [
                'nama' => $winner['label'],
                'suara' => $winner['suara'],
                'persen' => $winner['persentase'],
            ] : null,
            'rows' => $section['rows'],
        ];
    }

    private function dpdWidget(array $scope): array
    {
        $section = $this->candidateSection('dpd', $scope);
        $winner = $section['rows'][0] ?? null;
        $top4 = array_slice($section['rows'], 0, 4);
        $top5 = array_slice($section['rows'], 0, 5);

        return [
            'jenis' => 'dpd',
            'title' => 'Dewan Perwakilan Daerah (DPD)',
            'total_suara' => $section['total_suara'],
            'winner' => $winner ? [
                'nama' => $winner['label'],
                'suara' => $winner['suara'],
                'persen' => $winner['persentase'],
            ] : null,
            'top4' => $top4,
            'top5' => $top5,
            'rows' => $section['rows'] ?? [],
        ];
    }

    private function legislativeWidget(string $jenis, array $scope): array
    {
        $partais = RekapPartai::with('calegs')
            ->where('jenis', $jenis)
            ->orderBy('nomor_urut')
            ->get();

        if ($partais->isEmpty()) {
            return [
                'jenis' => $jenis,
                'title' => RekapHeader::JENIS_LABELS[$jenis] ?? strtoupper($jenis),
                'total_suara' => 0,
                'winner' => null,
                'top5_parties' => [],
            ];
        }

        $partyMap = [];
        foreach ($partais as $p) {
            $partyMap[$p->id] = [
                'id' => $p->id,
                'nama_partai' => $p->nama_partai,
                'nomor_urut' => $p->nomor_urut,
                'party_suara' => 0,
                'caleg_suara' => [],
            ];
        }

        $partaiIds = array_keys($partyMap);

        $partaiRows = $this->applyScope(
            DB::table('rekap_partai_suaras as s')
                ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->where('h.jenis', $jenis),
            $scope
        )
            ->whereIn('s.partai_id', $partaiIds)
            ->select('s.partai_id', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('s.partai_id')
            ->get();

        foreach ($partaiRows as $row) {
            if (isset($partyMap[$row->partai_id])) {
                $partyMap[$row->partai_id]['party_suara'] += (int) $row->total_suara;
            }
        }

        $calegs = $partais->flatMap(fn ($p) => $p->calegs->map(fn ($c) => [
            'id' => $c->id,
            'partai_id' => $p->id,
            'nama_caleg' => $c->nama_caleg,
            'nomor_urut' => $c->nomor_urut,
        ]));

        $calegIds = $calegs->pluck('id')->values()->all();

        $calegSuaraRows = $this->applyScope(
            DB::table('rekap_caleg_suaras as s')
                ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                ->join('tps as t', 't.id', '=', 'h.tps_id')
                ->join('desas as d', 'd.id', '=', 't.desa_id')
                ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                ->where('h.jenis', $jenis),
            $scope
        )
            ->whereIn('s.caleg_id', $calegIds)
            ->select('s.caleg_id', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('s.caleg_id')
            ->pluck('total_suara', 'caleg_id');

        $allCalegs = [];

        foreach ($calegs as $c) {
            $suara = (int) ($calegSuaraRows[$c['id']] ?? 0);
            $partyMap[$c['partai_id']]['caleg_suara'][] = [
                'nama_caleg' => $c['nama_caleg'],
                'nomor_urut' => $c['nomor_urut'],
                'suara' => $suara,
            ];
            $allCalegs[] = [
                'nama_caleg' => $c['nama_caleg'],
                'nomor_urut' => $c['nomor_urut'],
                'nama_partai' => $partyMap[$c['partai_id']]['nama_partai'] ?? '',
                'nomor_urut_partai' => $partyMap[$c['partai_id']]['nomor_urut'] ?? '',
                'suara' => $suara,
            ];
        }

        $partyList = collect($partyMap)->map(function ($p) {
            $sumCaleg = array_sum(array_column($p['caleg_suara'], 'suara'));
            $totalSuara = $p['party_suara'] + $sumCaleg;

            usort($p['caleg_suara'], fn ($a, $b) => $b['suara'] <=> $a['suara']);
            $topCaleg = $p['caleg_suara'][0] ?? null;

            return [
                'nama_partai' => $p['nama_partai'],
                'nomor_urut' => $p['nomor_urut'],
                'suara' => $totalSuara,
                'top_caleg' => $topCaleg,
            ];
        })->sortByDesc('suara')->values();

        $totalSuaraAll = $partyList->sum('suara');

        $top5 = $partyList->take(5)->map(function ($row, $idx) use ($totalSuaraAll) {
            $row['rank'] = $idx + 1;
            $row['persentase'] = $totalSuaraAll > 0 ? round(($row['suara'] / $totalSuaraAll) * 100, 2) : 0;

            return $row;
        })->toArray();

        usort($allCalegs, fn ($a, $b) => $b['suara'] <=> $a['suara']);
        $top5Calegs = collect($allCalegs)->take(5)->map(function ($row, $idx) use ($totalSuaraAll) {
            $row['rank'] = $idx + 1;
            $row['persentase'] = $totalSuaraAll > 0 ? round(($row['suara'] / $totalSuaraAll) * 100, 2) : 0;

            return $row;
        })->toArray();

        $winner = $top5[0] ?? null;

        return [
            'jenis' => $jenis,
            'title' => RekapHeader::JENIS_LABELS[$jenis] ?? strtoupper($jenis),
            'total_suara' => $totalSuaraAll,
            'winner' => $winner ? [
                'nama' => $winner['nama_partai'],
                'nomor_urut' => $winner['nomor_urut'],
                'suara' => $winner['suara'],
                'persen' => $winner['persentase'],
            ] : null,
            'top5_parties' => $top5,
            'top5_calegs' => $top5Calegs,
        ];
    }

    private function dprdKabGridWidget(array $scope): array
    {
        $dapils = Dapil::orderBy('id')->get(['id', 'nama']);

        $dapilCards = [];
        $totalSuaraKab = 0;
        $allKabCalegs = [];

        foreach ($dapils as $dapil) {
            $partais = RekapPartai::with('calegs')
                ->where('jenis', 'dprd_kab')
                ->where('dapil_id', $dapil->id)
                ->orderBy('nomor_urut')
                ->get();

            if ($partais->isEmpty()) {
                $dapilCards[] = [
                    'dapil_id' => $dapil->id,
                    'nama_dapil' => $dapil->nama,
                    'winner_party' => null,
                    'star_caleg' => null,
                    'total_suara' => 0,
                ];
                continue;
            }

            $partyMap = [];
            foreach ($partais as $p) {
                $partyMap[$p->id] = [
                    'nama_partai' => $p->nama_partai,
                    'nomor_urut' => $p->nomor_urut,
                    'party_suara' => 0,
                    'caleg_list' => [],
                ];
            }

            $partaiIds = array_keys($partyMap);

            $partaiRows = $this->applyScope(
                DB::table('rekap_partai_suaras as s')
                    ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                    ->join('tps as t', 't.id', '=', 'h.tps_id')
                    ->join('desas as d', 'd.id', '=', 't.desa_id')
                    ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                    ->where('h.jenis', 'dprd_kab'),
                $scope
            )
                ->whereIn('s.partai_id', $partaiIds)
                ->select('s.partai_id', DB::raw('SUM(s.suara) as total_suara'))
                ->groupBy('s.partai_id')
                ->get();

            foreach ($partaiRows as $row) {
                if (isset($partyMap[$row->partai_id])) {
                    $partyMap[$row->partai_id]['party_suara'] += (int) $row->total_suara;
                }
            }

            $calegs = $partais->flatMap(fn ($p) => $p->calegs->map(fn ($c) => [
                'id' => $c->id,
                'partai_id' => $p->id,
                'nama_caleg' => $c->nama_caleg,
                'nomor_urut' => $c->nomor_urut,
                'party_name' => $p->nama_partai,
                'party_nomor' => $p->nomor_urut,
            ]));

            $calegIds = $calegs->pluck('id')->values()->all();

            $calegSuaraRows = $this->applyScope(
                DB::table('rekap_caleg_suaras as s')
                    ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
                    ->join('tps as t', 't.id', '=', 'h.tps_id')
                    ->join('desas as d', 'd.id', '=', 't.desa_id')
                    ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
                    ->where('h.jenis', 'dprd_kab'),
                $scope
            )
                ->whereIn('s.caleg_id', $calegIds)
                ->select('s.caleg_id', DB::raw('SUM(s.suara) as total_suara'))
                ->groupBy('s.caleg_id')
                ->pluck('total_suara', 'caleg_id');

            $allCalegsWithSuara = [];

            foreach ($calegs as $c) {
                $suara = (int) ($calegSuaraRows[$c['id']] ?? 0);
                $partyMap[$c['partai_id']]['caleg_list'][] = [
                    'nama_caleg' => $c['nama_caleg'],
                    'nomor_urut' => $c['nomor_urut'],
                    'suara' => $suara,
                ];
                $calegItem = [
                    'nama_caleg' => $c['nama_caleg'],
                    'nomor_urut' => $c['nomor_urut'],
                    'party_name' => $c['party_name'],
                    'nama_partai' => $c['party_name'],
                    'nomor_urut_partai' => $c['party_nomor'],
                    'nama_dapil' => $dapil->nama,
                    'suara' => $suara,
                ];
                $allCalegsWithSuara[] = $calegItem;
                $allKabCalegs[] = $calegItem;
            }

            $partyList = collect($partyMap)->map(function ($p) {
                $sumCaleg = array_sum(array_column($p['caleg_list'], 'suara'));
                return [
                    'nama_partai' => $p['nama_partai'],
                    'nomor_urut' => $p['nomor_urut'],
                    'suara' => $p['party_suara'] + $sumCaleg,
                ];
            })->sortByDesc('suara')->values();

            $dapilTotalSuara = $partyList->sum('suara');
            $totalSuaraKab += $dapilTotalSuara;

            $winnerParty = $partyList->first();
            if ($winnerParty) {
                $winnerParty['persen'] = $dapilTotalSuara > 0 ? round(($winnerParty['suara'] / $dapilTotalSuara) * 100, 1) : 0;
            }

            usort($allCalegsWithSuara, fn ($a, $b) => $b['suara'] <=> $a['suara']);
            $starCaleg = $allCalegsWithSuara[0] ?? null;

            $dapilCards[] = [
                'dapil_id' => $dapil->id,
                'nama_dapil' => $dapil->nama,
                'winner_party' => $winnerParty,
                'star_caleg' => $starCaleg,
                'total_suara' => $dapilTotalSuara,
            ];
        }

        $topPartiesKab = $this->topPartiesDprdKab($scope);
        $kabWinner = $topPartiesKab['rows'][0] ?? null;

        usort($allKabCalegs, fn ($a, $b) => $b['suara'] <=> $a['suara']);
        $top5Calegs = collect($allKabCalegs)->take(5)->map(function ($row, $idx) use ($totalSuaraKab) {
            $row['rank'] = $idx + 1;
            $row['persentase'] = $totalSuaraKab > 0 ? round(($row['suara'] / $totalSuaraKab) * 100, 2) : 0;

            return $row;
        })->toArray();

        return [
            'jenis' => 'dprd_kab',
            'title' => 'DPRD Kabupaten Banyuwangi',
            'total_suara' => $totalSuaraKab,
            'winner_party' => $kabWinner ? [
                'nama' => $kabWinner['nama_partai'] ?? $kabWinner['nama'] ?? '',
                'nama_partai' => $kabWinner['nama_partai'] ?? $kabWinner['nama'] ?? '',
                'nomor_urut' => $kabWinner['nomor_urut'] ?? 0,
                'suara' => $kabWinner['suara'] ?? 0,
                'persen' => $kabWinner['persentase'] ?? $kabWinner['persen'] ?? 0,
                'persentase' => $kabWinner['persentase'] ?? $kabWinner['persen'] ?? 0,
            ] : null,
            'top5_calegs' => $top5Calegs,
            'dapils' => $dapilCards,
        ];
    }

    private function cacheParts(User $user, array $scope, array $activeJenis): array
    {
        return [
            'version' => 8,
            'user_role' => $user->role,
            'scope' => $scope,
            'active' => $activeJenis,
        ];
    }
}
