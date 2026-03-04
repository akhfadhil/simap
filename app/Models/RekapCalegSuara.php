<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekapCalegSuara extends Model {
    protected $fillable = ['rekap_id', 'caleg_id', 'suara'];

    public function rekap()  { return $this->belongsTo(RekapHeader::class, 'rekap_id'); }
    public function caleg()  { return $this->belongsTo(RekapCaleg::class, 'caleg_id'); }
}