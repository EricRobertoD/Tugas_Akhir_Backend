<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    use HasFactory;
    protected $table = 'paket';
    protected $primaryKey = 'id_paket';
    protected $fillable = [
        'id_paket',
        'id_penyedia',
        'nama_paket',
        'isi_paket',
        'harga_paket',
    ];
    
    public function PenyediaJasa()
    {
        return $this->belongsTo(PenyediaJasa::class, 'id_penyedia');
    }
    
    public function DetailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_paket');
    }
}
