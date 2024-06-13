<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiVoucher extends Model
{
    use HasFactory;
    protected $table = 'transaksi_voucher';
    protected $primaryKey = 'id_transaksi_voucher';
    protected $fillable = [
        'id_transaksi_voucher',
        'id_pengguna',
        'id_voucher',
    ];

    public function Pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    public function Voucher()
    {
        return $this->belongsTo(Voucher::class, 'id_voucher');
    }

}
