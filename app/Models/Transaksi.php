<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    protected $fillable = [
        'id_transaksi',
        'id_pengguna',
        'status_transaksi',
        'total_harga',
    ];

    public function Pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    public function DetailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

}
