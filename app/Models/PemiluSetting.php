<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemiluSetting extends Model
{
    protected $fillable = ['jenis', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    // Cache aktif jenis agar tidak query berulang
    public static function aktif(): array
    {
        return static::where('is_active', true)->pluck('jenis')->toArray();
    }
}