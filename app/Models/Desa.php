<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    protected $fillable = ['nama', 'kecamatan_id'];

    public function kecamatan() { return $this->belongsTo(Kecamatan::class); }
    public function tps()       { return $this->hasMany(Tps::class); }
    public function users()     { return $this->hasMany(User::class); }
}