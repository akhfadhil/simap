<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $fillable = ['nama', 'dapil_id'];

    public function desas() { return $this->hasMany(Desa::class); }
    public function users() { return $this->hasMany(User::class); }
    public function dapil() { return $this->belongsTo(Dapil::class); }
}