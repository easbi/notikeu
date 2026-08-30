<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KgbRiwayatGaji extends Model
{
    protected $table = 'riwayat_gaji';
    protected $fillable = ['pegawai_id', 'gaji_pokok', 'tmt_berlaku', 'jenis', 'nomor_sk', 'tanggal_sk', 'pejabat_penetap', 'dasar_peraturan'];
    protected $dates = ['tmt_berlaku', 'tanggal_sk'];

    public function pegawai() { return $this->belongsTo(Pegawai::class); }
}