<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KgbRiwayatPangkat extends Model
{
    protected $table = 'riwayat_pangkat';  

    protected $fillable = [
        'pegawai_id', 'golongan', 'pangkat', 'jabatan',
        'tmt_mulai', 'nomor_sk', 'tanggal_sk',
        'pejabat_penetap', 'masa_kerja_golongan'
    ];

    protected $dates = ['tmt_mulai', 'tanggal_sk'];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}