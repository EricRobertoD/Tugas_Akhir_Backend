<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;
    protected $table = 'jadwal';
    protected $primaryKey = 'id_jadwal';
    protected $fillable = [
        'id_penyedia',
        'hari',
        'jam_buka',
        'jam_tutup',
    ];

    
    public function PenyediaJasa()
    {
        return $this->belongsTo(PenyediaJasa::class, 'id_penyedia');
    }

}
