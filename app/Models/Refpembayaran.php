<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refpembayaran extends Model
{
    use HasFactory;
    public $table = "referensi_pembayaran";
    protected $fillable = [
        'nama_pembayaran',
        'bulan',
        'tahun'
    ];
}
