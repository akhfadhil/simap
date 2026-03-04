<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekapCaleg extends Model {
    protected $fillable = ['partai_id', 'nomor_urut', 'nama_caleg'];

    public function partai() { return $this->belongsTo(RekapPartai::class, 'partai_id'); }
    public function suaras() { return $this->hasMany(RekapCalegSuara::class, 'caleg_id'); }
}