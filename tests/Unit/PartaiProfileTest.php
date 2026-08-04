<?php

namespace Tests\Unit;

use App\Models\PartaiProfile;
use App\Models\RekapPartai;
use App\Models\User;
use Database\Seeders\PartaiProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartaiProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_partai_profile_with_correct_fields(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'pdi-p',
            'nama' => 'Partai Demokrasi Indonesia Perjuangan',
            'nama_singkat' => 'PDI P',
            'warna_utama' => '#DC2626',
            'nomor_urut_aktif' => 3,
            'nomor_urut_historis_json' => [2024 => 3],
        ]);

        $this->assertDatabaseHas('partai_profiles', [
            'slug' => 'pdi-p',
            'nama_singkat' => 'PDI P',
            'warna_utama' => '#DC2626',
            'nomor_urut_aktif' => 3,
        ]);

        $profile->refresh();
        $this->assertEquals([2024 => 3], $profile->nomor_urut_historis_json);
        $this->assertTrue($profile->is_active);
    }

    public function test_partai_profile_seeder_seeds_all_eighteen_parties(): void
    {
        $this->seed(PartaiProfileSeeder::class);

        $this->assertDatabaseCount('partai_profiles', 18);
        $this->assertDatabaseHas('partai_profiles', [
            'slug' => 'golkar',
            'nomor_urut_aktif' => 4,
        ]);
    }

    public function test_user_belongs_to_partai_profile(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'golkar',
            'nama' => 'Partai Golongan Karya',
            'nama_singkat' => 'Golkar',
            'nomor_urut_aktif' => 4,
        ]);

        $user = User::create([
            'name' => 'Saksi Golkar',
            'username' => 'saksigolkar',
            'role' => 'partai',
            'password' => bcrypt('password'),
            'partai_profile_id' => $profile->id,
        ]);

        $this->assertEquals($profile->id, $user->partaiProfile->id);
        $this->assertEquals('Golkar', $user->partaiProfile->nama_singkat);
    }

    public function test_partai_profile_rekap_partai_relationship(): void
    {
        $profile = PartaiProfile::create([
            'slug' => 'pkb',
            'nama' => 'Partai Kebangkitan Bangsa',
            'nama_singkat' => 'PKB',
            'nomor_urut_aktif' => 1,
        ]);

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

        $this->assertCount(2, $profile->rekapPartais);
        $this->assertTrue($profile->rekapPartais->contains($rekapDpr));
        $this->assertTrue($profile->rekapPartais->contains($rekapProv));
    }
}
