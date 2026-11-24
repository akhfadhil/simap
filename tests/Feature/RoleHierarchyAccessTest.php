<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Dokumen;
use App\Models\Kecamatan;
use App\Models\PemiluSetting;
use App\Models\RekapCaleg;
use App\Models\RekapCalegSuara;
use App\Models\RekapHeader;
use App\Models\RekapPartai;
use App\Models\RekapPartaiSuara;
use App\Models\RekapPpwpCalon;
use App\Models\RekapPpwpSuara;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleHierarchyAccessTest extends TestCase
{
    use RefreshDatabase;

    private Kecamatan $kecamatanA;

    private Kecamatan $kecamatanB;

    private Desa $desaA;

    private Desa $desaB;

    private Tps $tpsA;

    private Tps $tpsB;

    private User $admin;

    private User $komisioner;

    private User $ppkA;

    private User $ppsA;

    private User $kppsA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kecamatanA = Kecamatan::create(['nama' => 'Kecamatan A']);
        $this->kecamatanB = Kecamatan::create(['nama' => 'Kecamatan B']);
        $this->desaA = Desa::create(['nama' => 'Desa A', 'kecamatan_id' => $this->kecamatanA->id]);
        $this->desaB = Desa::create(['nama' => 'Desa B', 'kecamatan_id' => $this->kecamatanB->id]);
        $this->tpsA = Tps::create(['nama' => 'TPS A', 'desa_id' => $this->desaA->id]);
        $this->tpsB = Tps::create(['nama' => 'TPS B', 'desa_id' => $this->desaB->id]);
        PemiluSetting::create(['jenis' => 'ppwp', 'is_active' => true]);

        $this->admin = $this->user('admin');
        $this->komisioner = $this->user('komisioner');
        $this->ppkA = $this->user('ppk', ['kecamatan_id' => $this->kecamatanA->id]);
        $this->ppsA = $this->user('pps', ['desa_id' => $this->desaA->id]);
        $this->kppsA = $this->user('kpps', ['tps_id' => $this->tpsA->id]);
    }

    public function test_admin_can_enter_all_view_as_levels(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.kecamatan.view', $this->kecamatanB))
            ->assertRedirect(route('dashboard.ppk'));

        $this->actingAs($this->admin)
            ->get(route('admin.desa.view', $this->desaB))
            ->assertRedirect(route('dashboard.pps'));

        $this->actingAs($this->admin)
            ->get(route('admin.tps.view', $this->tpsB))
            ->assertRedirect(route('dashboard.kpps'));
    }

    public function test_admin_view_session_shares_territory_and_clears_properly(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.kecamatan.view', $this->kecamatanA))
            ->assertRedirect(route('dashboard.ppk'));

        $this->actingAs($this->admin)
            ->withSession(['admin_view_kecamatan_id' => $this->kecamatanA->id])
            ->get(route('dashboard.ppk'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/Ppk')
                ->where('adminViewSession.kecamatan_id', $this->kecamatanA->id)
                ->where('adminViewSession.kecamatan_nama', 'Kecamatan A')
            );

        $this->actingAs($this->admin)
            ->withSession(['admin_view_kecamatan_id' => $this->kecamatanA->id])
            ->get(route('clear.view.session'))
            ->assertRedirect(route('dashboard.admin'))
            ->assertSessionMissing('admin_view_kecamatan_id');

        $this->actingAs($this->admin)
            ->withSession(['admin_view_kecamatan_id' => $this->kecamatanA->id])
            ->get(route('clear.view.session', ['redirect' => '/admin/kecamatan']))
            ->assertRedirect('/admin/kecamatan')
            ->assertSessionMissing('admin_view_kecamatan_id');
    }

    public function test_admin_can_create_caleg_with_ajax(): void
    {
        $partai = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 1,
            'nama_partai' => 'Partai A',
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.setup.caleg.store', $partai), [
                'nomor_urut' => 1,
                'nama_caleg' => 'Caleg Ajax',
            ])
            ->assertOk()
            ->assertJsonPath('caleg.nama_caleg', 'Caleg Ajax');

        $this->assertDatabaseHas('rekap_calegs', [
            'partai_id' => $partai->id,
            'nomor_urut' => 1,
            'nama_caleg' => 'Caleg Ajax',
        ]);
    }

    public function test_ppk_can_access_only_lower_roles_inside_own_kecamatan(): void
    {
        $this->actingAs($this->ppkA)
            ->get(route('ppk.view-pps', $this->desaA))
            ->assertRedirect(route('dashboard.pps'));

        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_desa_id' => $this->desaA->id])
            ->get(route('dashboard.pps'))
            ->assertOk();

        $this->actingAs($this->ppkA)
            ->get(route('ppk.view-pps', $this->desaB))
            ->assertForbidden();

        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_desa_id' => $this->desaB->id])
            ->get(route('pps.data-tps'))
            ->assertForbidden();
    }

    public function test_ppk_can_reach_kpps_only_inside_own_kecamatan(): void
    {
        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_desa_id' => $this->desaA->id])
            ->get(route('pps.view-tps', $this->tpsA))
            ->assertRedirect(route('dashboard.kpps'));

        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->get(route('dashboard.kpps'))
            ->assertOk();

        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_tps_id' => $this->tpsB->id])
            ->get(route('rekap.index'))
            ->assertForbidden();
    }

    public function test_pps_can_access_only_kpps_inside_own_desa(): void
    {
        $this->actingAs($this->ppsA)
            ->get(route('dashboard.pps'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/Pps')
                ->where('viewDesa.id', $this->desaA->id)
                ->where('viewDesa.nama', 'Desa A')
                ->where('isAdminView', false)
            );

        $this->actingAs($this->ppsA)
            ->get(route('pps.view-tps', $this->tpsA))
            ->assertRedirect(route('dashboard.kpps'));

        $this->actingAs($this->ppsA)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->get(route('dashboard.kpps'))
            ->assertOk();

        $this->actingAs($this->ppsA)
            ->get(route('pps.view-tps', $this->tpsB))
            ->assertForbidden();

        $this->actingAs($this->ppsA)
            ->withSession(['admin_view_tps_id' => $this->tpsB->id])
            ->get(route('dokumen.upload'))
            ->assertForbidden();

        // PPS can access its own dokumen verifikasi page rendered via Inertia
        $this->actingAs($this->ppsA)
            ->get(route('dokumen.pps'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dokumen/Pps')
                ->has('tpsList')
                ->has('desa')
            );
    }

    public function test_parent_roles_cannot_mutate_lower_level_documents_or_rekap(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kppsA->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'dummy.pdf',
            'file_name' => 'dummy.pdf',
            'file_size' => 1,
        ]);

        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_desa_id' => $this->desaA->id])
            ->post(route('dokumen.verifikasi', $dokumen), ['aksi' => 'terverifikasi'])
            ->assertForbidden();

        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->post(route('dokumen.store'))
            ->assertForbidden();

        $this->actingAs($this->ppsA)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->post(route('dokumen.store'))
            ->assertForbidden();

        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->post(route('rekap.store', 'ppwp'), ['dpt_lk' => 1])
            ->assertForbidden();

        $this->actingAs($this->ppsA)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->post(route('rekap.store', 'ppwp'), ['dpt_lk' => 1])
            ->assertForbidden();

        $this->actingAs($this->ppkA)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->post(route('rekap.finalisasi', 'ppwp'))
            ->assertForbidden();

        // But PPS itself can verify document in own desa
        $this->actingAs($this->ppsA)
            ->post(route('dokumen.verifikasi', $dokumen), ['aksi' => 'terverifikasi'])
            ->assertRedirect();

        $this->assertDatabaseHas('dokumens', [
            'id' => $dokumen->id,
            'status' => 'terverifikasi',
            'verified_by' => $this->ppsA->id,
        ]);

        // And PPS can reject document with reason
        $this->actingAs($this->ppsA)
            ->post(route('dokumen.verifikasi', $dokumen), [
                'aksi' => 'ditolak',
                'komentar' => 'Foto dokumen kurang jelas',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('dokumens', [
            'id' => $dokumen->id,
            'status' => 'ditolak',
            'komentar' => 'Foto dokumen kurang jelas',
            'verified_by' => $this->ppsA->id,
        ]);
    }

    public function test_admin_can_temporarily_edit_rekap_from_admin_detail(): void
    {
        $calon = RekapPpwpCalon::create(['nomor_urut' => 1, 'nama_paslon' => 'Paslon A']);
        $rekap = RekapHeader::create([
            'tps_id' => $this->tpsA->id,
            'jenis' => 'ppwp',
            'status' => 'final',
            'dpt_lk' => 10,
            'dpt_pr' => 10,
            'pengguna_dpt_lk' => 8,
            'pengguna_dpt_pr' => 7,
            'suara_tidak_sah' => 1,
            'diinput_oleh' => $this->kppsA->id,
            'difinalisasi_at' => now(),
        ]);
        RekapPpwpSuara::create(['rekap_id' => $rekap->id, 'calon_id' => $calon->id, 'suara' => 20]);

        $this->actingAs($this->admin)
            ->get(route('admin.rekap.show', [
                'jenis' => 'ppwp',
                'detail' => 1,
                'detail_kecamatan_id' => $this->kecamatanA->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Rekap/Show')
                ->where('jenis', 'ppwp')
            );

        $this->actingAs($this->admin)
            ->from(route('admin.rekap.show', ['jenis' => 'ppwp', 'detail' => 1, 'detail_kecamatan_id' => $this->kecamatanA->id]))
            ->get(route('admin.rekap.edit-tps', ['ppwp', $this->tpsA]))
            ->assertRedirect(route('rekap.form', 'ppwp'))
            ->assertSessionHas('admin_view_tps_id', $this->tpsA->id);

        $this->actingAs($this->admin)
            ->withSession([
                'admin_view_tps_id' => $this->tpsA->id,
                'admin_rekap_return_url' => route('admin.rekap.show', 'ppwp'),
            ])
            ->post(route('rekap.store', 'ppwp'), [
                'dpt_lk' => 12,
                'suara_tidak_sah' => 2,
                'suara' => [$calon->id => 33],
            ])
            ->assertRedirect(route('admin.rekap.show', 'ppwp'));

        $this->assertDatabaseHas('rekap_headers', [
            'id' => $rekap->id,
            'status' => 'final',
            'dpt_lk' => 12,
            'suara_tidak_sah' => 2,
            'diinput_oleh' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('rekap_ppwp_suaras', [
            'rekap_id' => $rekap->id,
            'calon_id' => $calon->id,
            'suara' => 33,
        ]);
    }

    public function test_admin_can_toggle_manual_red_flag_on_tps_rekap_cell_and_roll_it_up(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.rekap.cell-flag', 'ppwp'), [
                'entity_id' => $this->tpsA->id,
                'row_key' => 'pengguna_dpk_lk',
            ])
            ->assertOk()
            ->assertJson([
                'flagged' => true,
                'tps_id' => $this->tpsA->id,
                'desa_id' => $this->desaA->id,
                'row_key' => 'pengguna_dpk_lk',
            ]);

        $this->assertDatabaseHas('rekap_cell_flags', [
            'jenis' => 'ppwp',
            'level' => 'tps',
            'entity_id' => $this->tpsA->id,
            'row_key' => 'pengguna_dpk_lk',
            'flagged_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.rekap.show', [
                'jenis' => 'ppwp',
                'detail' => 1,
                'detail_kecamatan_id' => $this->kecamatanA->id,
                'detail_desa_id' => $this->desaA->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Rekap/Show')
                ->has('tpsCellFlags')
            );

        $this->actingAs($this->ppkA)
            ->get(route('ppk.rekap.show', 'ppwp'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Rekap/Show')
                ->has('cellFlags')
            );

        $this->actingAs($this->ppkA)
            ->get(route('ppk.rekap.show', [
                'jenis' => 'ppwp',
                'detail' => 1,
                'detail_desa_id' => $this->desaA->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Rekap/Show')
                ->has('tpsCellFlags')
            );

        $this->actingAs($this->ppsA)
            ->get(route('pps.rekap.show', 'ppwp'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Rekap/Show')
                ->has('cellFlags')
                ->has('tpsCellFlags')
            );

        $this->actingAs($this->ppkA)
            ->postJson(route('admin.rekap.cell-flag', 'ppwp'), [
                'entity_id' => $this->tpsA->id,
                'row_key' => 'pengguna_dpk_lk',
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->postJson(route('admin.rekap.cell-flag', 'ppwp'), [
                'entity_id' => $this->tpsA->id,
                'row_key' => 'pengguna_dpk_lk',
            ])
            ->assertOk()
            ->assertJson([
                'flagged' => false,
                'tps_id' => $this->tpsA->id,
                'desa_id' => $this->desaA->id,
                'row_key' => 'pengguna_dpk_lk',
            ]);

        $this->assertDatabaseMissing('rekap_cell_flags', [
            'jenis' => 'ppwp',
            'level' => 'tps',
            'entity_id' => $this->tpsA->id,
            'row_key' => 'pengguna_dpk_lk',
        ]);
    }

    public function test_kpps_cannot_access_parent_or_admin_areas(): void
    {
        $this->actingAs($this->kppsA)
            ->get(route('dashboard.pps'))
            ->assertForbidden();

        $this->actingAs($this->kppsA)
            ->get(route('dashboard.ppk'))
            ->assertForbidden();

        $this->actingAs($this->kppsA)
            ->get(route('admin.kecamatan.index'))
            ->assertForbidden();
    }

    public function test_kpps_can_access_modernized_inertia_pages(): void
    {
        $this->actingAs($this->kppsA)
            ->get(route('dashboard.kpps'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard/Kpps'));

        $this->actingAs($this->kppsA)
            ->get(route('dokumen.upload'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dokumen/Upload')
                ->has('tps')
                ->has('uploaded')
            );

        $this->actingAs($this->kppsA)
            ->get(route('rekap.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Rekap/Kpps/Index')
                ->has('tps')
                ->has('rekaps')
            );

        $this->actingAs($this->kppsA)
            ->get(route('rekap.form', 'ppwp'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Rekap/Kpps/Form')
                ->has('tps')
                ->has('data')
            );
    }

    public function test_komisioner_can_only_read_admin_documents_rekap_and_charts(): void
    {
        $this->post(route('login.post'), [
            'username' => $this->komisioner->username,
            'password' => 'password',
        ])->assertRedirect(route('dashboard.komisioner'));

        $this->actingAs($this->komisioner)
            ->get(route('dashboard.komisioner'))
            ->assertOk();

        $this->actingAs($this->komisioner)
            ->get(route('dokumen.admin'))
            ->assertOk();

        $this->actingAs($this->komisioner)
            ->get(route('admin.rekap.index'))
            ->assertOk();

        $this->actingAs($this->komisioner)
            ->get(route('admin.rekap.chart'))
            ->assertOk();

        $this->actingAs($this->komisioner)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($this->komisioner)
            ->get(route('admin.kecamatan.index'))
            ->assertForbidden();

        $this->actingAs($this->komisioner)
            ->get(route('admin.setup.index'))
            ->assertForbidden();
    }

    public function test_komisioner_cannot_mutate_admin_documents_or_rekap(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kppsA->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'dummy.pdf',
            'file_name' => 'dummy.pdf',
            'file_size' => 1,
        ]);

        $this->actingAs($this->komisioner)
            ->post(route('dokumen.verifikasi.admin', $dokumen), ['aksi' => 'terverifikasi'])
            ->assertForbidden();

        $this->actingAs($this->komisioner)
            ->post(route('dokumen.restore', $dokumen))
            ->assertForbidden();

        $this->actingAs($this->komisioner)
            ->post(route('admin.tools.backup'))
            ->assertForbidden();

        $this->actingAs($this->komisioner)
            ->post(route('admin.rekap.unlock', 'ppwp'), ['tps_id' => $this->tpsA->id])
            ->assertForbidden();
    }

    public function test_admin_can_manage_admin_operator_users(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Operator Admin',
                'username' => 'operator_admin',
                'password' => 'operator123',
                'role' => 'admin',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'operator_admin',
            'role' => 'admin',
            'kecamatan_id' => null,
            'desa_id' => null,
            'tps_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'admin']))
            ->assertOk()
            ->assertSee('operator_admin');
    }

    public function test_admin_can_access_bulk_users_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.bulk', ['role' => 'ppk']))
            ->assertOk();
    }

    public function test_admin_can_bulk_store_users(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.bulk.store'), [
                'role' => 'ppk',
                'rows' => [
                    $this->kecamatanA->id => [
                        'enabled' => 1,
                        'name' => 'PPK Kecamatan A Bulk',
                        'username' => 'ppk_kec_a_bulk',
                        'password' => 'secret123',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'ppk_kec_a_bulk',
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatanA->id,
        ]);
    }

    public function test_admin_can_create_ppk_pps_kpps_with_territory_assignment(): void
    {
        // 1. Create PPK with kecamatan
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Operator PPK Test',
                'username' => 'ppk_test',
                'password' => 'ppk12345',
                'role' => 'ppk',
                'kecamatan_id' => $this->kecamatanA->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'ppk_test',
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatanA->id,
            'desa_id' => null,
            'tps_id' => null,
        ]);

        // 2. Create PPS with desa (should resolve kecamatan)
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Operator PPS Test',
                'username' => 'pps_test',
                'password' => 'pps12345',
                'role' => 'pps',
                'desa_id' => $this->desaA->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'pps_test',
            'role' => 'pps',
            'desa_id' => $this->desaA->id,
            'kecamatan_id' => $this->kecamatanA->id,
            'tps_id' => null,
        ]);

        // 3. Create KPPS with tps (should resolve desa and kecamatan)
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Operator KPPS Test',
                'username' => 'kpps_test',
                'password' => 'kpps12345',
                'role' => 'kpps',
                'tps_id' => $this->tpsA->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'kpps_test',
            'role' => 'kpps',
            'tps_id' => $this->tpsA->id,
            'desa_id' => $this->desaA->id,
            'kecamatan_id' => $this->kecamatanA->id,
        ]);
    }

    public function test_admin_can_update_user_territory_and_profile(): void
    {
        $ppk = $this->user('ppk', ['kecamatan_id' => $this->kecamatanA->id]);

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $ppk), [
                'name' => 'Updated PPK Name',
                'username' => 'ppk_updated',
                'kecamatan_id' => $this->kecamatanB->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $ppk->id,
            'name' => 'Updated PPK Name',
            'username' => 'ppk_updated',
            'kecamatan_id' => $this->kecamatanB->id,
        ]);
    }

    public function test_admin_cannot_delete_current_account(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'role' => 'admin',
        ]);
    }

    private function user(string $role, array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => strtoupper($role),
            'username' => $role.'_'.uniqid(),
            'role' => $role,
            'password' => 'password',
        ], $attributes));
    }
}
