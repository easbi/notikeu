<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefGajiPokok extends Model
{
    protected $table = 'ref_gaji_pokok';

    protected $fillable = [
        'jenis_pegawai',
        'golongan',
        'mkg',
        'nominal_gaji',
        'tahun_berlaku'
    ];
}