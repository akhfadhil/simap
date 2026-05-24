<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Dokumen;
use App\Models\Kecamatan;
use App\Models\PemiluSetting;
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

    private function user(string $role, array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => strtoupper($role),
            'username' => $role . '_' . uniqid(),
            'role' => $role,
            'password' => 'password',
        ], $attributes));
    }
}
