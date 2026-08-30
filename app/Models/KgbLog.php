<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KgbLog extends Model
{
    protected $table = 'log_kgb';
    public $timestamps = false;
    protected $fillable = ['pengurusan_sk_kgb_id', 'pegawai_id', 'nip', 'nama', 'aktivitas', 'deskripsi', 'data_lama', 'data_baru', 'dilakukan_oleh', 'ip_address', 'waktu'];
    protected $casts = ['data_lama' => 'array', 'data_baru' => 'array', 'waktu' => 'datetime'];

    public function pengurusan() { return $this->belongsTo(KgbPengurusan::class, 'kgb_pengurusan_id'); }
    public function pegawai() { return $this->belongsTo(Pegawai::class); }
    public function dilakukanOleh() { return $this->belongsTo(User::class, 'dilakukan_oleh'); }
}