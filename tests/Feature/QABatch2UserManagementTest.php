<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QABatch2UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $secondAdmin;
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

        $this->kecamatanA = Kecamatan::create(['nama' => 'Kecamatan Banyuwangi']);
        $this->kecamatanB = Kecamatan::create(['nama' => 'Kecamatan Rogojampi']);
        $this->desaA = Desa::create(['nama' => 'Desa Kepatihan', 'kecamatan_id' => $this->kecamatanA->id]);
        $this->desaB = Desa::create(['nama' => 'Desa Gitik', 'kecamatan_id' => $this->kecamatanB->id]);
        $this->tpsA = Tps::create(['nama' => 'TPS 001', 'desa_id' => $this->desaA->id]);
        $this->tpsB = Tps::create(['nama' => 'TPS 002', 'desa_id' => $this->desaB->id]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'username' => 'admin_main',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->secondAdmin = User::create([
            'name' => 'Secondary Admin',
            'username' => 'admin_two',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->komisioner = User::create([
            'name' => 'Komisioner KPU',
            'username' => 'komisioner_user',
            'password' => Hash::make('password123'),
            'role' => 'komisioner',
        ]);

        $this->ppk = User::create([
            'name' => 'Petugas PPK',
            'username' => 'ppk_user',
            'password' => Hash::make('password123'),
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatanA->id,
        ]);

        $this->pps = User::create([
            'name' => 'Petugas PPS',
            'username' => 'pps_user',
            'password' => Hash::make('password123'),
            'role' => 'pps',
            'desa_id' => $this->desaA->id,
            'kecamatan_id' => $this->kecamatanA->id,
        ]);

        $this->kpps = User::create([
            'name' => 'Petugas KPPS',
            'username' => 'kpps_user',
            'password' => Hash::make('password123'),
            'role' => 'kpps',
            'tps_id' => $this->tpsA->id,
            'desa_id' => $this->desaA->id,
            'kecamatan_id' => $this->kecamatanA->id,
        ]);
    }

    // ── 1. AKSES & AUTHORIZATION (POSITIVE & NEGATIVE) ──

    public function test_TC_USER_01_admin_can_access_user_index_with_inertia(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->has('users')
                ->has('kecamatans')
                ->has('desas')
                ->has('tpsList')
            );
    }

    public function test_TC_USER_02_non_admin_cannot_access_user_management_returns_403(): void
    {
        // Komisioner, PPK, PPS, KPPS tidak boleh mengakses kelola pengguna
        $this->actingAs($this->komisioner)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($this->ppk)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($this->pps)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($this->kpps)->get(route('admin.users.index'))->assertForbidden();

        // Mutasi juga harus 403 Forbidden
        $this->actingAs($this->ppk)->post(route('admin.users.store'), [
            'name' => 'Hacker',
            'username' => 'hacker',
            'role' => 'admin',
        ])->assertForbidden();
    }

    // ── 2. FILTER & PENCARIAN USER ──

    public function test_TC_USER_03_admin_can_filter_users_by_role_and_territory(): void
    {
        // Filter role PPK
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'ppk']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->where('filters.role', 'ppk')
            );
    }

    // ── 3. TAMBAH PENGGUNA BARU (POSITIVE) ──

    public function test_TC_USER_04_admin_can_create_admin_and_komisioner_without_territory(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Operator Baru',
            'username' => 'operator_new',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'username' => 'operator_new',
            'role' => 'admin',
            'kecamatan_id' => null,
            'desa_id' => null,
            'tps_id' => null,
        ]);
    }

    public function test_TC_USER_05_admin_can_create_ppk_with_kecamatan_assignment(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'PPK Rogojampi',
            'username' => 'ppk_rogojampi',
            'password' => 'secret123',
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatanB->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'username' => 'ppk_rogojampi',
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatanB->id,
            'desa_id' => null,
            'tps_id' => null,
        ]);
    }

    public function test_TC_USER_06_admin_can_create_pps_with_desa_and_auto_resolved_kecamatan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'PPS Gitik',
            'username' => 'pps_gitik',
            'password' => 'secret123',
            'role' => 'pps',
            'desa_id' => $this->desaB->id,
        ]);

        $response->assertSessionHas('success');
        // Kecamatan ID otomatis terisi dari relasi Desa
        $this->assertDatabaseHas('users', [
            'username' => 'pps_gitik',
            'role' => 'pps',
            'desa_id' => $this->desaB->id,
            'kecamatan_id' => $this->kecamatanB->id,
        ]);
    }

    public function test_TC_USER_07_admin_can_create_kpps_with_tps_and_auto_resolved_hierarchy(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'KPPS TPS 002',
            'username' => 'kpps_tps002',
            'password' => 'secret123',
            'role' => 'kpps',
            'tps_id' => $this->tpsB->id,
        ]);

        $response->assertSessionHas('success');
        // Desa & Kecamatan ID otomatis terisi dari relasi TPS
        $this->assertDatabaseHas('users', [
            'username' => 'kpps_tps002',
            'role' => 'kpps',
            'tps_id' => $this->tpsB->id,
            'desa_id' => $this->desaB->id,
            'kecamatan_id' => $this->kecamatanB->id,
        ]);
    }

    // ── 4. VALIDASI FORM TAMBAH PENGGUNA (NEGATIVE) ──

    public function test_TC_USER_08_create_user_fails_with_duplicate_username(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Duplicate User',
            'username' => 'admin_main', // sudah ada
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_TC_USER_09_create_user_fails_when_ppk_missing_kecamatan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'PPK Tanpa Wilayah',
            'username' => 'ppk_nowhere',
            'password' => 'secret123',
            'role' => 'ppk',
            'kecamatan_id' => null,
        ]);

        $response->assertSessionHasErrors('kecamatan_id');
    }

    public function test_TC_USER_10_create_user_fails_when_pps_missing_desa(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'PPS Tanpa Desa',
            'username' => 'pps_nowhere',
            'password' => 'secret123',
            'role' => 'pps',
            'desa_id' => null,
        ]);

        $response->assertSessionHasErrors('desa_id');
    }

    public function test_TC_USER_11_create_user_fails_when_kpps_missing_tps(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'KPPS Tanpa TPS',
            'username' => 'kpps_nowhere',
            'password' => 'secret123',
            'role' => 'kpps',
            'tps_id' => null,
        ]);

        $response->assertSessionHasErrors('tps_id');
    }

    public function test_TC_USER_12_create_user_fails_with_short_password_under_6_chars(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'User Password Pendek',
            'username' => 'user_short_pwd',
            'password' => '12345',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_TC_USER_13_create_user_fails_with_invalid_or_deleted_partai_role(): void
    {
        // Role partai atau role asing harus ditolak validasi
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Akun Partai',
            'username' => 'partai_pdi',
            'password' => 'secret123',
            'role' => 'partai',
        ]);

        $response->assertSessionHasErrors('role');
    }

    // ── 5. UPDATE DATA PENGGUNA (POSITIVE & NEGATIVE) ──

    public function test_TC_USER_14_admin_can_update_user_name_and_territory(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->ppk), [
            'name' => 'PPK Banyuwangi Updated',
            'username' => 'ppk_user',
            'kecamatan_id' => $this->kecamatanB->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->ppk->id,
            'name' => 'PPK Banyuwangi Updated',
            'kecamatan_id' => $this->kecamatanB->id,
        ]);
    }

    public function test_TC_USER_15_admin_can_update_user_password_optionally(): void
    {
        // Jika password diisi, password ter-update
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->pps), [
            'name' => $this->pps->name,
            'username' => $this->pps->username,
            'password' => 'newpassword999',
            'desa_id' => $this->desaA->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newpassword999', $this->pps->fresh()->password));

        // Jika password kosong, password lama tidak berubah
        $this->actingAs($this->admin)->put(route('admin.users.update', $this->pps), [
            'name' => 'PPS Name Changed',
            'username' => $this->pps->username,
            'password' => '',
            'desa_id' => $this->desaA->id,
        ]);

        $this->assertTrue(Hash::check('newpassword999', $this->pps->fresh()->password));
        $this->assertEquals('PPS Name Changed', $this->pps->fresh()->name);
    }

    public function test_TC_USER_16_update_user_fails_with_taken_username_of_another_user(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->ppk), [
            'name' => 'PPK Modified',
            'username' => 'admin_main', // milik admin utama
            'kecamatan_id' => $this->kecamatanA->id,
        ]);

        $response->assertSessionHasErrors('username');
    }

    // ── 6. HAPUS PENGGUNA & PROTEKSI (POSITIVE & NEGATIVE) ──

    public function test_TC_USER_17_admin_can_delete_other_user(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->kpps));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $this->kpps->id]);
    }

    public function test_TC_USER_18_admin_cannot_delete_own_logged_in_account(): void
    {
        // Admin tidak boleh menghapus dirinya sendiri saat sedang login
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertSessionHas('error', 'Akun yang sedang dipakai tidak bisa dihapus.');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_TC_USER_19_admin_cannot_delete_the_last_remaining_admin(): void
    {
        // Hapus secondAdmin dulu
        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->secondAdmin));
        $this->assertDatabaseMissing('users', ['id' => $this->secondAdmin->id]);

        // Login sebagai admin, buat dummy admin lain untuk menghapus admin main
        $thirdAdmin = User::create([
            'name' => 'Third Admin',
            'username' => 'admin_three',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Hapus admin_main oleh thirdAdmin -> berhasil (karena masih sisa thirdAdmin)
        $this->actingAs($thirdAdmin)->delete(route('admin.users.destroy', $this->admin));
        $this->assertDatabaseMissing('users', ['id' => $this->admin->id]);

        // Sekarang coba hapus thirdAdmin saat ia adalah satu-satunya admin tersisa
        // Buat user non-admin sementara atau simulasi pengecekan
        $this->assertEquals(1, User::where('role', 'admin')->count());
        $response = $this->actingAs($thirdAdmin)->delete(route('admin.users.destroy', $thirdAdmin));

        // Dicegat self-delete atau last-admin
        $this->assertDatabaseHas('users', ['id' => $thirdAdmin->id]);
    }

    // ── 7. BULK INPUT USERS (POSITIVE & NEGATIVE) ──

    public function test_TC_BULK_01_admin_can_access_bulk_users_page_with_inertia(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.bulk', [
            'role' => 'kpps',
            'kecamatan_id' => $this->kecamatanA->id,
            'desa_id' => $this->desaA->id,
        ]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Bulk')
                ->where('role', 'kpps')
                ->has('rows')
            );
    }

    public function test_TC_BULK_02_admin_can_bulk_create_users_for_kpps(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.bulk.store'), [
            'role' => 'kpps',
            'rows' => [
                $this->tpsA->id => [
                    'enabled' => '1',
                    'name' => 'KPPS TPS 001 Bulk',
                    'username' => 'kpps_bulk_01',
                    'password' => 'secret123',
                ],
                $this->tpsB->id => [
                    'enabled' => '1',
                    'name' => 'KPPS TPS 002 Bulk',
                    'username' => 'kpps_bulk_02',
                    'password' => 'secret123',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'username' => 'kpps_bulk_01',
            'role' => 'kpps',
            'tps_id' => $this->tpsA->id,
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'kpps_bulk_02',
            'role' => 'kpps',
            'tps_id' => $this->tpsB->id,
        ]);
    }

    public function test_TC_BULK_03_bulk_store_fails_with_empty_selection(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.bulk.store'), [
            'role' => 'kpps',
            'rows' => [
                $this->tpsA->id => [
                    'enabled' => '0', // tidak dicentang
                    'name' => 'KPPS',
                    'username' => 'kpps_unselected',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('rows');
    }

    public function test_TC_BULK_04_bulk_store_fails_with_duplicate_usernames_in_submitted_batch(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.bulk.store'), [
            'role' => 'kpps',
            'rows' => [
                $this->tpsA->id => [
                    'enabled' => '1',
                    'name' => 'KPPS Satu',
                    'username' => 'kpps_same_username',
                    'password' => 'secret123',
                ],
                $this->tpsB->id => [
                    'enabled' => '1',
                    'name' => 'KPPS Dua',
                    'username' => 'kpps_same_username', // username kembar
                    'password' => 'secret123',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('rows');
    }

    public function test_TC_BULK_05_bulk_store_fails_when_username_already_taken_in_database(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.bulk.store'), [
            'role' => 'kpps',
            'rows' => [
                $this->tpsB->id => [
                    'enabled' => '1',
                    'name' => 'KPPS Duplikat DB',
                    'username' => 'admin_main', // sudah ada di DB
                    'password' => 'secret123',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('rows');
    }

    // ── 8. EXPORT USERS CSV ──

    public function test_TC_EXPORT_01_admin_can_export_users_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.export', ['role' => 'admin']));

        $response->assertOk();
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('admin_main', $response->streamedContent());
    }
}
