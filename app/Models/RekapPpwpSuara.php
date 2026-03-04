<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekapPpwpSuara extends Model {
    protected $fillable = ['rekap_id', 'calon_id', 'suara'];

    public function rekap() { return $this->belongsTo(RekapHeader::class, 'rekap_id'); }
    public function calon() { return $this->belongsTo(RekapPpwpCalon::class, 'calon_id'); }
}