<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekapHeader extends Model {
    protected $fillable = [
        'tps_id', 'jenis',
        'dpt_lk', 'dpt_pr',
        'pengguna_dpt_lk', 'pengguna_dpt_pr',
        'pengguna_dptb_lk', 'pengguna_dptb_pr',
        'pengguna_dpk_lk', 'pengguna_dpk_pr',
        'ss_diterima', 'ss_digunakan', 'ss_rusak', 'ss_sisa',
        'disabilitas_lk', 'disabilitas_pr',
        'suara_tidak_sah', 'status', 'diinput_oleh', 'difinalisasi_at',
    ];

    protected $casts = ['difinalisasi_at' => 'datetime'];

    const JENIS_LABELS = [
        'ppwp'      => 'Presiden & Wakil Presiden',
        'dpd'       => 'DPD',
        'dpr_ri'    => 'DPR RI',
        'dprd_prov' => 'DPRD Provinsi',
        'dprd_kab'  => 'DPRD Kabupaten',
    ];

    public function tps()         { return $this->belongsTo(Tps::class); }
    public function inputBy()     { return $this->belongsTo(User::class, 'diinput_oleh'); }
    public function ppwpSuaras()  { return $this->hasMany(RekapPpwpSuara::class, 'rekap_id'); }
    public function dpdSuaras()   { return $this->hasMany(RekapDpdSuara::class, 'rekap_id'); }
    public function partaiSuaras(){ return $this->hasMany(RekapPartaiSuara::class, 'rekap_id'); }
    public function calegSuaras() { return $this->hasMany(RekapCalegSuara::class, 'rekap_id'); }

    // Computed: total suara sah
    public function getSuaraSahAttribute(): int {
        return match($this->jenis) {
            'ppwp'      => $this->ppwpSuaras->sum('suara'),
            'dpd'       => $this->dpdSuaras->sum('suara'),
            default     => $this->partaiSuaras->sum('suara') + $this->calegSuaras->sum('suara'),
        };
    }

    // Total pengguna hak pilih
    public function getTotalPenggunaLkAttribute(): int {
        return $this->pengguna_dpt_lk + $this->pengguna_dptb_lk + $this->pengguna_dpk_lk;
    }
    public function getTotalPenggunaPrAttribute(): int {
        return $this->pengguna_dpt_pr + $this->pengguna_dptb_pr + $this->pengguna_dpk_pr;
    }
}