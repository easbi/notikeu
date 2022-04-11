<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;
    public $table = "transaksi_pembayaran";
    protected $fillable = [
        'id_pegawai',
        'id_pembayaran',
        'bersih',
        'potongan',
        'jumlah_bayar',
        'send_notif'
    ];
}
