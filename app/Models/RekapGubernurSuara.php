<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekapGubernurSuara extends Model
{
    protected $fillable = ['rekap_id', 'calon_id', 'suara'];

    public function calon() { return $this->belongsTo(RekapGubernurCalon::class, 'calon_id'); }
}
