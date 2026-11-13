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
     * Siklus KGB: Golongan II = ganjil, Golongan III = genap
     */
    public function getSiklusKgbAttribute()
    {
        // preg_match memastikan yang dicek benar-benar II/ (II/a, II/b, dll), bukan III
        if (preg_match('/^II\//', $this->golongan)) {
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
            return Carbon::parse($this->tmt_kgb_terakhir)->addYears(2);
        }

        $tmtCpns = $this->tmt_cpns;
        if (!$tmtCpns) {
            return null;
        }

        $siklus = $this->siklus_kgb; // 2 untuk III, 1 untuk II
        $tahunCpns = $tmtCpns->year;
        $bulanCpns = $tmtCpns->month;
        $tahunSekarang = Carbon::now()->year;

        // Cari kelipatan siklus berikutnya yang > tahun sekarang
        $tahunKgb = $tahunCpns;
        while ($tahunKgb <= $tahunSekarang) {
            $tahunKgb += $siklus;
        }

        return Carbon::create($tahunKgb, $bulanCpns, 1);
    }

    public function getMkgGolonganAttribute()
    {
        if (!$this->tmt_cpns || !$this->tmt_pangkat_terakhir) {
            return '0 Tahun 0 Bulan';
        }

        $diff = $this->tmt_cpns->diff($this->tmt_pangkat_terakhir);
        $tahun = $diff->y;
        $bulan = $diff->m;

        $riwayats = \App\Models\KgbRiwayatPangkat::where('pegawai_id', $this->id)
            ->orderBy('tmt_mulai', 'asc')
            ->get();

        if ($riwayats->isNotEmpty()) {
            $golAwal = $riwayats->first()->golongan;

            // 1. Modal awal 3 tahun
            if (in_array($golAwal, ['I/b', 'I/c', 'I/d', 'II/b', 'II/c', 'II/d'])) {
                $tahun += 3;
            }

            // 2. Potong Segaris
            $roman = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4];
            $prev = $roman[explode('/', $riwayats->first()->golongan)[0]] ?? 0;

            foreach ($riwayats as $r) {
                $curr = $roman[explode('/', $r->golongan)[0]] ?? 0;
                if ($prev == 1 && $curr == 2) {
                    $tahun -= 6; // I ke II potong 6
                } elseif ($prev == 2 && $curr == 3) {
                    $tahun -= 5; // II ke III potong 5
                }
                // III ke IV tidak dipotong
                $prev = $curr;
            }
        }

        $tahun = max(0, $tahun);
        return $tahun . ' Tahun ' . $bulan . ' Bulan';
    }

    public function getMkgEfektifAttribute()
    {
        $tmtKgb = $this->getTmtKgbBerikutnyaAttribute();
        $tmtCpns = $this->tmt_cpns;

        if (!$tmtKgb || !$tmtCpns) {
            return 0;
        }

        // Masa Kerja Seluruhnya (MKS)
        $mkg = $tmtCpns->diffInYears($tmtKgb);

        $riwayats = \App\Models\KgbRiwayatPangkat::where('pegawai_id', $this->id)
            ->orderBy('tmt_mulai', 'asc')
            ->get();

        if ($riwayats->isNotEmpty()) {
            $golAwal = $riwayats->first()->golongan;

            // 1. Modal awal 3 tahun
            if (in_array($golAwal, ['I/b', 'I/c', 'I/d', 'II/b', 'II/c', 'II/d'])) {
                $mkg += 3;
            }

            // 2. Potong Segaris (Pakai Array)
            $roman = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4];
            $prev = $roman[explode('/', $riwayats->first()->golongan)[0]] ?? 0;

            foreach ($riwayats as $r) {
                $curr = $roman[explode('/', $r->golongan)[0]] ?? 0;
                if ($prev == 1 && $curr == 2) {
                    $mkg -= 6;
                } elseif ($prev == 2 && $curr == 3) {
                    $mkg -= 5;
                }
                $prev = $curr;
            }
        }

        $mkg = max($mkg, 0);

        // Pembulatan (Pecah string pakai explode agar akurat)
        $golSekarang = explode('/', $this->golongan)[0];

        if (in_array($golSekarang, ['I', 'II'])) {
            if ($mkg % 2 == 0)
                $mkg--; // Ganjil
        } else {
            if ($mkg % 2 != 0)
                $mkg--; // Genap
        }

        return max($mkg, 0);
    }

    /**
     * Gaji Pokok Baru (dari tabel ref_gaji_pokok)
     * Jika MKG tidak ditemukan, turunkan -1 tahun sampai ditemukan
     */
    public function getGajiPokokBaruAttribute()
    {
        $jenis = $this->jenis_pegawai ?? 'PNS';
        $mkg = $this->mkg_efektif;
        $golongan = $this->golongan;

        // Cari gaji dengan MKG yang tersedia, turunkan -1 jika tidak ditemukan
        while ($mkg >= 0) {
            $gaji = RefGajiPokok::where('jenis_pegawai', $jenis)
                ->where('golongan', $golongan)
                ->where('mkg', $mkg)
                ->first();

            if ($gaji) {
                return $gaji->nominal_gaji;
            }

            // Jika tidak ditemukan, turunkan 1 tahun
            $mkg--;
        }

        return 0; // Jika tidak ditemukan sama sekali
    }
        public function hitungMasaKerjaSegaris($tmtAkhir)
    {
        if (!$this->tmt_cpns || !$tmtAkhir) {
            return '0 Tahun 0 Bulan';
        }

        $diff = $this->tmt_cpns->diff($tmtAkhir);
        $tahun = $diff->y;
        $bulan = $diff->m;

        $riwayats = \App\Models\KgbRiwayatPangkat::where('pegawai_id', $this->id)
            ->orderBy('tmt_mulai', 'asc')
            ->get();

        if ($riwayats->isNotEmpty()) {
            $golAwal = $riwayats->first()->golongan;
            $romanAwal = explode('/', $golAwal)[0] ?? '';

            // JIKA MULAI DARI GOLONGAN II
            if ($romanAwal == 'II') {
                $tahun -= 5; // Kurangi 5 karena lompat ke Gol III/IV

                // Jika mulai dari II/b, II/c, II/d, tambah modal 3 tahun
                if (in_array($golAwal, ['II/b', 'II/c', 'II/d'])) {
                    $tahun += 3;
                }
            }
            // JIKA MULAI DARI GOLONGAN I
            elseif ($romanAwal == 'I') {
                $tahun -= 11; // I ke II (-6), II ke III (-5) = -11
                if (in_array($golAwal, ['I/b', 'I/c', 'I/d'])) {
                    $tahun += 3;
                }
            }
            // Jika mulai dari Gol III, tidak ada pengurangan/tambahan
        }

        $tahun = max(0, $tahun);
        return $tahun . ' Tahun ' . $bulan . ' Bulan';
    }
}
