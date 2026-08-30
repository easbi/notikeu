<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KgbPengurusan extends Model
{
    protected $table = 'pengurusan_sk_kgb';

    protected $fillable = [
        'pegawai_id', 'nip', 'nama', 'golongan', 'pangkat', 'jabatan',
        'instansi', 'kode_instansi',
        'tmt_kgb_lama', 'tmt_kgb_baru', 'tmt_gaji_lama', 'tmt_gaji_baru',
        'tmt_kgb_berikutnya',
        'gaji_pokok_lama', 'gaji_pokok_baru', 'dasar_peraturan',
        'masa_kerja_golongan', 'masa_kerja_kgb',
        'sk_pangkat_nomor', 'sk_pangkat_tanggal', 'sk_pangkat_pejabat',
        'sk_pangkat_tmt_gaji', 'sk_pangkat_masa_kerja',
        'nomor_sk', 'tanggal_sk', 'pejabat_penetap', 'nip_pejabat',
        'status', 'keterangan',
        'tanggal_pengajuan', 'tanggal_selesai', 'diproses_oleh'
    ];

    protected $dates = [
        'tmt_kgb_lama', 'tmt_kgb_baru',
        'tmt_gaji_lama', 'tmt_gaji_baru',
        'tmt_kgb_berikutnya',
        'sk_pangkat_tanggal', 'sk_pangkat_tmt_gaji',
        'tanggal_sk', 'tanggal_pengajuan', 'tanggal_selesai'
    ];

    // ============ RELASI ============

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    // PERBAIKI: foreign key = 'pengurusan_sk_kgb_id'
    public function logs()
    {
        return $this->hasMany(KgbLog::class, 'pengurusan_sk_kgb_id', 'id');
        //                           ↑ foreign key di log_kgb
    }

    // ============ SCOPE ============

    public function scopeOnGoing($q)
    {
        return $q->whereIn('status', ['pending', 'proses']);
    }

    public function scopeSelesai($q)
    {
        return $q->where('status', 'selesai');
    }

    // ============ ACCESSOR ============

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Menunggu Proses',
            'proses' => 'Sedang Diproses',
            'selesai' => 'Selesai'
        ][$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'pending' => 'warning',
            'proses' => 'info',
            'selesai' => 'success'
        ][$this->status] ?? 'secondary';
    }

    public function getGajiLamaFormattedAttribute()
    {
        return 'Rp ' . number_format($this->gaji_pokok_lama, 0, ',', '.') . ',-';
    }

    public function getGajiBaruFormattedAttribute()
    {
        return 'Rp ' . number_format($this->gaji_pokok_baru, 0, ',', '.') . ',-';
    }

    // ============ HELPER ============

    public function generateNomorSk($kode = '13741')
    {
        $tahun = Carbon::now()->year;
        $urutan = str_pad(
            self::whereYear('created_at', $tahun)
                ->where('status', 'selesai')
                ->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
        return Carbon::now()->format('dmY') . "/{$kode}/KPG-{$urutan} Tahun {$tahun}";
    }
}