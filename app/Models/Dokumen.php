<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $fillable = [
        'tps_id', 'uploaded_by', 'jenis', 'status',
        'verified_by', 'verified_at', 'file_path', 'file_name', 'file_size',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    const JENIS = [
        'PPWP'       => 'PPWP',
        'DPR_RI'     => 'DPR RI',
        'DPD'        => 'DPD',
        'DPRD_PROV'  => 'DPRD Provinsi',
        'DPRD_KAB'   => 'DPRD Kabupaten/Kota',
    ];

    const STATUS_COLORS = [
        'menunggu_verifikasi' => '#F4A261',
        'terverifikasi'       => '#2EC4B6',
    ];

    const STATUS_LABELS = [
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'terverifikasi'       => 'Terverifikasi',
    ];

    public function tps()       { return $this->belongsTo(Tps::class); }
    public function uploader()  { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function verifier()  { return $this->belongsTo(User::class, 'verified_by'); }
}