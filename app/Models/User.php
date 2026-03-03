<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'username', 'role', 'email', 'password',
        'kecamatan_id', 'desa_id', 'tps_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleColor(): string
    {
        return match($this->role) {
            'admin' => '#E63946',
            'ppk'   => '#F4A261',
            'pps'   => '#2EC4B6',
            'kpps'  => '#A8DADC',
            default => '#666666',
        };
    }

    public function kecamatan() 
    { 
        return $this->belongsTo(Kecamatan::class); 
    }

    public function desa()      { return $this->belongsTo(Desa::class); }
    public function tps()       { return $this->belongsTo(Tps::class); }
}
