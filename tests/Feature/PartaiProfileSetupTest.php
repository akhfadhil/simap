<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PartaiProfile;
use App\Models\RekapPartai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PartaiProfileSetupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $nonAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'role' => 'admin',
            'password' => 'password',
        ]);

        $this->nonAdmin = User::create([
            'name' => 'Komisioner',
            'username' => 'komisioner',
            'role' => 'komisioner',
            'password' => 'password',
        ]);
    }

    public function test_admin_can_access_setup_page_and_see_profiles(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'pkb',
            'nama' => 'Partai Kebangkitan Bangsa',
            'nama_singkat' => 'PKB',
            'nomor_urut_aktif' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.setup.index'));

        $response->assertOk();
        $response->assertSee('Setup Profil');
        $response->assertSee('Nomor Urut Partai');
        $response->assertSee('Partai Kebangkitan Bangsa');
    }

    public function test_non_admin_cannot_access_setup_page(): void
    {
        $response = $this->actingAs($this->nonAdmin)
            ->get(route('admin.setup.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_update_partai_profile_and_sync_rekap_partai(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'pkb',
            'nama' => 'Partai Kebangkitan Bangsa',
            'nama_singkat' => 'PKB',
            'nomor_urut_aktif' => 1,
            'nomor_urut_historis_json' => [2024 => 1],
        ]);

        // Create matching RekapPartai records that should be updated
        $rekapDpr = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 1,
            'nama_partai' => 'PKB',
        ]);

        $rekapProv = RekapPartai::create([
            'jenis' => 'dprd_prov',
            'nomor_urut' => 1,
            'nama_partai' => 'PKB',
        ]);

        $fakeLogo = UploadedFile::fake()->image('new-logo.png');

        $response = $this->actingAs($this->admin)
            ->put(route('admin.setup.partai-profile.update', $profile), [
                'nama' => 'Partai Kebangkitan Bangsa Perubahan',
                'nama_singkat' => 'PKB Baru',
                'nomor_urut_aktif' => 99,
                'warna_utama' => '#00ff00',
                'warna_aksen' => '#008800',
                'logo' => $fakeLogo,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert PartaiProfile is updated
        $profile->refresh();
        $this->assertEquals('Partai Kebangkitan Bangsa Perubahan', $profile->nama);
        $this->assertEquals('PKB Baru', $profile->nama_singkat);
        $this->assertEquals(99, $profile->nomor_urut_aktif);
        $this->assertEquals('#00ff00', $profile->warna_utama);
        $this->assertEquals('#008800', $profile->warna_aksen);
        $this->assertStringContainsString('logo-pkb-', $profile->logo_path);

        // Assert history JSON is updated
        $currentYear = date('Y');
        $this->assertEquals(99, $profile->nomor_urut_historis_json[$currentYear]);
        $this->assertEquals(1, $profile->nomor_urut_historis_json[2024]);

        // Assert RekapPartai records are synchronized
        $rekapDpr->refresh();
        $rekapProv->refresh();

        $this->assertEquals(99, $rekapDpr->nomor_urut);
        $this->assertEquals('PKB Baru', $rekapDpr->nama_partai);
        
        $this->assertEquals(99, $rekapProv->nomor_urut);
        $this->assertEquals('PKB Baru', $rekapProv->nama_partai);

        // Clean up the uploaded fake file from public dir if needed, but in testing it goes to public_path()
        $uploadedPath = public_path($profile->logo_path);
        if (file_exists($uploadedPath)) {
            unlink($uploadedPath);
        }
    }

    public function test_non_admin_cannot_update_partai_profile(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'pkb',
            'nama' => 'Partai Kebangkitan Bangsa',
            'nama_singkat' => 'PKB',
            'nomor_urut_aktif' => 1,
        ]);

        $response = $this->actingAs($this->nonAdmin)
            ->put(route('admin.setup.partai-profile.update', $profile), [
                'nama' => 'Partai Kebangkitan Bangsa Perubahan',
                'nama_singkat' => 'PKB Baru',
                'nomor_urut_aktif' => 99,
            ]);

        $response->assertForbidden();
    }

    public function test_update_partai_profile_validation(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'pkb',
            'nama' => 'Partai Kebangkitan Bangsa',
            'nama_singkat' => 'PKB',
            'nomor_urut_aktif' => 1,
        ]);

        // Test missing fields
        $response = $this->actingAs($this->admin)
            ->put(route('admin.setup.partai-profile.update', $profile), [
                'nama' => '',
                'nama_singkat' => '',
                'nomor_urut_aktif' => '',
            ]);

        $response->assertSessionHasErrors(['nama', 'nama_singkat', 'nomor_urut_aktif']);

        // Test invalid hex colors
        $response = $this->actingAs($this->admin)
            ->put(route('admin.setup.partai-profile.update', $profile), [
                'nama' => 'Partai Kebangkitan Bangsa',
                'nama_singkat' => 'PKB',
                'nomor_urut_aktif' => 1,
                'warna_utama' => 'invalid-color',
                'warna_aksen' => 'blue',
            ]);

        $response->assertSessionHasErrors(['warna_utama', 'warna_aksen']);

        // Test invalid logo file
        $response = $this->actingAs($this->admin)
            ->put(route('admin.setup.partai-profile.update', $profile), [
                'nama' => 'Partai Kebangkitan Bangsa',
                'nama_singkat' => 'PKB',
                'nomor_urut_aktif' => 1,
                'logo' => 'not-an-image-file',
            ]);

        $response->assertSessionHasErrors(['logo']);
    }

    public function test_admin_can_update_partai_profile_via_ajax(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'pkb',
            'nama' => 'Partai Kebangkitan Bangsa',
            'nama_singkat' => 'PKB',
            'nomor_urut_aktif' => 1,
            'nomor_urut_historis_json' => [2024 => 1],
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.setup.partai-profile.update', $profile), [
                'nama' => 'Partai Kebangkitan Bangsa Perubahan',
                'nama_singkat' => 'PKB Baru',
                'nomor_urut_aktif' => 99,
                'warna_utama' => '#00ff00',
                'warna_aksen' => '#008800',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Profil partai PKB Baru berhasil diperbarui.',
            'sync' => [
                'old_nama_singkat' => 'PKB',
                'old_nama' => 'Partai Kebangkitan Bangsa',
                'new_nama_singkat' => 'PKB Baru',
                'new_nomor_urut' => 99,
            ]
        ]);

        $profile->refresh();
        $this->assertEquals('Partai Kebangkitan Bangsa Perubahan', $profile->nama);
        $this->assertEquals('PKB Baru', $profile->nama_singkat);
        $this->assertEquals(99, $profile->nomor_urut_aktif);
    }
}
