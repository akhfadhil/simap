<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekapPartaiSuara extends Model {
    protected $fillable = ['rekap_id', 'partai_id', 'suara'];

    public function rekap()  { return $this->belongsTo(RekapHeader::class, 'rekap_id'); }
    public function partai() { return $this->belongsTo(RekapPartai::class, 'partai_id'); }
}