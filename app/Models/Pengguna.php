<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';
    protected $fillable = [
        'id_pengguna',
        'nama_pengguna',
        'email_pengguna',
        'password',
        'nomor_telepon_pengguna',
        'nomor_whatsapp_pengguna',
        'alamat_pengguna',
        'saldo',
        'status_blokir',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_pengguna',
        'remember_token',
    ];

    public function Transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_pengguna');
    }

    public function Ulasan()
    {
        return $this->hasMany(Ulasan::class, 'id_pengguna');
    }

    public function Chat()
    {
        return $this->hasMany(Chat::class, 'id_pengguna');
    }

    public function Saldo()
    {
        return $this->hasMany(Saldo::class, 'id_pengguna');
    }
}
