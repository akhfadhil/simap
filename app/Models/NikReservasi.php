<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NikReservasi extends Model
{
    protected $table = 'nik_reservasis';

    protected $fillable = [
        'nik',
        'partai_slug',
    ];
}
