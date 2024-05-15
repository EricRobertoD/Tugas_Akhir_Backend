<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TanggalLibur extends Model
{
    use HasFactory;
    protected $table = 'tanggal_libur';
    protected $primaryKey = 'id_tanggal';
    protected $fillable = [
        'id_tanggal',
        'id_penyedia',
        'tanggal_awal',
        'tanggal_akhir',
    ];

    
    public function PenyediaJasa()
    {
        return $this->belongsTo(PenyediaJasa::class, 'id_penyedia');
    }

}
