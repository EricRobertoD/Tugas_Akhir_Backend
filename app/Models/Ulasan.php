<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasFactory;
    protected $table = 'ulasan';
    protected $primaryKey = 'id_ulasan';
    protected $fillable = [
        'id_ulasan',
        'id_pengguna',
        'id_detail_transaksi',
        'rate_ulasan',
        'isi_ulasan',
    ];

    public function Pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    public function DetailTransaksi()
    {
        return $this->belongsTo(DetailTransaksi::class, 'id_detail_transaksi');
    }

}
