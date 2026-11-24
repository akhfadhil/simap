<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Tps;
use App\Models\User;
use Database\Seeders\PemiluSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QABatch6UIAndNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $komisioner;
    private User $ppk;
    private User $pps;
    private User $kpps;

    private Kecamatan $kecamatan;
    private Desa $desa;
    private Tps $tps;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PemiluSettingSeeder::class);

        $this->kecamatan = Kecamatan::create(['nama' => 'Bangorejo']);
        $this->desa = Desa::create(['nama' => 'Bangorejo', 'kecamatan_id' => $this->kecamatan->id]);
        $this->tps = Tps::create(['nama' => 'TPS 001', 'desa_id' => $this->desa->id]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'username' => 'admin_nav',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->komisioner = User::create([
            'name' => 'Komisioner KPU',
            'username' => 'komisioner_nav',
            'password' => Hash::make('password123'),
            'role' => 'komisioner',
        ]);

        $this->ppk = User::create([
            'name' => 'PPK Bangorejo',
            'username' => 'ppk_nav',
            'password' => Hash::make('password123'),
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatan->id,
        ]);

        $this->pps = User::create([
            'name' => 'PPS Bangorejo',
            'username' => 'pps_nav',
            'password' => Hash::make('password123'),
            'role' => 'pps',
            'kecamatan_id' => $this->kecamatan->id,
            'desa_id' => $this->desa->id,
        ]);

        $this->kpps = User::create([
            'name' => 'KPPS Bangorejo',
            'username' => 'kpps_nav',
            'password' => Hash::make('password123'),
            'role' => 'kpps',
            'kecamatan_id' => $this->kecamatan->id,
            'desa_id' => $this->desa->id,
            'tps_id' => $this->tps->id,
        ]);
    }

    // ==========================================
    // 1. ADMIN SPA NAVIGATION AUDIT
    // ==========================================

    public function test_nav_admin_01_dashboard(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Admin'));
    }

    public function test_nav_admin_02_kelola_pengguna(): void
    {
        $this->actingAs($this->admin)->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Users/Index'));
    }

    public function test_nav_admin_03_grafik_statistik(): void
    {
        $this->actingAs($this->admin)->get('/admin/rekap/chart')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Rekap/Chart'));
    }

    public function test_nav_admin_04_kelola_kecamatan(): void
    {
        $this->actingAs($this->admin)->get('/admin/kecamatan')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Wilayah/Kecamatan'));
    }

    public function test_nav_admin_05_kelola_desa(): void
    {
        $this->actingAs($this->admin)->get('/admin/desa')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Wilayah/Desa'));
    }

    public function test_nav_admin_06_kelola_tps(): void
    {
        $this->actingAs($this->admin)->get('/admin/tps')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Wilayah/Tps'));
    }

    public function test_nav_admin_07_rekap_dokumen(): void
    {
        $this->actingAs($this->admin)->get('/dokumen/semua')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dokumen/Admin'));
    }

    public function test_nav_admin_08_rekapitulasi_data(): void
    {
        $this->actingAs($this->admin)->get('/admin/rekap')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Rekap/Index'));
    }

    public function test_nav_admin_09_setup_pemilu(): void
    {
        $this->actingAs($this->admin)->get('/admin/setup')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Setup/Index'));
    }

    // ==========================================
    // 2. KOMISIONER SPA NAVIGATION AUDIT
    // ==========================================

    public function test_nav_komisioner_01_dashboard(): void
    {
        $this->actingAs($this->komisioner)->get('/dashboard/komisioner')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Admin'));
    }

    public function test_nav_komisioner_02_grafik_statistik(): void
    {
        $this->actingAs($this->komisioner)->get('/admin/rekap/chart')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Rekap/Chart'));
    }

    public function test_nav_komisioner_03_rekap_dokumen(): void
    {
        $this->actingAs($this->komisioner)->get('/dokumen/semua')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dokumen/Admin'));
    }

    public function test_nav_komisioner_04_rekapitulasi_data(): void
    {
        $this->actingAs($this->komisioner)->get('/admin/rekap')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Rekap/Index'));
    }

    // ==========================================
    // 3. PPK SPA NAVIGATION AUDIT
    // ==========================================

    public function test_nav_ppk_01_dashboard(): void
    {
        $this->actingAs($this->ppk)->get('/dashboard/ppk')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Ppk'));
    }

    public function test_nav_ppk_02_rekap_dokumen(): void
    {
        $this->actingAs($this->ppk)->get('/dokumen/kecamatan')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dokumen/Ppk'));
    }

    public function test_nav_ppk_03_data_pps(): void
    {
        $this->actingAs($this->ppk)->get('/ppk/data-pps')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Ppk/DataPps'));
    }

    public function test_nav_ppk_04_rekapitulasi_data(): void
    {
        $this->actingAs($this->ppk)->get('/ppk/rekap')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Rekap/Index'));
    }

    // ==========================================
    // 4. PPS SPA NAVIGATION AUDIT
    // ==========================================

    public function test_nav_pps_01_dashboard(): void
    {
        $this->actingAs($this->pps)->get('/dashboard/pps')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Pps'));
    }

    public function test_nav_pps_02_rekap_dokumen(): void
    {
        $this->actingAs($this->pps)->get('/dokumen/verifikasi')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dokumen/Pps'));
    }

    public function test_nav_pps_03_data_tps(): void
    {
        $this->actingAs($this->pps)->get('/pps/data-tps')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Pps/DataTps'));
    }

    public function test_nav_pps_04_rekapitulasi_data(): void
    {
        $this->actingAs($this->pps)->get('/pps/rekap')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Rekap/Index'));
    }

    // ==========================================
    // 5. KPPS SPA NAVIGATION AUDIT
    // ==========================================

    public function test_nav_kpps_01_dashboard(): void
    {
        $this->actingAs($this->kpps)->get('/dashboard/kpps')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Kpps'));
    }

    public function test_nav_kpps_02_rekap_suara(): void
    {
        $this->actingAs($this->kpps)->get('/rekap')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Rekap/Kpps/Index'));
    }

    public function test_nav_kpps_03_upload_dokumen(): void
    {
        $this->actingAs($this->kpps)->get('/dokumen/upload')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dokumen/Upload'));
    }

    // ==========================================
    // 6. COMMON SPA ACCOUNT & SESSION WORKFLOW
    // ==========================================

    public function test_nav_common_01_password_settings(): void
    {
        $this->actingAs($this->kpps)->get('/password')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Password/Edit'));
    }

    public function test_nav_common_02_view_as_switcher_flow(): void
    {
        // Admin views as PPK
        $viewResp = $this->actingAs($this->admin)->get(route('admin.kecamatan.view', $this->kecamatan));
        $viewResp->assertRedirect(route('dashboard.ppk'));
        $this->assertEquals($this->kecamatan->id, session('admin_view_kecamatan_id'));

        // Follow redirect to PPK dashboard
        $ppkDash = $this->actingAs($this->admin)->get(route('dashboard.ppk'));
        $ppkDash->assertOk();
        $ppkDash->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Ppk')
            ->where('isAdminView', true)
        );

        // Clear view session
        $clearResp = $this->actingAs($this->admin)->get(route('clear.view.session'));
        $clearResp->assertRedirect(route('dashboard.admin'));
        $this->assertNull(session('admin_view_kecamatan_id'));
    }
}
