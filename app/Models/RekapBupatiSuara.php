<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekapBupatiSuara extends Model
{
    protected $fillable = ['rekap_id', 'calon_id', 'suara'];

    public function calon() { return $this->belongsTo(RekapBupatiCalon::class, 'calon_id'); }
}
