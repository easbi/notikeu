<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Pegawai extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pegawai';

    protected $fillable = [
        'id',
        'fullname',
        'username',
        'nip',
        'no_hp',
        'no_rek',
        'no_rek_bni',
        'no_rek_bri',
        'organisasi',
        'unit_kerja',
        'jabatan',
        'email',
        'password',
        
        // ===== FIELD  UNTUK KGB =====
        'gelar',
        'golongan',
        'pangkat',
        'kode_instansi',
        'tmt_pangkat_terakhir',
        'tmt_kgb_terakhir',
        'tmt_gaji_terakhir',
        'gaji_pokok_saat_ini',
        'status_aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $dates = [
        'tmt_pangkat_terakhir',
        'tmt_kgb_terakhir',
        'tmt_gaji_terakhir',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];


    
    public function riwayatPangkat()
    {
        return $this->hasMany(KgbRiwayatPangkat::class)->orderBy('tmt_mulai', 'desc');
    }

    public function riwayatGaji()
    {
        return $this->hasMany(KgbRiwayatGaji::class)->orderBy('tmt_berlaku', 'desc');
    }

    public function pengurusanKgb()
    {
        return $this->hasMany(KgbPengurusan::class)->orderBy('created_at', 'desc');
    }

    public function pengurusanKgbOnGoing()
    {
        return $this->hasMany(KgbPengurusan::class)
            ->whereIn('status', ['pending', 'proses'])
            ->orderBy('created_at', 'desc');
    }

    public function kgbLogs()
    {
        return $this->hasMany(KgbLog::class)->orderBy('waktu', 'desc');
    }

 
    public function getTmtCpnsAttribute()
    {
        if (!$this->nip || strlen(trim($this->nip)) < 14) {
            return null;
        }
        
        $nip = trim($this->nip);
        
        // Digit 9-12 = Tahun (0-based: index 8-11)
        $tahun = substr($nip, 8, 4);  // 2019
        
        // Digit 13-14 = Bulan (0-based: index 12-13)
        $bulan = (int) substr($nip, 12, 2); // 01
        
        // Validasi
        if ($tahun < 1900 || $tahun > 2099 || $bulan < 1 || $bulan > 12) {
            return null;
        }
        
        return Carbon::create($tahun, $bulan, 1);
    }


    /**
     * Nama lengkap dengan gelar
     */
    public function getNamaLengkapAttribute()
    {
        return $this->fullname . ($this->gelar ? ', ' . $this->gelar : '');
    }

    /**
     * Pangkat lengkap dengan golongan
     */
    public function getPangkatLengkapAttribute()
    {
        return $this->pangkat . ' (' . $this->golongan . ')';
    }

    /**
     * Masa Kerja Golongan (untuk SK)
     * = TMT Pangkat - TMT CPNS (tanpa pembulatan)
     */
    public function getMkgGolonganAttribute()
    {
        if (!$this->tmt_cpns || !$this->tmt_pangkat_terakhir) {
            return '0 Tahun 0 Bulan';
        }
        
        $diff = $this->tmt_cpns->diff($this->tmt_pangkat_terakhir);
        return $diff->y . ' Tahun ' . $diff->m . ' Bulan';
    }

    /**
     * Siklus KGB: Golongan II = ganjil, Golongan III = genap
     */
    public function getSiklusKgbAttribute()
    {
        if (str_starts_with($this->golongan, 'II')) {
            return 1; // ganjil
        }
        return 2; // genap
    }

    /**
     * TMT KGB Berikutnya
     * - Jika sudah pernah KGB: TMT KGB terakhir + 2 tahun
     * - Jika belum: TMT CPNS + siklus (genap/ganjil)
     */
    public function getTmtKgbBerikutnyaAttribute()
    {
        // Jika sudah pernah KGB
        if ($this->tmt_kgb_terakhir) {
            return $this->tmt_kgb_terakhir->copy()->addYears(2);
        }

        $tmtCpns = $this->tmt_cpns;
        if (!$tmtCpns) {
            return null;
        }

        $siklus = $this->siklus_kgb;
        $tahunSekarang = Carbon::now()->year;
        $tahunCpns = $tmtCpns->year;

        $selisihTahun = $tahunSekarang - $tahunCpns;
        $kelipatan = ceil($selisihTahun / $siklus) * $siklus;
        $tahunKgb = $tahunCpns + $kelipatan;

        return Carbon::create($tahunKgb, $tmtCpns->month, 1);
    }

    /**
     * MKG Efektif untuk Gaji (dari tabel ref_gaji_pokok)
     * - Golongan II: ganjil (1,3,5,...)
     * - Golongan III: genap (0,2,4,6,...)
     */
    public function getMkgEfektifAttribute()
    {
        $tmtCpns = $this->tmt_cpns;
        $tmtKgb = $this->tmt_kgb_berikutnya;
        
        if (!$tmtCpns || !$tmtKgb) {
            return 0;
        }

        $mkg = $tmtKgb->diffInYears($tmtCpns);

        if (str_starts_with($this->golongan, 'II')) {
            // Bulatkan ke ganjil terdekat ke bawah
            return $mkg - ($mkg % 2) + 1;
        }
        
        // Bulatkan ke genap terdekat ke bawah
        return $mkg - ($mkg % 2);
    }

    /**
     * Gaji Pokok Baru (dari tabel ref_gaji_pokok)
     */
    public function getGajiPokokBaruAttribute()
    {
        $gaji = RefGajiPokok::where('golongan', $this->golongan)
            ->where('mkg', $this->mkg_efektif)
            ->first();

        return $gaji->nominal_gaji ?? 0;
    }
}