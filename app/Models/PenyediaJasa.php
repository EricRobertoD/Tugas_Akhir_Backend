<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class PenyediaJasa extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'penyedia_jasa';
    protected $primaryKey = 'id_penyedia';
    protected $fillable = [
        'id_penyedia',
        'nama_penyedia',
        'email_penyedia',
        'password',
        'nomor_telepon_penyedia',
        'nomor_whatsapp_penyedia',
        'alamat_penyedia',
        'nama_role',
        'gambar_penyedia',
        'deskripsi_penyedia',
    ];

    protected $hidden = [
        'password_admin',
        'remember_token',
    ];

    
    public function GambarPorto()
    {
        return $this->hasMany(GambarPorto::class, 'id_penyedia');
    }

    public function TanggalLibur()
    {
        return $this->hasMany(TanggalLibur::class, 'id_penyedia');
    }

    public function Jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_penyedia');
    }

    public function Paket()
    {
        return $this->hasMany(Paket::class, 'id_penyedia');
    }
}