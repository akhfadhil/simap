<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Dokumen;
use App\Models\Kecamatan;
use App\Models\PemiluSetting;
use App\Models\Tps;
use App\Models\User;
use Database\Seeders\PemiluSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QABatch4DokumenAndVerifikasiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $komisioner;
    private User $ppk;
    private User $pps;
    private User $kpps;
    private User $ppkOther;
    private User $ppsOther;
    private User $kppsOther;

    private Kecamatan $kecamatanA;
    private Kecamatan $kecamatanB;
    private Desa $desaA;
    private Desa $desaB;
    private Tps $tpsA;
    private Tps $tpsB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();

        $this->seed(PemiluSettingSeeder::class);

        $this->kecamatanA = Kecamatan::create(['nama' => 'Bangorejo']);
        $this->kecamatanB = Kecamatan::create(['nama' => 'Genteng']);
        $this->desaA = Desa::create(['nama' => 'Bangorejo', 'kecamatan_id' => $this->kecamatanA->id]);
        $this->desaB = Desa::create(['nama' => 'Genteng Kulon', 'kecamatan_id' => $this->kecamatanB->id]);
        $this->tpsA = Tps::create(['nama' => 'TPS 001', 'desa_id' => $this->desaA->id]);
        $this->tpsB = Tps::create(['nama' => 'TPS 002', 'desa_id' => $this->desaB->id]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'username' => 'admin_dokumen',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->komisioner = User::create([
            'name' => 'Komisioner User',
            'username' => 'komisioner_dokumen',
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

        $this->kpps = User::create([
            'name' => 'KPPS Bangorejo',
            'username' => 'kpps_bangorejo',
            'password' => Hash::make('password123'),
            'role' => 'kpps',
            'kecamatan_id' => $this->kecamatanA->id,
            'desa_id' => $this->desaA->id,
            'tps_id' => $this->tpsA->id,
        ]);

        $this->ppkOther = User::create([
            'name' => 'PPK Genteng',
            'username' => 'ppk_genteng',
            'password' => Hash::make('password123'),
            'role' => 'ppk',
            'kecamatan_id' => $this->kecamatanB->id,
        ]);

        $this->ppsOther = User::create([
            'name' => 'PPS Genteng',
            'username' => 'pps_genteng',
            'password' => Hash::make('password123'),
            'role' => 'pps',
            'kecamatan_id' => $this->kecamatanB->id,
            'desa_id' => $this->desaB->id,
        ]);

        $this->kppsOther = User::create([
            'name' => 'KPPS Genteng',
            'username' => 'kpps_genteng',
            'password' => Hash::make('password123'),
            'role' => 'kpps',
            'kecamatan_id' => $this->kecamatanB->id,
            'desa_id' => $this->desaB->id,
            'tps_id' => $this->tpsB->id,
        ]);
    }

    // ==========================================
    // 1. KPPS UPLOAD C-HASIL FORM & WORKFLOW
    // ==========================================

    public function test_dok_kpps_01_kpps_can_access_upload_form_cleanly(): void
    {
        $response = $this->actingAs($this->kpps)->get(route('dokumen.upload'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dokumen/Upload')
            ->where('isAdminView', false)
            ->where('tps.id', $this->tpsA->id)
            ->has('aktifJenis')
            ->has('jenisLabels')
            ->has('statusLabels')
            ->has('uploaded')
        );
    }

    public function test_dok_kpps_02_kpps_can_upload_valid_pdf_for_active_election(): void
    {
        $file = UploadedFile::fake()->create('c_hasil_ppwp.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->kpps)->post(route('dokumen.store'), [
            'jenis' => 'PPWP',
            'file' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dokumens', [
            'tps_id' => $this->tpsA->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_name' => 'c_hasil_ppwp.pdf',
        ]);

        $dokumen = Dokumen::where('tps_id', $this->tpsA->id)->where('jenis', 'PPWP')->first();
        $this->assertNotNull($dokumen);
        Storage::assertExists($dokumen->file_path);
    }

    public function test_dok_kpps_03_kpps_reuploading_replaces_previous_file_and_record(): void
    {
        // First upload
        $firstFile = UploadedFile::fake()->createWithContent('first.pdf', 'first content stream');
        $this->actingAs($this->kpps)->post(route('dokumen.store'), [
            'jenis' => 'PPWP',
            'file' => $firstFile,
        ]);

        $oldDoc = Dokumen::where('tps_id', $this->tpsA->id)->where('jenis', 'PPWP')->first();
        $this->assertEquals('first.pdf', $oldDoc->file_name);
        $this->assertEquals('first content stream', Storage::get($oldDoc->file_path));

        // Second upload with revision
        $secondFile = UploadedFile::fake()->createWithContent('revision.pdf', 'revised content stream');
        $this->actingAs($this->kpps)->post(route('dokumen.store'), [
            'jenis' => 'PPWP',
            'file' => $secondFile,
        ]);

        // Only 1 record should exist for this TPS and jenis
        $this->assertEquals(1, Dokumen::where('tps_id', $this->tpsA->id)->where('jenis', 'PPWP')->count());
        $newDoc = Dokumen::where('tps_id', $this->tpsA->id)->where('jenis', 'PPWP')->first();
        $this->assertEquals('revision.pdf', $newDoc->file_name);
        $this->assertEquals('revised content stream', Storage::get($newDoc->file_path));
    }

    public function test_dok_kpps_04_upload_fails_when_file_is_not_pdf(): void
    {
        $fakeImg = UploadedFile::fake()->create('image.png', 500, 'image/png');

        $response = $this->actingAs($this->kpps)->post(route('dokumen.store'), [
            'jenis' => 'PPWP',
            'file' => $fakeImg,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_dok_kpps_05_upload_fails_when_file_exceeds_5mb(): void
    {
        // 6000 KB > 5120 KB limit
        $bigFile = UploadedFile::fake()->create('large.pdf', 6000, 'application/pdf');

        $response = $this->actingAs($this->kpps)->post(route('dokumen.store'), [
            'jenis' => 'PPWP',
            'file' => $bigFile,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_dok_kpps_06_upload_fails_for_inactive_election_type(): void
    {
        // Deactivate DPD
        PemiluSetting::where('jenis', 'dpd')->update(['is_active' => false]);

        $file = UploadedFile::fake()->create('dpd.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->kpps)->post(route('dokumen.store'), [
            'jenis' => 'DPD',
            'file' => $file,
        ]);

        $response->assertForbidden();
    }

    public function test_dok_kpps_07_upload_fails_with_missing_or_invalid_jenis(): void
    {
        $file = UploadedFile::fake()->create('c_hasil.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->kpps)->post(route('dokumen.store'), [
            'jenis' => 'INVALID_JENIS',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['jenis']);
    }

    public function test_dok_kpps_08_non_kpps_cannot_upload_via_kpps_route(): void
    {
        $file = UploadedFile::fake()->create('c_hasil.pdf', 500, 'application/pdf');

        // PPS trying to upload via /dokumen/upload directly
        $response = $this->actingAs($this->pps)->post(route('dokumen.store'), [
            'jenis' => 'PPWP',
            'file' => $file,
        ]);

        $response->assertForbidden();
    }

    // ==========================================
    // 2. PPS VERIFIKASI & PENOLAKAN
    // ==========================================

    public function test_dok_pps_01_pps_can_view_verification_index(): void
    {
        $response = $this->actingAs($this->pps)->get(route('dokumen.pps'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dokumen/Pps')
            ->has('desa')
            ->has('tpsList')
            ->has('tpsOptions')
            ->has('aktifJenis')
        );
    }

    public function test_dok_pps_02_pps_can_filter_documents_by_tps(): void
    {
        $response = $this->actingAs($this->pps)->get(route('dokumen.pps', [
            'tps_id' => $this->tpsA->id,
        ]));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dokumen/Pps')
            ->where('selectedTpsId', $this->tpsA->id)
        );
    }

    public function test_dok_pps_03_pps_can_verify_document_with_one_click(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->pps)->post(route('dokumen.verifikasi', $dokumen), [
            'aksi' => 'terverifikasi',
        ]);

        $response->assertSessionHas('success', 'Dokumen berhasil diverifikasi.');
        $dokumen->refresh();
        $this->assertEquals('terverifikasi', $dokumen->status);
        $this->assertEquals($this->pps->id, $dokumen->verified_by);
        $this->assertNotNull($dokumen->verified_at);
        $this->assertNull($dokumen->komentar);
    }

    public function test_dok_pps_04_pps_can_reject_document_with_mandatory_comment(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->pps)->post(route('dokumen.verifikasi', $dokumen), [
            'aksi' => 'ditolak',
            'komentar' => 'Halaman 2 buram dan tanda tangan saksi tidak terlihat.',
        ]);

        $response->assertSessionHas('success', 'Dokumen berhasil ditolak.');
        $dokumen->refresh();
        $this->assertEquals('ditolak', $dokumen->status);
        $this->assertEquals('Halaman 2 buram dan tanda tangan saksi tidak terlihat.', $dokumen->komentar);
        $this->assertEquals($this->pps->id, $dokumen->verified_by);
        $this->assertNotNull($dokumen->verified_at);
    }

    public function test_dok_pps_05_rejection_fails_without_comment(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->pps)->post(route('dokumen.verifikasi', $dokumen), [
            'aksi' => 'ditolak',
            'komentar' => '',
        ]);

        $response->assertSessionHasErrors(['komentar']);
    }

    public function test_dok_pps_06_pps_cannot_verify_document_from_another_desa(): void
    {
        // Document belongs to $this->tpsB (Desa Genteng Kulon)
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsB->id,
            'uploaded_by' => $this->kppsOther->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'documents/test_b.pdf',
            'file_name' => 'test_b.pdf',
            'file_size' => 1024,
        ]);

        // $this->pps is for Desa Bangorejo -> cannot verify
        $response = $this->actingAs($this->pps)->post(route('dokumen.verifikasi', $dokumen), [
            'aksi' => 'terverifikasi',
        ]);

        $response->assertForbidden();
    }

    public function test_dok_pps_07_non_pps_cannot_call_pps_verifikasi(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ]);

        // KPPS cannot verify own document
        $this->actingAs($this->kpps)->post(route('dokumen.verifikasi', $dokumen), [
            'aksi' => 'terverifikasi',
        ])->assertForbidden();
    }

    // ==========================================
    // 3. ALUR REVISI KPPS SETELAH PENOLAKAN
    // ==========================================

    public function test_dok_revise_01_kpps_sees_rejection_notes_in_upload_page(): void
    {
        Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'ditolak',
            'komentar' => 'Mohon scan ulang halaman C-Hasil Salinan yang jelas.',
            'file_path' => 'documents/old.pdf',
            'file_name' => 'old.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->kpps)->get(route('dokumen.upload'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dokumen/Upload')
            ->where('uploaded.PPWP.status', 'ditolak')
            ->where('uploaded.PPWP.komentar', 'Mohon scan ulang halaman C-Hasil Salinan yang jelas.')
        );
    }

    public function test_dok_revise_02_kpps_can_upload_revised_file_resetting_status(): void
    {
        Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'ditolak',
            'komentar' => 'Foto buram.',
            'file_path' => 'documents/old.pdf',
            'file_name' => 'old.pdf',
            'file_size' => 1024,
        ]);

        $revisi = UploadedFile::fake()->create('c_hasil_revisi.pdf', 1500, 'application/pdf');

        $response = $this->actingAs($this->kpps)->post(route('dokumen.store'), [
            'jenis' => 'PPWP',
            'file' => $revisi,
        ]);

        $response->assertSessionHas('success');
        $newDoc = Dokumen::where('tps_id', $this->tpsA->id)->where('jenis', 'PPWP')->first();
        $this->assertEquals('menunggu_verifikasi', $newDoc->status);
        $this->assertNull($newDoc->komentar);
        $this->assertEquals('c_hasil_revisi.pdf', $newDoc->file_name);
    }

    // ==========================================
    // 4. PPK KECAMATAN DOKUMEN & D-HASIL
    // ==========================================

    public function test_dok_ppk_01_ppk_can_view_kecamatan_documents(): void
    {
        $response = $this->actingAs($this->ppk)->get(route('dokumen.ppk'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dokumen/Ppk')
            ->where('kecamatan.id', $this->kecamatanA->id)
            ->has('desas')
            ->has('aktifJenis')
        );
    }

    public function test_dok_ppk_02_ppk_upload_form_redirects_to_dokumen_ppk(): void
    {
        $response = $this->actingAs($this->ppk)->get(route('ppk.upload'));

        $response->assertRedirect(route('dokumen.ppk'));
    }

    public function test_dok_ppk_03_ppk_can_upload_d_hasil_kecamatan(): void
    {
        $file = UploadedFile::fake()->create('d_hasil_bupati.pdf', 2048, 'application/pdf');

        $response = $this->actingAs($this->ppk)->post(route('ppk.upload.store'), [
            'jenis' => 'BUPATI',
            'file' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dokumens', [
            'kecamatan_id' => $this->kecamatanA->id,
            'tps_id' => null,
            'level' => 'kecamatan',
            'jenis' => 'BUPATI',
            'status' => 'menunggu_verifikasi',
            'file_name' => 'd_hasil_bupati.pdf',
        ]);
    }

    public function test_dok_ppk_04_ppk_reuploading_replaces_kecamatan_document(): void
    {
        $file1 = UploadedFile::fake()->createWithContent('d_hasil_1.pdf', 'd_hasil initial');
        $this->actingAs($this->ppk)->post(route('ppk.upload.store'), [
            'jenis' => 'BUPATI',
            'file' => $file1,
        ]);

        $doc1 = Dokumen::where('kecamatan_id', $this->kecamatanA->id)->where('jenis', 'BUPATI')->first();
        $this->assertEquals('d_hasil_1.pdf', $doc1->file_name);
        $this->assertEquals('d_hasil initial', Storage::get($doc1->file_path));

        $file2 = UploadedFile::fake()->createWithContent('d_hasil_rev.pdf', 'd_hasil updated');
        $this->actingAs($this->ppk)->post(route('ppk.upload.store'), [
            'jenis' => 'BUPATI',
            'file' => $file2,
        ]);

        $this->assertEquals(1, Dokumen::where('kecamatan_id', $this->kecamatanA->id)->where('jenis', 'BUPATI')->count());
        $doc2 = Dokumen::where('kecamatan_id', $this->kecamatanA->id)->where('jenis', 'BUPATI')->first();
        $this->assertEquals('d_hasil_rev.pdf', $doc2->file_name);
        $this->assertEquals('d_hasil updated', Storage::get($doc2->file_path));
    }

    // ==========================================
    // 5. ADMIN MASTER DOKUMEN & VERIFIKASI ADMIN
    // ==========================================

    public function test_dok_adm_01_admin_can_view_all_documents(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dokumen.admin'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dokumen/Admin')
            ->has('kecamatans')
            ->has('aktifJenis')
            ->has('aktifDokumenMap')
        );
    }

    public function test_dok_adm_02_admin_can_verify_any_document(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'GUBERNUR',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'documents/gub.pdf',
            'file_name' => 'gub.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->admin)->post(route('dokumen.verifikasi.admin', $dokumen), [
            'aksi' => 'terverifikasi',
        ]);

        $response->assertSessionHas('success', 'Dokumen berhasil diverifikasi.');
        $dokumen->refresh();
        $this->assertEquals('terverifikasi', $dokumen->status);
        $this->assertEquals($this->admin->id, $dokumen->verified_by);
    }

    public function test_dok_adm_03_admin_can_reject_with_comment(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'GUBERNUR',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'documents/gub.pdf',
            'file_name' => 'gub.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->admin)->post(route('dokumen.verifikasi.admin', $dokumen), [
            'aksi' => 'ditolak',
            'komentar' => 'Data tidak cocok dengan angka Sirekap KPU.',
        ]);

        $response->assertSessionHas('success', 'Dokumen berhasil ditolak.');
        $dokumen->refresh();
        $this->assertEquals('ditolak', $dokumen->status);
        $this->assertEquals('Data tidak cocok dengan angka Sirekap KPU.', $dokumen->komentar);
    }

    public function test_dok_adm_04_non_admin_cannot_use_verifikasi_admin(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'GUBERNUR',
            'level' => 'tps',
            'status' => 'menunggu_verifikasi',
            'file_path' => 'documents/gub.pdf',
            'file_name' => 'gub.pdf',
            'file_size' => 1024,
        ]);

        $this->actingAs($this->pps)->post(route('dokumen.verifikasi.admin', $dokumen), [
            'aksi' => 'terverifikasi',
        ])->assertForbidden();

        $this->actingAs($this->ppk)->post(route('dokumen.verifikasi.admin', $dokumen), [
            'aksi' => 'terverifikasi',
        ])->assertForbidden();
    }

    // ==========================================
    // 6. PREVIEW, DOWNLOAD & SECURITY MATRIX
    // ==========================================

    public function test_dok_view_01_admin_and_komisioner_can_preview_any_document(): void
    {
        Storage::put('documents/test.pdf', 'dummy pdf content');
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'terverifikasi',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ]);

        $this->actingAs($this->admin)->get(route('dokumen.preview', $dokumen))->assertOk();
        $this->actingAs($this->komisioner)->get(route('dokumen.preview', $dokumen))->assertOk();
    }

    public function test_dok_view_02_ppk_can_preview_inside_own_kecamatan_only(): void
    {
        Storage::put('documents/test.pdf', 'dummy pdf content');
        $docKecA = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'terverifikasi',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ]);

        // PPK Bangorejo can preview Bangorejo TPS doc
        $this->actingAs($this->ppk)->get(route('dokumen.preview', $docKecA))->assertOk();

        // PPK Genteng cannot preview Bangorejo TPS doc (403)
        $this->actingAs($this->ppkOther)->get(route('dokumen.preview', $docKecA))->assertForbidden();
    }

    public function test_dok_view_03_pps_can_preview_inside_own_desa_only(): void
    {
        Storage::put('documents/test.pdf', 'dummy pdf content');
        $docDesaA = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'terverifikasi',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ]);

        // PPS Bangorejo can preview
        $this->actingAs($this->pps)->get(route('dokumen.preview', $docDesaA))->assertOk();

        // PPS Genteng cannot preview Bangorejo doc
        $this->actingAs($this->ppsOther)->get(route('dokumen.preview', $docDesaA))->assertForbidden();
    }

    public function test_dok_view_04_kpps_can_preview_own_tps_only(): void
    {
        Storage::put('documents/test.pdf', 'dummy pdf content');
        $docTpsA = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'terverifikasi',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ]);

        // KPPS A can preview
        $this->actingAs($this->kpps)->get(route('dokumen.preview', $docTpsA))->assertOk();

        // KPPS B cannot preview TPS A doc
        $this->actingAs($this->kppsOther)->get(route('dokumen.preview', $docTpsA))->assertForbidden();
    }

    public function test_dok_view_05_preview_returns_404_if_file_missing_on_disk(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'terverifikasi',
            'file_path' => 'documents/missing_file.pdf',
            'file_name' => 'missing_file.pdf',
            'file_size' => 1024,
        ]);

        $this->actingAs($this->admin)->get(route('dokumen.preview', $dokumen))->assertNotFound();
    }

    public function test_dok_dl_01_download_delivers_file_or_aborts_404(): void
    {
        Storage::put('documents/dl_test.pdf', 'pdf binary content');
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'terverifikasi',
            'file_path' => 'documents/dl_test.pdf',
            'file_name' => 'dl_test.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->kpps)->get(route('dokumen.download', $dokumen));
        $response->assertOk();

        // Missing file
        $dokumenMissing = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'GUBERNUR',
            'level' => 'tps',
            'status' => 'terverifikasi',
            'file_path' => 'documents/does_not_exist.pdf',
            'file_name' => 'does_not_exist.pdf',
            'file_size' => 1024,
        ]);
        $this->actingAs($this->kpps)->get(route('dokumen.download', $dokumenMissing))->assertNotFound();
    }

    public function test_dok_restore_01_non_admin_cannot_restore_and_non_archived_returns_400(): void
    {
        $dokumen = Dokumen::create([
            'tps_id' => $this->tpsA->id,
            'uploaded_by' => $this->kpps->id,
            'jenis' => 'PPWP',
            'level' => 'tps',
            'status' => 'terverifikasi',
            'is_archived' => false,
            'file_path' => 'documents/not_archived.pdf',
            'file_name' => 'not_archived.pdf',
            'file_size' => 1024,
        ]);

        // Non-admin forbidden
        $this->actingAs($this->ppk)->post(route('dokumen.restore', $dokumen))->assertForbidden();

        // Admin trying to restore non-archived document gets 400
        $this->actingAs($this->admin)->post(route('dokumen.restore', $dokumen))->assertStatus(400);
    }
}
