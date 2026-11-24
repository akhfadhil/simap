<?php

namespace Tests\Feature;

use App\Models\Dapil;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\PemiluSetting;
use App\Models\RekapBupatiCalon;
use App\Models\RekapCaleg;
use App\Models\RekapCellFlag;
use App\Models\RekapDpdCalon;
use App\Models\RekapGubernurCalon;
use App\Models\RekapHeader;
use App\Models\RekapPartai;
use App\Models\RekapPpwpCalon;
use App\Models\Tps;
use App\Models\User;
use Database\Seeders\PemiluSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class QABatch5RekapSuaraTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $komisioner;
    private User $ppk;
    private User $pps;
    private User $kpps;
    private User $kpps2;

    private Kecamatan $kecamatan;
    private Desa $desa;
    private Dapil $dapil;
    private Tps $tps1;
    private Tps $tps2;

    private RekapPpwpCalon $ppwpCalon1;
    private RekapPpwpCalon $ppwpCalon2;
    private RekapGubernurCalon $gubCalon1;
    private RekapBupatiCalon $bupCalon1;
    private RekapPartai $partai1;
    private RekapCaleg $caleg1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PemiluSettingSeeder::class);

        $this->dapil = Dapil::create(['nama' => 'Dapil Banyuwangi 1']);
        $this->kecamatan = Kecamatan::create(['nama' => 'Bangorejo', 'dapil_id' => $this->dapil->id]);
        $this->desa = Desa::create(['nama' => 'Bangorejo', 'kecamatan_id' => $this->kecamatan->id]);
        $this->tps1 = Tps::create(['nama' => 'TPS 001', 'desa_id' => $this->desa->id]);
        $this->tps2 = Tps::create(['nama' => 'TPS 002', 'desa_id' => $this->desa->id]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'username' => 'admin_rekap',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->komisioner = User::create([
            'name' => 'Komisioner User',
            'username' => 'komisioner_rekap',
            'password' => Hash::make('password123'),
            'role' => 'komisioner',
        ]);

        $this->ppk = User::create([
            'name' => 'PPK Bangorejo',
            'username' => 'ppk_rekap',
            'password' => Hash::make('password123'),
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatan->id,
        ]);

        $this->pps = User::create([
            'name' => 'PPS Bangorejo',
            'username' => 'pps_rekap',
            'password' => Hash::make('password123'),
            'role' => 'pps',
            'kecamatan_id' => $this->kecamatan->id,
            'desa_id' => $this->desa->id,
        ]);

        $this->kpps = User::create([
            'name' => 'KPPS Bangorejo 1',
            'username' => 'kpps_rekap_1',
            'password' => Hash::make('password123'),
            'role' => 'kpps',
            'kecamatan_id' => $this->kecamatan->id,
            'desa_id' => $this->desa->id,
            'tps_id' => $this->tps1->id,
        ]);

        $this->kpps2 = User::create([
            'name' => 'KPPS Bangorejo 2',
            'username' => 'kpps_rekap_2',
            'password' => Hash::make('password123'),
            'role' => 'kpps',
            'kecamatan_id' => $this->kecamatan->id,
            'desa_id' => $this->desa->id,
            'tps_id' => $this->tps2->id,
        ]);

        // Candidates setup
        $this->ppwpCalon1 = RekapPpwpCalon::create(['nomor_urut' => 1, 'nama_paslon' => 'Paslon 01']);
        $this->ppwpCalon2 = RekapPpwpCalon::create(['nomor_urut' => 2, 'nama_paslon' => 'Paslon 02']);

        $this->gubCalon1 = RekapGubernurCalon::create(['nomor_urut' => 1, 'nama_paslon' => 'Gubernur 01']);
        $this->bupCalon1 = RekapBupatiCalon::create(['nomor_urut' => 1, 'nama_paslon' => 'Bupati 01']);

        $this->partai1 = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 1,
            'nama_partai' => 'Partai Kebangkitan Bangsa',
        ]);
        $this->caleg1 = $this->partai1->calegs()->create([
            'nomor_urut' => 1,
            'nama_caleg' => 'Caleg DPR RI 1',
        ]);
    }

    // ==========================================
    // 1. KPPS INDEX & FORM ACCESS
    // ==========================================

    public function test_rek_kpps_01_kpps_can_access_rekap_index(): void
    {
        $response = $this->actingAs($this->kpps)->get(route('rekap.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Rekap/Kpps/Index')
            ->where('canEditRekap', true)
            ->where('tps.id', $this->tps1->id)
            ->has('aktifJenis')
            ->has('jenisLabels')
        );
    }

    public function test_rek_kpps_02_kpps_can_access_rekap_form_for_active_election(): void
    {
        $response = $this->actingAs($this->kpps)->get(route('rekap.form', 'ppwp'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Rekap/Kpps/Form')
            ->where('jenis', 'ppwp')
            ->where('canEditRekap', true)
            ->where('isAdminRekapEdit', false)
            ->has('data.calons')
        );
    }

    public function test_rek_kpps_03_rekap_form_aborts_403_for_inactive_election(): void
    {
        PemiluSetting::where('jenis', 'dpd')->update(['is_active' => false]);

        $response = $this->actingAs($this->kpps)->get(route('rekap.form', 'dpd'));

        $response->assertForbidden();
    }

    public function test_rek_kpps_04_rekap_form_aborts_403_for_unknown_jenis(): void
    {
        $response = $this->actingAs($this->kpps)->get(route('rekap.form', 'walikota'));

        $response->assertForbidden();
    }

    // ==========================================
    // 2. 5-SECTION FORM & LIVE AUTO-CALCULATIONS
    // ==========================================

    public function test_rek_calc_01_saving_draft_stores_five_sections_and_auto_calculates(): void
    {
        $response = $this->actingAs($this->kpps)->post(route('rekap.store', 'ppwp'), [
            // Bagian I
            'dpt_lk' => 100,
            'dpt_pr' => 120,
            'pengguna_dpt_lk' => 80,
            'pengguna_dpt_pr' => 90,
            'pengguna_dptb_lk' => 2,
            'pengguna_dptb_pr' => 3,
            'pengguna_dpk_lk' => 5,
            'pengguna_dpk_pr' => 5, // Total pemilih = 80+90+2+3+5+5 = 185

            // Bagian II
            'ss_diterima' => 225,
            'ss_rusak' => 3, // auto ss_digunakan = 185; ss_sisa = 225 - 185 - 3 = 37

            // Bagian III
            'disabilitas_lk' => 1,
            'disabilitas_pr' => 2,

            // Bagian IV
            'suara' => [
                $this->ppwpCalon1->id => 100,
                $this->ppwpCalon2->id => 80, // Total suara sah = 180
            ],

            // Bagian V
            'suara_tidak_sah' => 5, // Total suara sah + tidak sah = 185 (SINKRON!)
        ]);

        $response->assertRedirect(route('rekap.index'));
        $response->assertSessionHas('success');

        $rekap = RekapHeader::where('tps_id', $this->tps1->id)->where('jenis', 'ppwp')->first();
        $this->assertNotNull($rekap);
        $this->assertEquals('draft', $rekap->status);
        $this->assertEquals(185, $rekap->ss_digunakan);
        $this->assertEquals(37, $rekap->ss_sisa);
        $this->assertEquals(100, $rekap->dpt_lk);
        $this->assertEquals(120, $rekap->dpt_pr);
        $this->assertEquals(5, $rekap->suara_tidak_sah);

        // Child suaras
        $this->assertDatabaseHas('rekap_ppwp_suaras', [
            'rekap_id' => $rekap->id,
            'calon_id' => $this->ppwpCalon1->id,
            'suara' => 100,
        ]);
        $this->assertDatabaseHas('rekap_ppwp_suaras', [
            'rekap_id' => $rekap->id,
            'calon_id' => $this->ppwpCalon2->id,
            'suara' => 80,
        ]);
    }

    public function test_rek_calc_02_legislative_election_stores_partai_and_caleg_suara(): void
    {
        $response = $this->actingAs($this->kpps)->post(route('rekap.store', 'dpr_ri'), [
            'pengguna_dpt_lk' => 50,
            'pengguna_dpt_pr' => 50,
            'ss_diterima' => 120,
            'ss_rusak' => 0,
            'suara_partai' => [
                $this->partai1->id => 20,
            ],
            'suara_caleg' => [
                $this->caleg1->id => 75,
            ],
            'suara_tidak_sah' => 5,
        ]);

        $response->assertRedirect(route('rekap.index'));
        $rekap = RekapHeader::where('tps_id', $this->tps1->id)->where('jenis', 'dpr_ri')->first();
        $this->assertNotNull($rekap);
        $this->assertEquals(100, $rekap->ss_digunakan);
        $this->assertEquals(20, $rekap->ss_sisa);

        $this->assertDatabaseHas('rekap_partai_suaras', [
            'rekap_id' => $rekap->id,
            'partai_id' => $this->partai1->id,
            'suara' => 20,
        ]);
        $this->assertDatabaseHas('rekap_caleg_suaras', [
            'rekap_id' => $rekap->id,
            'caleg_id' => $this->caleg1->id,
            'suara' => 75,
        ]);
    }

    public function test_rek_calc_03_gubernur_and_bupati_suara_stored_correctly(): void
    {
        // Gubernur
        $this->actingAs($this->kpps)->post(route('rekap.store', 'gubernur'), [
            'pengguna_dpt_lk' => 10,
            'suara' => [$this->gubCalon1->id => 10],
            'suara_tidak_sah' => 0,
        ])->assertRedirect(route('rekap.index'));

        $rekapGub = RekapHeader::where('tps_id', $this->tps1->id)->where('jenis', 'gubernur')->first();
        $this->assertDatabaseHas('rekap_gubernur_suaras', [
            'rekap_id' => $rekapGub->id,
            'calon_id' => $this->gubCalon1->id,
            'suara' => 10,
        ]);

        // Bupati
        $this->actingAs($this->kpps)->post(route('rekap.store', 'bupati'), [
            'pengguna_dpt_lk' => 10,
            'suara' => [$this->bupCalon1->id => 10],
            'suara_tidak_sah' => 0,
        ])->assertRedirect(route('rekap.index'));

        $rekapBup = RekapHeader::where('tps_id', $this->tps1->id)->where('jenis', 'bupati')->first();
        $this->assertDatabaseHas('rekap_bupati_suaras', [
            'rekap_id' => $rekapBup->id,
            'calon_id' => $this->bupCalon1->id,
            'suara' => 10,
        ]);
    }

    // ==========================================
    // 3. STATUS WORKFLOW: DRAFT VS FINALISASI & LOCK
    // ==========================================

    public function test_rek_lock_01_saving_with_finalisasi_marks_status_final(): void
    {
        $response = $this->actingAs($this->kpps)->post(route('rekap.store', 'ppwp'), [
            'pengguna_dpt_lk' => 10,
            'suara' => [$this->ppwpCalon1->id => 10],
            'suara_tidak_sah' => 0,
            'finalisasi' => '1',
        ]);

        $response->assertRedirect(route('rekap.index'));
        $rekap = RekapHeader::where('tps_id', $this->tps1->id)->where('jenis', 'ppwp')->first();
        $this->assertEquals('final', $rekap->status);
        $this->assertNotNull($rekap->difinalisasi_at);
    }

    public function test_rek_lock_02_kpps_cannot_overwrite_finalized_rekap(): void
    {
        $rekap = RekapHeader::create([
            'tps_id' => $this->tps1->id,
            'jenis' => 'ppwp',
            'status' => 'final',
            'difinalisasi_at' => now(),
            'dpt_lk' => 50,
        ]);

        $response = $this->actingAs($this->kpps)->post(route('rekap.store', 'ppwp'), [
            'dpt_lk' => 99,
        ]);

        $response->assertSessionHas('error', 'Rekap sudah difinalisasi, tidak bisa diubah.');
        $this->assertEquals(50, $rekap->fresh()->dpt_lk);
    }

    public function test_rek_lock_03_finalisasi_endpoint_marks_draft_as_final(): void
    {
        $rekap = RekapHeader::create([
            'tps_id' => $this->tps1->id,
            'jenis' => 'ppwp',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->kpps)->post(route('rekap.finalisasi', 'ppwp'));

        $response->assertRedirect(route('rekap.index'));
        $this->assertEquals('final', $rekap->fresh()->status);
        $this->assertNotNull($rekap->fresh()->difinalisasi_at);
    }

    // ==========================================
    // 4. ADMIN UNLOCK & EMERGENCY EDIT
    // ==========================================

    public function test_rek_adm_01_admin_can_unlock_finalized_rekap(): void
    {
        $rekap = RekapHeader::create([
            'tps_id' => $this->tps1->id,
            'jenis' => 'ppwp',
            'status' => 'final',
            'difinalisasi_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.rekap.unlock', 'ppwp'), [
            'tps_id' => $this->tps1->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('draft', $rekap->fresh()->status);
        $this->assertNull($rekap->fresh()->difinalisasi_at);
    }

    public function test_rek_adm_02_non_admin_cannot_unlock_rekap(): void
    {
        $rekap = RekapHeader::create([
            'tps_id' => $this->tps1->id,
            'jenis' => 'ppwp',
            'status' => 'final',
            'difinalisasi_at' => now(),
        ]);

        $this->actingAs($this->ppk)->post(route('admin.rekap.unlock', 'ppwp'), [
            'tps_id' => $this->tps1->id,
        ])->assertForbidden();

        $this->actingAs($this->pps)->post(route('admin.rekap.unlock', 'ppwp'), [
            'tps_id' => $this->tps1->id,
        ])->assertForbidden();
    }

    public function test_rek_adm_03_admin_emergency_edit_sets_session_and_redirects(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.rekap.edit-tps', [
            'jenis' => 'ppwp',
            'tps' => $this->tps1,
        ]));

        $response->assertRedirect(route('rekap.form', 'ppwp'));
        $this->assertEquals($this->tps1->id, session('admin_view_tps_id'));
    }

    public function test_rek_adm_04_admin_can_save_updates_to_finalized_rekap_directly(): void
    {
        $rekap = RekapHeader::create([
            'tps_id' => $this->tps1->id,
            'jenis' => 'ppwp',
            'status' => 'final',
            'difinalisasi_at' => now(),
            'dpt_lk' => 50,
        ]);

        // Admin enters edit mode
        $this->actingAs($this->admin)->withSession(['admin_view_tps_id' => $this->tps1->id])
            ->post(route('rekap.store', 'ppwp'), [
                'dpt_lk' => 77,
            ]);

        $this->assertEquals(77, $rekap->fresh()->dpt_lk);
        $this->assertEquals('final', $rekap->fresh()->status);
    }

    // ==========================================
    // 5. INLINE UPDATE & CELL FLAGGING
    // ==========================================

    public function test_rek_inline_01_admin_can_perform_inline_update(): void
    {
        $rekap = RekapHeader::create([
            'tps_id' => $this->tps1->id,
            'jenis' => 'ppwp',
            'status' => 'draft',
            'dpt_lk' => 50,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.rekap.inline-update', 'ppwp'), [
            'changes' => [
                [
                    'tps_id' => $this->tps1->id,
                    'row_key' => 'dpt_lk',
                    'value' => 88,
                ],
                [
                    'tps_id' => $this->tps1->id,
                    'row_key' => 'calon:'.$this->ppwpCalon1->id,
                    'value' => 120,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['updated' => 2]);
        $this->assertEquals(88, $rekap->fresh()->dpt_lk);
        $this->assertDatabaseHas('rekap_ppwp_suaras', [
            'rekap_id' => $rekap->id,
            'calon_id' => $this->ppwpCalon1->id,
            'suara' => 120,
        ]);
    }

    public function test_rek_flag_01_admin_can_toggle_cell_flag(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.rekap.cell-flag', 'ppwp'), [
            'level' => 'tps',
            'entity_id' => $this->tps1->id,
            'row_key' => 'calon:'.$this->ppwpCalon1->id,
        ]);

        $response->assertOk();
        $response->assertJson(['flagged' => true]);
        $this->assertDatabaseHas('rekap_cell_flags', [
            'jenis' => 'ppwp',
            'entity_id' => $this->tps1->id,
            'row_key' => 'calon:'.$this->ppwpCalon1->id,
        ]);

        // Toggle back off
        $responseOff = $this->actingAs($this->admin)->postJson(route('admin.rekap.cell-flag', 'ppwp'), [
            'level' => 'tps',
            'entity_id' => $this->tps1->id,
            'row_key' => 'calon:'.$this->ppwpCalon1->id,
        ]);
        $responseOff->assertOk();
        $responseOff->assertJson(['flagged' => false]);
        $this->assertDatabaseMissing('rekap_cell_flags', [
            'jenis' => 'ppwp',
            'entity_id' => $this->tps1->id,
            'row_key' => 'calon:'.$this->ppwpCalon1->id,
        ]);
    }

    public function test_rek_flag_02_non_admin_cannot_toggle_cell_flag_or_inline_update(): void
    {
        $this->actingAs($this->pps)->postJson(route('admin.rekap.cell-flag', 'ppwp'), [
            'level' => 'tps',
            'entity_id' => $this->tps1->id,
            'row_key' => 'dpt_lk',
        ])->assertForbidden();

        $this->actingAs($this->ppk)->postJson(route('admin.rekap.inline-update', 'ppwp'), [
            'changes' => [
                ['tps_id' => $this->tps1->id, 'row_key' => 'dpt_lk', 'value' => 10],
            ],
        ])->assertForbidden();
    }

    // ==========================================
    // 6. MULTI-LEVEL AGGREGATION ROLLUP
    // ==========================================

    public function test_rek_rollup_01_pps_rekap_show_aggregates_desa_data(): void
    {
        // Rekap for TPS 1
        $r1 = RekapHeader::create([
            'tps_id' => $this->tps1->id,
            'jenis' => 'ppwp',
            'status' => 'final',
            'dpt_lk' => 100,
            'ss_digunakan' => 80,
            'suara_tidak_sah' => 5,
        ]);
        $r1->ppwpSuaras()->create(['calon_id' => $this->ppwpCalon1->id, 'suara' => 50]);
        $r1->ppwpSuaras()->create(['calon_id' => $this->ppwpCalon2->id, 'suara' => 25]);

        // Rekap for TPS 2
        $r2 = RekapHeader::create([
            'tps_id' => $this->tps2->id,
            'jenis' => 'ppwp',
            'status' => 'final',
            'dpt_lk' => 150,
            'ss_digunakan' => 120,
            'suara_tidak_sah' => 10,
        ]);
        $r2->ppwpSuaras()->create(['calon_id' => $this->ppwpCalon1->id, 'suara' => 70]);
        $r2->ppwpSuaras()->create(['calon_id' => $this->ppwpCalon2->id, 'suara' => 40]);

        $response = $this->actingAs($this->pps)->get(route('pps.rekap.show', 'ppwp'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Rekap/Show')
            ->where('jenis', 'ppwp')
            ->where('scopeLevel', 'desa')
            ->has('columnList')
            ->has('masterList')
            ->has('scopeStats')
            ->has('scopeCalonTotals')
        );
    }

    public function test_rek_rollup_02_ppk_rekap_show_aggregates_kecamatan_data(): void
    {
        $response = $this->actingAs($this->ppk)->get(route('ppk.rekap.show', 'ppwp'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Rekap/Show')
            ->where('jenis', 'ppwp')
            ->where('selectedKecamatanId', $this->kecamatan->id)
            ->has('kecamatans')
            ->has('scopeStats')
        );
    }

    public function test_rek_rollup_03_admin_rekap_show_aggregates_kabupaten_data(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.rekap.show', 'ppwp'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Rekap/Show')
            ->where('jenis', 'ppwp')
            ->has('kecamatans')
            ->has('columnList')
            ->has('scopeStats')
        );
    }

    public function test_rek_chart_01_admin_chart_page_and_data_endpoint(): void
    {
        $pageResp = $this->actingAs($this->admin)->get(route('admin.rekap.chart'));
        $pageResp->assertOk();
        $pageResp->assertInertia(fn (Assert $page) => $page
            ->component('Rekap/Chart')
            ->has('aktifJenis')
        );

        $dataResp = $this->actingAs($this->admin)->getJson(route('admin.rekap.chart.data', [
            'jenis' => 'ppwp',
            'level' => 'kabupaten',
        ]));
        $dataResp->assertOk();
        $dataResp->assertJsonStructure([
            'type',
            'jenis',
            'labels',
            'candidate_rank',
            'data',
        ]);
    }

    // ==========================================
    // 7. EXPORT EXCEL ACROSS ALL LEVELS
    // ==========================================

    public function test_rek_exp_01_kpps_can_export_excel(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->kpps)->get(route('rekap.export', 'ppwp'));

        $response->assertOk();
        Excel::assertDownloaded('Rekap_PPWP_TPS_001.xlsx');
    }

    public function test_rek_exp_02_pps_can_export_excel(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->pps)->get(route('pps.rekap.export', 'ppwp'));

        $response->assertOk();
        Excel::assertDownloaded('Rekap_PPWP_Bangorejo.xlsx');
    }

    public function test_rek_exp_03_ppk_can_export_excel(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->ppk)->get(route('ppk.rekap.export', 'ppwp'));

        $response->assertOk();
        Excel::assertDownloaded('Rekap_PPWP_PPK_Bangorejo.xlsx');
    }

    public function test_rek_exp_04_admin_can_export_excel(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->admin)->get(route('admin.rekap.export', 'ppwp'));

        $response->assertOk();
        Excel::assertDownloaded('Rekap_PPWP_Admin_Semua.xlsx');
    }

    // ==========================================
    // 8. SECURITY & ROLE BOUNDARY MATRIX
    // ==========================================

    public function test_rek_sec_01_guest_redirected_to_login_for_all_rekap_routes(): void
    {
        $this->get(route('rekap.index'))->assertRedirect(route('login'));
        $this->get(route('pps.rekap.index'))->assertRedirect(route('login'));
        $this->get(route('ppk.rekap.index'))->assertRedirect(route('login'));
        $this->get(route('admin.rekap.index'))->assertRedirect(route('login'));
        $this->get(route('admin.rekap.chart'))->assertRedirect(route('login'));
    }

    public function test_rek_sec_02_pps_and_ppk_cannot_mutate_kpps_rekap_directly(): void
    {
        // PPS trying to store rekap directly
        $this->actingAs($this->pps)->post(route('rekap.store', 'ppwp'), [
            'dpt_lk' => 10,
        ])->assertForbidden();

        // PPK trying to store rekap directly
        $this->actingAs($this->ppk)->post(route('rekap.store', 'ppwp'), [
            'dpt_lk' => 10,
        ])->assertForbidden();
    }
}
