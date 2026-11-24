<?php

namespace Tests\Feature;

use App\Models\Dapil;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\PartaiProfile;
use App\Models\PemiluSetting;
use App\Models\RekapBupatiCalon;
use App\Models\RekapCaleg;
use App\Models\RekapDpdCalon;
use App\Models\RekapGubernurCalon;
use App\Models\RekapPartai;
use App\Models\RekapPpwpCalon;
use App\Models\Tps;
use App\Models\User;
use Database\Seeders\PemiluSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QABatch3WilayahAndSetupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $komisioner;
    private User $ppk;
    private User $pps;
    private User $kpps;
    private Kecamatan $kecamatanA;
    private Kecamatan $kecamatanB;
    private Desa $desaA;
    private Desa $desaB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PemiluSettingSeeder::class);

        $this->kecamatanA = Kecamatan::create(['nama' => 'Bangorejo']);
        $this->kecamatanB = Kecamatan::create(['nama' => 'Genteng']);
        $this->desaA = Desa::create(['nama' => 'Bangorejo', 'kecamatan_id' => $this->kecamatanA->id]);
        $this->desaB = Desa::create(['nama' => 'Genteng Kulon', 'kecamatan_id' => $this->kecamatanB->id]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'username' => 'admin_wilayah',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->komisioner = User::create([
            'name' => 'Komisioner User',
            'username' => 'komisioner_wilayah',
            'password' => Hash::make('password123'),
            'role' => 'komisioner',
        ]);

        $this->ppk = User::create([
            'name' => 'PPK Bangorejo',
            'username' => 'ppk_bangorejo',
            'password' => Hash::make('password123'),
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatanA->id,
        ]);

        $this->pps = User::create([
            'name' => 'PPS Bangorejo',
            'username' => 'pps_bangorejo',
            'password' => Hash::make('password123'),
            'role' => 'pps',
            'kecamatan_id' => $this->kecamatanA->id,
            'desa_id' => $this->desaA->id,
        ]);

        $tpsInitial = Tps::create(['nama' => 'TPS 001', 'desa_id' => $this->desaA->id]);
        $this->kpps = User::create([
            'name' => 'KPPS Bangorejo',
            'username' => 'kpps_bangorejo',
            'password' => Hash::make('password123'),
            'role' => 'kpps',
            'kecamatan_id' => $this->kecamatanA->id,
            'desa_id' => $this->desaA->id,
            'tps_id' => $tpsInitial->id,
        ]);
    }

    // ==========================================
    // 1. MASTER WILAYAH: KECAMATAN
    // ==========================================

    public function test_wil_kec_01_admin_can_view_kecamatan_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kecamatan.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Wilayah/Kecamatan')
            ->has('kecamatans', 2)
            ->where('kecamatans.0.desas_count', 1)
        );
    }

    public function test_wil_kec_02_admin_can_create_new_kecamatan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kecamatan.store'), [
            'nama' => 'Gambiran',
        ]);

        $response->assertSessionHas('success', 'Kecamatan berhasil ditambahkan.');
        $this->assertDatabaseHas('kecamatans', ['nama' => 'Gambiran']);
    }

    public function test_wil_kec_03_admin_cannot_create_kecamatan_with_empty_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kecamatan.store'), [
            'nama' => '',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_wil_kec_04_admin_cannot_create_duplicate_kecamatan_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kecamatan.store'), [
            'nama' => 'Bangorejo',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_wil_kec_05_admin_cannot_create_kecamatan_exceeding_max_length(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kecamatan.store'), [
            'nama' => str_repeat('A', 101),
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_wil_kec_06_admin_can_update_kecamatan_name(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.kecamatan.update', $this->kecamatanB), [
            'nama' => 'Genteng Baru',
        ]);

        $response->assertSessionHas('success', 'Kecamatan berhasil diupdate.');
        $this->assertDatabaseHas('kecamatans', ['id' => $this->kecamatanB->id, 'nama' => 'Genteng Baru']);
    }

    public function test_wil_kec_07_admin_can_update_kecamatan_retaining_its_own_name(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.kecamatan.update', $this->kecamatanB), [
            'nama' => 'Genteng',
        ]);

        $response->assertSessionHas('success', 'Kecamatan berhasil diupdate.');
    }

    public function test_wil_kec_08_admin_cannot_update_kecamatan_to_another_existing_name(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.kecamatan.update', $this->kecamatanB), [
            'nama' => 'Bangorejo',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_wil_kec_09_admin_can_delete_kecamatan(): void
    {
        $kecamatan = Kecamatan::create(['nama' => 'Kecamatan Hapus']);

        $response = $this->actingAs($this->admin)->delete(route('admin.kecamatan.destroy', $kecamatan));

        $response->assertSessionHas('success', 'Kecamatan berhasil dihapus.');
        $this->assertDatabaseMissing('kecamatans', ['id' => $kecamatan->id]);
    }

    // ==========================================
    // 2. MASTER WILAYAH: DESA
    // ==========================================

    public function test_wil_desa_01_admin_can_view_desa_index_with_default_bangorejo(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.desa.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Wilayah/Desa')
            ->has('kecamatans', 2)
            ->has('desas', 1)
            ->where('filters.kecamatan_id', $this->kecamatanA->id)
        );
    }

    public function test_wil_desa_02_admin_can_filter_desa_by_kecamatan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.desa.index', [
            'kecamatan_id' => $this->kecamatanB->id,
        ]));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Wilayah/Desa')
            ->has('desas', 1)
            ->where('desas.0.nama', 'Genteng Kulon')
        );
    }

    public function test_wil_desa_03_admin_can_create_desa(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.desa.store'), [
            'nama' => 'Sambirejo',
            'kecamatan_id' => $this->kecamatanA->id,
        ]);

        $response->assertSessionHas('success', 'Desa berhasil ditambahkan.');
        $this->assertDatabaseHas('desas', [
            'nama' => 'Sambirejo',
            'kecamatan_id' => $this->kecamatanA->id,
        ]);
    }

    public function test_wil_desa_04_admin_cannot_create_desa_with_missing_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.desa.store'), [
            'nama' => '',
            'kecamatan_id' => null,
        ]);

        $response->assertSessionHasErrors(['nama', 'kecamatan_id']);
    }

    public function test_wil_desa_05_admin_cannot_create_desa_with_invalid_kecamatan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.desa.store'), [
            'nama' => 'Desa Fiktif',
            'kecamatan_id' => 999999,
        ]);

        $response->assertSessionHasErrors(['kecamatan_id']);
    }

    public function test_wil_desa_06_admin_can_update_desa(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.desa.update', $this->desaB), [
            'nama' => 'Genteng Wetan',
            'kecamatan_id' => $this->kecamatanB->id,
        ]);

        $response->assertSessionHas('success', 'Desa berhasil diupdate.');
        $this->assertDatabaseHas('desas', [
            'id' => $this->desaB->id,
            'nama' => 'Genteng Wetan',
        ]);
    }

    public function test_wil_desa_07_admin_can_delete_desa(): void
    {
        $desa = Desa::create(['nama' => 'Desa Hapus', 'kecamatan_id' => $this->kecamatanA->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.desa.destroy', $desa));

        $response->assertSessionHas('success', 'Desa berhasil dihapus.');
        $this->assertDatabaseMissing('desas', ['id' => $desa->id]);
    }

    // ==========================================
    // 3. MASTER WILAYAH: TPS
    // ==========================================

    public function test_wil_tps_01_admin_can_view_tps_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tps.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Wilayah/Tps')
            ->has('kecamatans')
            ->has('filteredTps')
        );
    }

    public function test_wil_tps_02_admin_can_batch_create_tps_for_desa(): void
    {
        $desaNew = Desa::create(['nama' => 'Ringintelu', 'kecamatan_id' => $this->kecamatanA->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.tps.store'), [
            'jumlah_tps' => [
                $desaNew->id => 3,
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tps', ['desa_id' => $desaNew->id, 'nama' => 'TPS 001']);
        $this->assertDatabaseHas('tps', ['desa_id' => $desaNew->id, 'nama' => 'TPS 002']);
        $this->assertDatabaseHas('tps', ['desa_id' => $desaNew->id, 'nama' => 'TPS 003']);
        $this->assertEquals(3, Tps::where('desa_id', $desaNew->id)->count());
    }

    public function test_wil_tps_03_batch_create_tps_skips_existing_tps(): void
    {
        // $this->desaA already has TPS 001 from setUp
        $response = $this->actingAs($this->admin)->post(route('admin.tps.store'), [
            'jumlah_tps' => [
                $this->desaA->id => 3,
            ],
        ]);

        $response->assertSessionHas('success');
        // Total should be 3 (TPS 001 already existed, TPS 002 & TPS 003 added)
        $this->assertEquals(3, Tps::where('desa_id', $this->desaA->id)->count());
    }

    public function test_wil_tps_04_batch_create_tps_fails_with_empty_or_zero_values(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tps.store'), [
            'jumlah_tps' => [
                $this->desaA->id => 0,
            ],
        ]);

        $response->assertSessionHasErrors(['jumlah_tps']);
    }

    public function test_wil_tps_05_admin_can_update_tps_name(): void
    {
        $tps = Tps::first();

        $response = $this->actingAs($this->admin)->put(route('admin.tps.update', $tps), [
            'nama' => 'TPS 001 Renamed',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tps', ['id' => $tps->id, 'nama' => 'TPS 001 Renamed']);
    }

    public function test_wil_tps_06_admin_cannot_update_tps_with_empty_name(): void
    {
        $tps = Tps::first();

        $response = $this->actingAs($this->admin)->put(route('admin.tps.update', $tps), [
            'nama' => '',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_wil_tps_07_admin_can_delete_tps(): void
    {
        $tps = Tps::create(['nama' => 'TPS 999', 'desa_id' => $this->desaA->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.tps.destroy', $tps));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('tps', ['id' => $tps->id]);
    }

    // ==========================================
    // 4. SETUP PEMILU: INDEX & SETTINGS TOGGLE
    // ==========================================

    public function test_set_idx_01_admin_can_view_setup_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.setup.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Setup/Index')
            ->has('pemiluSettings')
            ->has('ppwpCalons')
            ->has('gubernurCalons')
            ->has('bupatiCalons')
            ->has('dpdCalons')
            ->has('partaiDprRi')
            ->has('partaiProv')
            ->has('partaiKab')
            ->has('dapils')
            ->has('kecamatans')
            ->has('partaiProfiles')
        );
    }

    public function test_set_toggle_02_admin_can_toggle_pemilu_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.pemilu.settings'), [
            'jenis_ppwp' => '1',
            'jenis_gubernur' => '0',
            'jenis_bupati' => '1',
            'jenis_dpd' => '0',
            'jenis_dpr_ri' => '1',
            'jenis_dprd_prov' => '0',
            'jenis_dprd_kab' => '0',
        ]);

        $response->assertSessionHas('success', 'Pengaturan jenis pemilu berhasil disimpan.');
        $this->assertTrue(PemiluSetting::where('jenis', 'ppwp')->value('is_active'));
        $this->assertFalse(PemiluSetting::where('jenis', 'gubernur')->value('is_active'));
        $this->assertTrue(PemiluSetting::where('jenis', 'bupati')->value('is_active'));
        $this->assertFalse(PemiluSetting::where('jenis', 'dpd')->value('is_active'));
    }

    // ==========================================
    // 5. SETUP PEMILU: PASLON & CALON (PPWP, GUBERNUR, BUPATI, DPD)
    // ==========================================

    public function test_set_ppwp_01_admin_can_batch_store_ppwp(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.ppwp.store'), [
            'calons' => [
                ['nomor_urut' => 1, 'nama_paslon' => 'Anies - Muhaimin'],
                ['nomor_urut' => 2, 'nama_paslon' => 'Prabowo - Gibran'],
                ['nomor_urut' => 3, 'nama_paslon' => 'Ganjar - Mahfud'],
            ],
        ]);

        $response->assertSessionHas('success', 'Paslon PPWP berhasil ditambahkan.');
        $this->assertDatabaseHas('rekap_ppwp_calons', ['nomor_urut' => 1, 'nama_paslon' => 'Anies - Muhaimin']);
        $this->assertDatabaseHas('rekap_ppwp_calons', ['nomor_urut' => 2, 'nama_paslon' => 'Prabowo - Gibran']);
        $this->assertEquals(3, RekapPpwpCalon::count());
    }

    public function test_set_ppwp_02_store_ppwp_fails_on_incomplete_row(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.ppwp.store'), [
            'calons' => [
                ['nomor_urut' => 1, 'nama_paslon' => ''],
            ],
        ]);

        $response->assertSessionHasErrors(['calons']);
    }

    public function test_set_ppwp_03_store_ppwp_fails_when_all_empty(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.ppwp.store'), [
            'calons' => [
                ['nomor_urut' => '', 'nama_paslon' => ''],
            ],
        ]);

        $response->assertSessionHasErrors(['calons']);
    }

    public function test_set_ppwp_04_admin_can_delete_ppwp(): void
    {
        $calon = RekapPpwpCalon::create(['nomor_urut' => 1, 'nama_paslon' => 'Paslon Test']);

        $response = $this->actingAs($this->admin)->delete(route('admin.setup.ppwp.destroy', $calon));

        $response->assertSessionHas('success', 'Paslon dihapus.');
        $this->assertDatabaseMissing('rekap_ppwp_calons', ['id' => $calon->id]);
    }

    public function test_set_gub_01_admin_can_batch_store_and_delete_gubernur(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.gubernur.store'), [
            'calons' => [
                ['nomor_urut' => 1, 'nama_paslon' => 'Khofifah - Emil'],
                ['nomor_urut' => 2, 'nama_paslon' => 'Risma - Gus Hans'],
            ],
        ]);

        $response->assertSessionHas('success', 'Paslon Gubernur berhasil ditambahkan.');
        $calon = RekapGubernurCalon::where('nomor_urut', 1)->first();
        $this->assertNotNull($calon);

        $deleteResp = $this->actingAs($this->admin)->delete(route('admin.setup.gubernur.destroy', $calon));
        $deleteResp->assertSessionHas('success', 'Paslon Gubernur dihapus.');
        $this->assertDatabaseMissing('rekap_gubernur_calons', ['id' => $calon->id]);
    }

    public function test_set_bup_01_admin_can_batch_store_and_delete_bupati(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.bupati.store'), [
            'calons' => [
                ['nomor_urut' => 1, 'nama_paslon' => 'Ipuk - Mujiono'],
                ['nomor_urut' => 2, 'nama_paslon' => 'Ali Makki - Ali Ruchi'],
            ],
        ]);

        $response->assertSessionHas('success', 'Paslon Bupati berhasil ditambahkan.');
        $calon = RekapBupatiCalon::where('nomor_urut', 1)->first();
        $this->assertNotNull($calon);

        $deleteResp = $this->actingAs($this->admin)->delete(route('admin.setup.bupati.destroy', $calon));
        $deleteResp->assertSessionHas('success', 'Paslon Bupati dihapus.');
        $this->assertDatabaseMissing('rekap_bupati_calons', ['id' => $calon->id]);
    }

    public function test_set_dpd_01_admin_can_batch_store_and_delete_dpd(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.dpd.store'), [
            'calons' => [
                ['nomor_urut' => 1, 'nama_calon' => 'Ir. H. AA La Nyalla Mahmud Mattalitti'],
                ['nomor_urut' => 2, 'nama_calon' => 'Kondang Kusumaning Ayu'],
            ],
        ]);

        $response->assertSessionHas('success', 'Calon DPD berhasil ditambahkan.');
        $calon = RekapDpdCalon::where('nomor_urut', 1)->first();
        $this->assertNotNull($calon);

        $deleteResp = $this->actingAs($this->admin)->delete(route('admin.setup.dpd.destroy', $calon));
        $deleteResp->assertSessionHas('success', 'Calon DPD dihapus.');
        $this->assertDatabaseMissing('rekap_dpd_calons', ['id' => $calon->id]);
    }

    // ==========================================
    // 6. SETUP PEMILU: DAPIL & ASSIGNMENT
    // ==========================================

    public function test_set_dapil_01_admin_can_create_and_delete_dapil(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.dapil.store'), [
            'nama' => 'Dapil Banyuwangi 1',
        ]);

        $response->assertSessionHas('success', 'Dapil berhasil ditambahkan.');
        $dapil = Dapil::where('nama', 'Dapil Banyuwangi 1')->first();
        $this->assertNotNull($dapil);

        $delResp = $this->actingAs($this->admin)->delete(route('admin.setup.dapil.destroy', $dapil));
        $delResp->assertSessionHas('success', 'Dapil dihapus.');
        $this->assertDatabaseMissing('dapils', ['id' => $dapil->id]);
    }

    public function test_set_dapil_02_create_dapil_fails_with_empty_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.dapil.store'), [
            'nama' => '',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_set_dapil_03_admin_can_assign_and_unassign_kecamatan_to_dapil(): void
    {
        $dapil = Dapil::create(['nama' => 'Dapil 3']);

        // Assign kecamatanA to dapil
        $response = $this->actingAs($this->admin)->post(route('admin.setup.kecamatan.dapil'), [
            'kecamatan_dapil' => [
                $this->kecamatanA->id => $dapil->id,
            ],
        ]);

        $response->assertSessionHas('success', 'Dapil kecamatan berhasil diupdate.');
        $this->assertEquals($dapil->id, $this->kecamatanA->fresh()->dapil_id);

        // Unassign kecamatanA
        $responseUnassign = $this->actingAs($this->admin)->post(route('admin.setup.kecamatan.dapil'), [
            'kecamatan_dapil' => [
                $this->kecamatanA->id => null,
            ],
        ]);

        $responseUnassign->assertSessionHas('success');
        $this->assertNull($this->kecamatanA->fresh()->dapil_id);
    }

    // ==========================================
    // 7. SETUP PEMILU: PARTAI POLITIK & CALEG
    // ==========================================

    public function test_set_partai_01_admin_can_store_batch_partai_dpr_ri(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.partai.store'), [
            'jenis' => 'dpr_ri',
            'partais' => [
                ['nomor_urut' => 1, 'nama_partai' => 'PKB'],
                ['nomor_urut' => 2, 'nama_partai' => 'Gerindra'],
            ],
        ]);

        $response->assertSessionHas('success', 'Partai berhasil ditambahkan.');
        $this->assertDatabaseHas('rekap_partais', ['jenis' => 'dpr_ri', 'nomor_urut' => 1, 'nama_partai' => 'PKB']);
        $this->assertDatabaseHas('rekap_partais', ['jenis' => 'dpr_ri', 'nomor_urut' => 2, 'nama_partai' => 'Gerindra']);
    }

    public function test_set_partai_02_store_partai_dprd_kab_requires_dapil_id(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.partai.store'), [
            'jenis' => 'dprd_kab',
            'dapil_id' => null,
            'partais' => [
                ['nomor_urut' => 1, 'nama_partai' => 'PDI Perjuangan'],
            ],
        ]);

        $response->assertSessionHasErrors(['dapil_id']);
    }

    public function test_set_partai_03_admin_can_store_partai_dprd_kab_with_valid_dapil(): void
    {
        $dapil = Dapil::create(['nama' => 'Dapil 1']);

        $response = $this->actingAs($this->admin)->post(route('admin.setup.partai.store'), [
            'jenis' => 'dprd_kab',
            'dapil_id' => $dapil->id,
            'partais' => [
                ['nomor_urut' => 3, 'nama_partai' => 'PDI Perjuangan'],
            ],
        ]);

        $response->assertSessionHas('success', 'Partai berhasil ditambahkan.');
        $this->assertDatabaseHas('rekap_partais', [
            'jenis' => 'dprd_kab',
            'dapil_id' => $dapil->id,
            'nomor_urut' => 3,
            'nama_partai' => 'PDI Perjuangan',
        ]);
    }

    public function test_set_partai_04_store_partai_fails_on_incomplete_rows(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.setup.partai.store'), [
            'jenis' => 'dpr_ri',
            'partais' => [
                ['nomor_urut' => 1, 'nama_partai' => ''],
            ],
        ]);

        $response->assertSessionHasErrors(['partais']);
    }

    public function test_set_caleg_01_admin_can_add_caleg_to_partai(): void
    {
        $partai = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 1,
            'nama_partai' => 'PKB',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.setup.caleg.store', $partai), [
            'nomor_urut' => 1,
            'nama_caleg' => 'H. Syaiful Huda',
        ]);

        $response->assertSessionHas('success', 'Caleg berhasil ditambahkan.');
        $this->assertDatabaseHas('rekap_calegs', [
            'partai_id' => $partai->id,
            'nomor_urut' => 1,
            'nama_caleg' => 'H. Syaiful Huda',
        ]);
    }

    public function test_set_caleg_02_adding_caleg_supports_json_response(): void
    {
        $partai = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 2,
            'nama_partai' => 'Gerindra',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.setup.caleg.store', $partai), [
                'nomor_urut' => 1,
                'nama_caleg' => 'Sufmi Dasco Ahmad',
            ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Caleg berhasil ditambahkan.',
            'partai_id' => $partai->id,
            'caleg' => [
                'nomor_urut' => 1,
                'nama_caleg' => 'Sufmi Dasco Ahmad',
            ],
        ]);
    }

    public function test_set_caleg_03_admin_can_delete_caleg(): void
    {
        $partai = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 1,
            'nama_partai' => 'PKB',
        ]);
        $caleg = $partai->calegs()->create([
            'nomor_urut' => 2,
            'nama_caleg' => 'Caleg Dihapus',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.setup.caleg.destroy', $caleg));

        $response->assertSessionHas('success', 'Caleg dihapus.');
        $this->assertDatabaseMissing('rekap_calegs', ['id' => $caleg->id]);
    }

    public function test_set_caleg_04_delete_caleg_supports_json_response(): void
    {
        $partai = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 1,
            'nama_partai' => 'PKB',
        ]);
        $caleg = $partai->calegs()->create([
            'nomor_urut' => 3,
            'nama_caleg' => 'Caleg JSON Delete',
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.setup.caleg.destroy', $calon = $caleg));

        $response->assertOk();
        $response->assertJson(['message' => 'Caleg dihapus.']);
        $this->assertDatabaseMissing('rekap_calegs', ['id' => $caleg->id]);
    }

    public function test_set_partai_05_admin_can_delete_partai_and_its_calegs(): void
    {
        $partai = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 99,
            'nama_partai' => 'Partai Hapus',
        ]);
        $caleg = $partai->calegs()->create([
            'nomor_urut' => 1,
            'nama_caleg' => 'Caleg Terhapus',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.setup.partai.destroy', $partai));

        $response->assertSessionHas('success', 'Partai dan caleg-calegnya dihapus.');
        $this->assertDatabaseMissing('rekap_partais', ['id' => $partai->id]);
        $this->assertDatabaseMissing('rekap_calegs', ['id' => $caleg->id]);
    }

    // ==========================================
    // 8. SETUP PEMILU: PARTAI PROFILE & SYNC
    // ==========================================

    public function test_set_profile_01_admin_can_update_partai_profile_and_sync_rekap_partai(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'pdi-p',
            'nama' => 'Partai Demokrasi Indonesia Perjuangan',
            'nama_singkat' => 'PDI-P',
            'nomor_urut_aktif' => 3,
        ]);

        $rekapPartai = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 3,
            'nama_partai' => 'PDI-P',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.setup.partai-profile.update', $profile), [
            'nama' => 'Partai Demokrasi Indonesia Perjuangan',
            'nama_singkat' => 'PDIP',
            'nomor_urut_aktif' => 4,
            'warna_utama' => '#e01b22',
            'warna_aksen' => '#000000',
            'alamat_kantor' => 'Jl. Diponegoro No. 58',
            'nama_ketua' => 'Megawati Soekarnoputri',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('PDIP', $profile->fresh()->nama_singkat);
        $this->assertEquals(4, $profile->fresh()->nomor_urut_aktif);

        // RekapPartai should have synced to new nomor_urut and nama_partai
        $this->assertEquals(4, $rekapPartai->fresh()->nomor_urut);
        $this->assertEquals('PDIP', $rekapPartai->fresh()->nama_partai);
    }

    public function test_set_profile_02_update_profile_fails_with_invalid_color_format(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'golkar',
            'nama' => 'Partai Golongan Karya',
            'nama_singkat' => 'Golkar',
            'nomor_urut_aktif' => 4,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.setup.partai-profile.update', $profile), [
            'nama' => 'Partai Golongan Karya',
            'nama_singkat' => 'Golkar',
            'nomor_urut_aktif' => 4,
            'warna_utama' => 'not-a-hex',
        ]);

        $response->assertSessionHasErrors(['warna_utama']);
    }

    // ==========================================
    // 9. SECURITY & ROLE BOUNDARY ENFORCEMENT
    // ==========================================

    public function test_sec_01_komisioner_cannot_modify_wilayah_or_setup(): void
    {
        // Kecamatan modify
        $this->actingAs($this->komisioner)->post(route('admin.kecamatan.store'), ['nama' => 'Hacked Kec'])
            ->assertForbidden();

        // Desa modify
        $this->actingAs($this->komisioner)->post(route('admin.desa.store'), [
            'nama' => 'Hacked Desa',
            'kecamatan_id' => $this->kecamatanA->id,
        ])->assertForbidden();

        // TPS modify
        $this->actingAs($this->komisioner)->post(route('admin.tps.store'), [
            'jumlah_tps' => [$this->desaA->id => 5],
        ])->assertForbidden();

        // Setup modify
        $this->actingAs($this->komisioner)->post(route('admin.setup.ppwp.store'), [
            'calons' => [['nomor_urut' => 99, 'nama_paslon' => 'Hacked Paslon']],
        ])->assertForbidden();

        $this->actingAs($this->komisioner)->post(route('admin.setup.pemilu.settings'), [
            'jenis_ppwp' => 0,
        ])->assertForbidden();
    }

    public function test_sec_02_ppk_pps_kpps_cannot_access_setup_or_wilayah_admin(): void
    {
        // PPK
        $this->actingAs($this->ppk)->get(route('admin.kecamatan.index'))->assertForbidden();
        $this->actingAs($this->ppk)->get(route('admin.setup.index'))->assertForbidden();

        // PPS
        $this->actingAs($this->pps)->get(route('admin.desa.index'))->assertForbidden();
        $this->actingAs($this->pps)->get(route('admin.setup.index'))->assertForbidden();

        // KPPS
        $this->actingAs($this->kpps)->get(route('admin.tps.index'))->assertForbidden();
        $this->actingAs($this->kpps)->get(route('admin.setup.index'))->assertForbidden();
    }

    public function test_sec_03_guest_redirected_to_login(): void
    {
        $this->get(route('admin.kecamatan.index'))->assertRedirect(route('login'));
        $this->get(route('admin.desa.index'))->assertRedirect(route('login'));
        $this->get(route('admin.tps.index'))->assertRedirect(route('login'));
        $this->get(route('admin.setup.index'))->assertRedirect(route('login'));
        $this->post(route('admin.setup.pemilu.settings'))->assertRedirect(route('login'));
    }
}
