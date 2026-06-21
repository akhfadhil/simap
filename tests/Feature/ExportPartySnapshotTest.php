<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PartaiProfile;
use App\Models\RekapPartai;
use App\Models\RekapCaleg;
use App\Models\Dapil;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Tps;
use App\Models\RekapHeader;
use App\Models\RekapPartaiSuara;
use App\Models\RekapCalegSuara;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ExportPartySnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function test_command_fails_if_slug_does_not_exist(): void
    {
        $this->artisan('export:party-snapshot non-existent')
            ->assertFailed()
            ->expectsOutput("Profil partai dengan slug 'non-existent' tidak ditemukan.");
    }

    public function test_command_exports_snapshot_successfully(): void
    {
        // 1. Create target party profile
        $profile = PartaiProfile::create([
            'slug' => 'pdi-p',
            'nama' => 'Partai Demokrasi Indonesia Perjuangan',
            'nama_singkat' => 'PDIP',
            'nomor_urut_aktif' => 3,
        ]);

        // Create competitor party profile
        $competitorProfile = PartaiProfile::create([
            'slug' => 'golkar',
            'nama' => 'Partai Golongan Karya',
            'nama_singkat' => 'Golkar',
            'nomor_urut_aktif' => 4,
        ]);

        // 2. Create master database records
        $dapil = Dapil::create(['nama' => 'Dapil 1']);
        $kecamatan = Kecamatan::create(['nama' => 'Kecamatan A', 'dapil_id' => $dapil->id]);
        $desa = Desa::create(['nama' => 'Desa B', 'kecamatan_id' => $kecamatan->id]);
        $tps = Tps::create(['nama' => 'TPS 01', 'desa_id' => $desa->id]);

        // 3. Create legislative RekapPartai records
        $rekapPdip = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 3,
            'nama_partai' => 'PDIP',
        ]);

        $rekapGolkar = RekapPartai::create([
            'jenis' => 'dpr_ri',
            'nomor_urut' => 4,
            'nama_partai' => 'Golkar',
        ]);

        // 4. Create RekapCaleg records
        $calegPdip = RekapCaleg::create([
            'partai_id' => $rekapPdip->id,
            'nomor_urut' => 1,
            'nama_caleg' => 'Megawati',
        ]);

        $calegGolkar = RekapCaleg::create([
            'partai_id' => $rekapGolkar->id,
            'nomor_urut' => 1,
            'nama_caleg' => 'Airlangga',
        ]);

        // 5. Create RekapHeader
        $header = RekapHeader::create([
            'tps_id' => $tps->id,
            'jenis' => 'dpr_ri',
            'status' => 'final',
        ]);

        // 6. Create Votes
        $pdipVote = RekapPartaiSuara::create([
            'rekap_id' => $header->id,
            'partai_id' => $rekapPdip->id,
            'suara' => 150,
        ]);

        $golkarVote = RekapPartaiSuara::create([
            'rekap_id' => $header->id,
            'partai_id' => $rekapGolkar->id,
            'suara' => 120,
        ]);

        $pdipCalegVote = RekapCalegSuara::create([
            'rekap_id' => $header->id,
            'caleg_id' => $calegPdip->id,
            'suara' => 80,
        ]);

        $golkarCalegVote = RekapCalegSuara::create([
            'rekap_id' => $header->id,
            'caleg_id' => $calegGolkar->id,
            'suara' => 60,
        ]);

        // 7. Run command
        $this->artisan('export:party-snapshot pdi-p')
            ->assertSuccessful()
            ->expectsOutput("Memulai ekspor snapshot untuk partai: Partai Demokrasi Indonesia Perjuangan (PDIP)...");

        // 8. Assert JSON file exists in exports folder
        $files = Storage::files('exports');
        $this->assertCount(1, $files);
        $filename = $files[0];
        $this->assertStringContainsString('party-snapshot-pdi-p-', $filename);

        // 9. Read and parse JSON content
        $jsonContent = json_decode(Storage::get($filename), true);
        
        $this->assertArrayHasKey('exported_at', $jsonContent);
        $this->assertEquals('SIMAP Utama', $jsonContent['source_app']);
        
        // Assert party profile
        $this->assertEquals('pdi-p', $jsonContent['party_profile']['slug']);
        $this->assertEquals('PDIP', $jsonContent['party_profile']['nama_singkat']);

        // Assert master data is fully exported
        $this->assertCount(1, $jsonContent['dapils']);
        $this->assertEquals('Dapil 1', $jsonContent['dapils'][0]['nama']);
        $this->assertCount(1, $jsonContent['kecamatans']);
        $this->assertCount(1, $jsonContent['desas']);
        $this->assertCount(1, $jsonContent['tps']);

        // Assert rekap_partais contains PDIP but NOT Golkar
        $this->assertCount(1, $jsonContent['rekap_partais']);
        $this->assertEquals('PDIP', $jsonContent['rekap_partais'][0]['nama_partai']);

        // Assert rekap_calegs contains Megawati but NOT Airlangga
        $this->assertCount(1, $jsonContent['rekap_calegs']);
        $this->assertEquals('Megawati', $jsonContent['rekap_calegs'][0]['nama_caleg']);

        // Assert rekap_headers is present
        $this->assertCount(1, $jsonContent['rekap_headers']);
        $this->assertEquals('dpr_ri', $jsonContent['rekap_headers'][0]['jenis']);

        // Assert voice results only contain target party/caleg
        // PdipVote (150) should be included, GolkarVote (120) should NOT
        $this->assertCount(1, $jsonContent['rekap_partai_suaras']);
        $this->assertEquals($rekapPdip->id, $jsonContent['rekap_partai_suaras'][0]['partai_id']);
        $this->assertEquals(150, $jsonContent['rekap_partai_suaras'][0]['suara']);

        // PdipCalegVote (80) should be included, GolkarCalegVote (60) should NOT
        $this->assertCount(1, $jsonContent['rekap_caleg_suaras']);
        $this->assertEquals($calegPdip->id, $jsonContent['rekap_caleg_suaras'][0]['caleg_id']);
        $this->assertEquals(80, $jsonContent['rekap_caleg_suaras'][0]['suara']);
    }
}
