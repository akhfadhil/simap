<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekapPartai extends Model {
    protected $fillable = ['jenis', 'nomor_urut', 'nama_partai'];

    const JENIS = ['dpr_ri', 'dprd_prov', 'dprd_kab'];

    public function calegs()      { return $this->hasMany(RekapCaleg::class, 'partai_id')->orderBy('nomor_urut'); }
    public function suaras()      { return $this->hasMany(RekapPartaiSuara::class, 'partai_id'); }
}