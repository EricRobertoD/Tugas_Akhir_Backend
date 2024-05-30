<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    use HasFactory;
    protected $table = 'saldo';
    protected $primaryKey = 'id_saldo';
    protected $fillable = [
        'id_saldo',
        'id_penyedia',
        'id_pengguna',
        'jenis',
        'total',
        'tanggal',
        'gambar_saldo',
        'status',
    ];

    public function PenyediaJasa()
    {
        return $this->belongsTo(PenyediaJasa::class, 'id_penyedia');
    }
    public function Pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
}
