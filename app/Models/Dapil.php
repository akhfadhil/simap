<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Dapil extends Model {
    protected $fillable = ['nama'];

    public function kecamatans() { return $this->hasMany(Kecamatan::class); }
    public function partais()    { return $this->hasMany(RekapPartai::class); }
}