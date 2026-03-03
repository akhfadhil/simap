<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tps extends Model
{
    protected $fillable = ['nama', 'desa_id'];

    public function desa()     { return $this->belongsTo(Desa::class); }
    public function dokumens() { return $this->hasMany(Dokumen::class); }
    public function users()    { return $this->hasMany(User::class); }
}