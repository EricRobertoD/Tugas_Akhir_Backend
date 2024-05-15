<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GambarPorto extends Model
{
    use HasFactory;
    protected $table = 'gambar_porto';
    protected $primaryKey = 'id_porto';
    protected $fillable = [
        'id_porto',
        'id_penyedia',
        'gambar',
    ];

    
    public function PenyediaJasa()
    {
        return $this->belongsTo(PenyediaJasa::class, 'id_penyedia');
    }
}
