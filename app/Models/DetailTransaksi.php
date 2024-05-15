<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail_transaksi';
    protected $fillable = [
        'id_detail_transaksi',
        'id_transaksi',
        'id_paket',
        'status_penyedia_jasa',
        'subtotal',
    ];

    public function Paket()
    {
        return $this->belongsTo(Paket::class, 'id_paket');
    }

    public function Transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    public function Ulasan()
    {
        return $this->hasMany(Ulasan::class, 'id_detail_transaksi');
    }
    
}
