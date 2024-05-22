<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $table = 'chat';
    protected $primaryKey = 'id_chat';
    protected $fillable = [
        'id_chat',
        'id_penyedia',
        'id_pengguna',
        'isi_chat',
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
