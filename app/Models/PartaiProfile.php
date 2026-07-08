<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartaiProfile extends Model {
    protected $fillable = [
        'slug', 'nama', 'nama_singkat', 'logo_path',
        'warna_utama', 'warna_aksen', 'nomor_urut_aktif',
        'nomor_urut_historis_json', 'is_active',
        'alamat_kantor', 'status_kantor', 'google_maps_url',
        'nama_ketua', 'telp_ketua',
        'nama_sekretaris', 'telp_sekretaris',
        'nama_bendahara', 'telp_bendahara',
    ];

    protected $casts = [
        'nomor_urut_historis_json' => 'array',
        'is_active' => 'boolean',
    ];

    // Relasi user partai.
    public function users() {
        return $this->hasMany(User::class, 'partai_profile_id');
    }

    // Relasi ke data rekap_partais yang memiliki nomor urut aktif yang sama.
    public function rekapPartais() {
        return $this->hasMany(RekapPartai::class, 'nomor_urut', 'nomor_urut_aktif');
    }

    // Relasi caleg melalui RekapPartai
    public function calegs() {
        return $this->hasManyThrough(
            RekapCaleg::class,
            RekapPartai::class,
            'nomor_urut',
            'partai_id',
            'nomor_urut_aktif',
            'id'
        );
    }
}
