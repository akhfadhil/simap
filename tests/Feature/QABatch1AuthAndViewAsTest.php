<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\PemiluSetting;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QABatch1AuthAndViewAsTest extends TestCase
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
    private Tps $tpsA;
    private Tps $tpsB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kecamatanA = Kecamatan::create(['nama' => 'Kecamatan Test A']);
        $this->kecamatanB = Kecamatan::create(['nama' => 'Kecamatan Test B']);
        $this->desaA = Desa::create(['nama' => 'Desa Test A', 'kecamatan_id' => $this->kecamatanA->id]);
        $this->desaB = Desa::create(['nama' => 'Desa Test B', 'kecamatan_id' => $this->kecamatanB->id]);
        $this->tpsA = Tps::create(['nama' => 'TPS 01', 'desa_id' => $this->desaA->id]);
        $this->tpsB = Tps::create(['nama' => 'TPS 02', 'desa_id' => $this->desaB->id]);

        PemiluSetting::create(['jenis' => 'ppwp', 'is_active' => true]);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->komisioner = User::create([
            'name' => 'Komisioner Test',
            'username' => 'komisioner_test',
            'password' => Hash::make('password123'),
            'role' => 'komisioner',
        ]);

        $this->ppk = User::create([
            'name' => 'PPK Test',
            'username' => 'ppk_test',
            'password' => Hash::make('password123'),
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatanA->id,
        ]);

        $this->pps = User::create([
            'name' => 'PPS Test',
            'username' => 'pps_test',
            'password' => Hash::make('password123'),
            'role' => 'pps',
            'desa_id' => $this->desaA->id,
        ]);

        $this->kpps = User::create([
            'name' => 'KPPS Test',
            'username' => 'kpps_test',
            'password' => Hash::make('password123'),
            'role' => 'kpps',
            'tps_id' => $this->tpsA->id,
        ]);
    }

    // ── AUTHENTICATION TESTS (POSITIVE) ──
    public function test_TC_AUTH_01_login_page_renders_cleanly_via_inertia(): void
    {
        $response = $this->get('/');
        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_TC_AUTH_02_all_roles_can_login_and_redirect_to_respective_dashboards(): void
    {
        $roles = [
            ['username' => 'admin_test', 'dashboard' => 'dashboard.admin'],
            ['username' => 'komisioner_test', 'dashboard' => 'dashboard.komisioner'],
            ['username' => 'ppk_test', 'dashboard' => 'dashboard.ppk'],
            ['username' => 'pps_test', 'dashboard' => 'dashboard.pps'],
            ['username' => 'kpps_test', 'dashboard' => 'dashboard.kpps'],
        ];

        foreach ($roles as $r) {
            $response = $this->post(route('login.post'), [
                'username' => $r['username'],
                'password' => 'password123',
            ]);

            $response->assertRedirect(route($r['dashboard']));
            $this->assertAuthenticated();

            // Logout for next user
            $this->post(route('logout'));
            $this->assertGuest();
        }
    }

    public function test_TC_AUTH_03_already_authenticated_user_visiting_login_redirects_to_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get('/')
            ->assertRedirect(route('dashboard.admin'));
    }

    public function test_TC_AUTH_04_user_can_logout_and_session_is_invalidated(): void
    {
        $this->actingAs($this->admin);
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ── AUTHENTICATION TESTS (NEGATIVE) ──
    public function test_TC_AUTH_05_login_fails_with_wrong_password(): void
    {
        $response = $this->from('/')->post(route('login.post'), [
            'username' => 'admin_test',
            'password' => 'wrong_password_999',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_TC_AUTH_06_login_fails_with_non_existent_username(): void
    {
        $response = $this->from('/')->post(route('login.post'), [
            'username' => 'ghost_user',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_TC_AUTH_07_login_fails_when_fields_are_empty(): void
    {
        $response = $this->from('/')->post(route('login.post'), [
            'username' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }

    public function test_TC_AUTH_08_guest_cannot_access_protected_routes(): void
    {
        $this->get(route('dashboard.admin'))->assertRedirect(route('login'));
        $this->get(route('dashboard.ppk'))->assertRedirect(route('login'));
        $this->get(route('dashboard.pps'))->assertRedirect(route('login'));
        $this->get(route('dashboard.kpps'))->assertRedirect(route('login'));
        $this->get(route('password.edit'))->assertRedirect(route('login'));
    }

    // ── ACCOUNT & PASSWORD CHANGE TESTS ──
    public function test_TC_PWD_01_authenticated_user_can_view_password_change_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('password.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Password/Edit'));
    }

    public function test_TC_PWD_02_user_can_update_password_with_valid_credentials(): void
    {
        $this->actingAs($this->admin)
            ->post(route('password.update'), [
                'current_password' => 'password123',
                'password' => 'newsecret2026',
                'password_confirmation' => 'newsecret2026',
            ])
            ->assertSessionHas('success', 'Password berhasil diubah.');

        $this->assertTrue(Hash::check('newsecret2026', $this->admin->fresh()->password));
    }

    public function test_TC_PWD_03_user_cannot_update_password_with_wrong_current_password(): void
    {
        $this->actingAs($this->admin)
            ->post(route('password.update'), [
                'current_password' => 'wrong_current_password',
                'password' => 'newsecret2026',
                'password_confirmation' => 'newsecret2026',
            ])
            ->assertSessionHasErrors(['current_password']);

        $this->assertTrue(Hash::check('password123', $this->admin->fresh()->password));
    }

    public function test_TC_PWD_04_user_cannot_update_password_with_mismatched_confirmation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('password.update'), [
                'current_password' => 'password123',
                'password' => 'newsecret2026',
                'password_confirmation' => 'differentsecret',
            ])
            ->assertSessionHasErrors(['password']);

        $this->assertTrue(Hash::check('password123', $this->admin->fresh()->password));
    }

    public function test_TC_PWD_05_user_cannot_update_password_with_short_length(): void
    {
        $this->actingAs($this->admin)
            ->post(route('password.update'), [
                'current_password' => 'password123',
                'password' => '12345',
                'password_confirmation' => '12345',
            ])
            ->assertSessionHasErrors(['password']);

        $this->assertTrue(Hash::check('password123', $this->admin->fresh()->password));
    }

    // ── "VIEW AS" & TERRITORY SCOPING TESTS ──
    public function test_TC_VIEW_01_admin_can_view_as_ppk_pps_and_kpps(): void
    {
        // View as PPK
        $this->actingAs($this->admin)
            ->get(route('admin.kecamatan.view', $this->kecamatanA))
            ->assertRedirect(route('dashboard.ppk'))
            ->assertSessionHas('admin_view_kecamatan_id', $this->kecamatanA->id);

        // View as PPS
        $this->actingAs($this->admin)
            ->get(route('admin.desa.view', $this->desaA))
            ->assertRedirect(route('dashboard.pps'))
            ->assertSessionHas('admin_view_desa_id', $this->desaA->id);

        // View as KPPS
        $this->actingAs($this->admin)
            ->get(route('admin.tps.view', $this->tpsA))
            ->assertRedirect(route('dashboard.kpps'))
            ->assertSessionHas('admin_view_tps_id', $this->tpsA->id);
    }

    public function test_TC_VIEW_02_ppk_can_view_as_kpps_inside_own_kecamatan(): void
    {
        $this->actingAs($this->ppk)
            ->withSession(['admin_view_desa_id' => $this->desaA->id])
            ->get(route('pps.view-tps', $this->tpsA))
            ->assertRedirect(route('dashboard.kpps'));

        $this->actingAs($this->ppk)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->get(route('dashboard.kpps'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/Kpps')
                ->where('isAdminView', true)
                ->where('viewTps.id', $this->tpsA->id)
            );
    }

    public function test_TC_VIEW_03_ppk_cannot_view_as_kpps_outside_own_kecamatan_returns_403(): void
    {
        $this->actingAs($this->ppk)
            ->get(route('pps.view-tps', $this->tpsB))
            ->assertForbidden();

        $this->actingAs($this->ppk)
            ->withSession(['admin_view_tps_id' => $this->tpsB->id])
            ->get(route('dashboard.kpps'))
            ->assertForbidden();
    }

    public function test_TC_VIEW_04_pps_can_view_as_kpps_inside_own_desa(): void
    {
        $this->actingAs($this->pps)
            ->get(route('pps.view-tps', $this->tpsA))
            ->assertRedirect(route('dashboard.kpps'));

        $this->actingAs($this->pps)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->get(route('dashboard.kpps'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/Kpps')
                ->where('isAdminView', true)
                ->where('viewTps.id', $this->tpsA->id)
            );
    }

    public function test_TC_VIEW_05_pps_cannot_view_as_kpps_outside_own_desa_returns_403(): void
    {
        $this->actingAs($this->pps)
            ->get(route('pps.view-tps', $this->tpsB))
            ->assertForbidden();

        $this->actingAs($this->pps)
            ->withSession(['admin_view_tps_id' => $this->tpsB->id])
            ->get(route('dashboard.kpps'))
            ->assertForbidden();
    }

    public function test_TC_VIEW_06_clear_view_session_clears_all_and_redirects_properly(): void
    {
        $this->actingAs($this->admin)
            ->withSession([
                'admin_view_kecamatan_id' => $this->kecamatanA->id,
                'admin_view_desa_id' => $this->desaA->id,
                'admin_view_tps_id' => $this->tpsA->id,
            ])
            ->get(route('clear.view.session'))
            ->assertRedirect(route('dashboard.admin'))
            ->assertSessionMissing(['admin_view_kecamatan_id', 'admin_view_desa_id', 'admin_view_tps_id']);

        // Custom redirect query parameter
        $this->actingAs($this->admin)
            ->withSession(['admin_view_tps_id' => $this->tpsA->id])
            ->get(route('clear.view.session', ['redirect' => '/admin/tps']))
            ->assertRedirect('/admin/tps')
            ->assertSessionMissing('admin_view_tps_id');
    }
}
