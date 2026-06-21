<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartaiProfile extends Model {
    protected $fillable = [
        'slug', 'nama', 'nama_singkat', 'logo_path',
        'warna_utama', 'warna_aksen', 'nomor_urut_aktif',
        'nomor_urut_historis_json', 'is_active',
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
}
