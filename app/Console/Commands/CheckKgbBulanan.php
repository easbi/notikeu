<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\KgbPengurusan;
use App\Models\KgbLog;
use App\Models\RefGajiPokok;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckKgbBulanan extends Command
{
    protected $signature = 'kgb:check-bulanan 
                            {--bulan= : Bulan yang dicek (default: bulan ini + 2)} 
                            {--tahun= : Tahun yang dicek (default: tahun ini)} 
                            {--dry-run : Hanya tampilkan hasil, tidak simpan ke database}';

    protected $description = 'Cek pegawai yang TMT KGB-nya jatuh pada bulan+2 dan buat pengurusan SK KGB';

    public function handle()
    {
        $this->info('========================================');
        $this->info('🔄 PENGECEKAN KGB BULANAN');
        $this->info('========================================');
        $this->newLine();

        // ============ 1. TENTUKAN BULAN TARGET ============
        $bulan = $this->option('bulan') ?? Carbon::now()->month;
        $tahun = $this->option('tahun') ?? Carbon::now()->year;
        
        $tanggalCek = Carbon::create($tahun, $bulan, 1);
        $bulanTarget = $tanggalCek->copy()->addMonths(2);
        
        $this->info('📅 Bulan Target: ' . $bulanTarget->format('F Y'));
        $this->newLine();

        // ============ 2. CEK PEGAWAI ============
        $this->info('🔍 Mencari pegawai yang TMT KGB-nya jatuh pada ' . $bulanTarget->format('F Y') . '...');
        
        $pegawais = Pegawai::where('status_aktif', true)
            ->whereNotNull('golongan')
            ->get()
            ->filter(function ($pegawai) use ($bulanTarget) {
                $tmtKgb = $pegawai->tmt_kgb_berikutnya;
                if (!$tmtKgb) return false;
                
                return $tmtKgb->month == $bulanTarget->month && 
                       $tmtKgb->year == $bulanTarget->year;
            })
            ->values();

        if ($pegawais->isEmpty()) {
            $this->warn('❌ Tidak ada pegawai yang naik KGB pada ' . $bulanTarget->format('F Y'));
            $this->newLine();
            $this->info('✅ Selesai!');
            return 0;
        }

        $this->info('✅ Ditemukan ' . $pegawais->count() . ' pegawai:');
        
        foreach ($pegawais as $pegawai) {
            $this->line("   - {$pegawai->fullname} (NIP: {$pegawai->nip})");
            $this->line("     TMT KGB: " . $pegawai->tmt_kgb_berikutnya->format('d-m-Y'));
            $this->line("     Golongan: {$pegawai->golongan}, MKG Efektif: {$pegawai->mkg_efektif}");
        }
        $this->newLine();

        // ============ 3. DRY-RUN ============
        if ($this->option('dry-run')) {
            $this->warn('⚠️ MODE DRY-RUN: Data tidak disimpan ke database');
            $this->info('✅ Selesai!');
            return 0;
        }

        // ============ 4. PROSES PENGURUSAN ============
        $this->info('📝 Membuat pengurusan SK KGB...');
        
        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        foreach ($pegawais as $pegawai) {
            try {
                DB::transaction(function () use ($pegawai, $bulanTarget, &$successCount, &$skipCount, &$errorCount) {
                    
                    // Cek duplikasi
                    $existing = KgbPengurusan::where('pegawai_id', $pegawai->id)
                        ->whereYear('tmt_kgb_baru', $bulanTarget->year)
                        ->whereMonth('tmt_kgb_baru', $bulanTarget->month)
                        ->whereIn('status', ['pending', 'proses'])
                        ->first();

                    if ($existing) {
                        $this->warn("   ⚠️ {$pegawai->fullname} sudah ada pengurusan (status: {$existing->status})");
                        $skipCount++;
                        return;
                    }

                    // Ambil riwayat pangkat dan gaji terakhir
                    $riwayatPangkat = $pegawai->riwayatPangkat->first();
                    $riwayatGaji = $pegawai->riwayatGaji->first();
                    
                    if (!$riwayatPangkat || !$riwayatGaji) {
                        $this->error("   ❌ {$pegawai->fullname} tidak memiliki riwayat pangkat/gaji");
                        $errorCount++;
                        return;
                    }

                    $tmtKgbLama = $pegawai->tmt_kgb_terakhir;
                    $tmtKgbBaru = $pegawai->tmt_kgb_berikutnya;
                    $tmtGajiLama = $pegawai->tmt_gaji_terakhir;
                    $tmtGajiBaru = $tmtKgbBaru;
                    $tmtKgbBerikutnya = $tmtKgbBaru->copy()->addYears(2);
                    
                    $gajiLama = $pegawai->gaji_pokok_saat_ini ?? 0;
                    $gajiBaru = $pegawai->gaji_pokok_baru ?? 0;
                    
                    if ($gajiBaru == 0) {
                        $refGaji = RefGajiPokok::where('golongan', $pegawai->golongan)
                            ->where('mkg', $pegawai->mkg_efektif)
                            ->first();
                        $gajiBaru = $refGaji->nominal_gaji ?? 0;
                    }

                    // Buat pengurusan baru
                    $pengurusan = KgbPengurusan::create([
                        'pegawai_id' => $pegawai->id,
                        'nip' => $pegawai->nip,
                        'nama' => $pegawai->fullname,
                        'golongan' => $pegawai->golongan,
                        'pangkat' => $pegawai->pangkat,
                        'jabatan' => $pegawai->jabatan ?? '-',
                        'instansi' => $pegawai->organisasi ?? 'BPS Kota Padang Panjang',
                        'kode_instansi' => $pegawai->kode_instansi ?? '13741',
                        
                        'tmt_kgb_lama' => $tmtKgbLama,
                        'tmt_kgb_baru' => $tmtKgbBaru,
                        'tmt_gaji_lama' => $tmtGajiLama,
                        'tmt_gaji_baru' => $tmtGajiBaru,
                        'tmt_kgb_berikutnya' => $tmtKgbBerikutnya,
                        
                        'gaji_pokok_lama' => $gajiLama,
                        'gaji_pokok_baru' => $gajiBaru,
                        'dasar_peraturan' => 'PP 5/2024',
                        
                        'masa_kerja_golongan' => $pegawai->mkg_golongan,
                        'masa_kerja_kgb' => $pegawai->mkg_golongan,
                        
                        'sk_pangkat_nomor' => $riwayatPangkat->nomor_sk ?? '-',
                        'sk_pangkat_tanggal' => $riwayatPangkat->tanggal_sk ?? now(),
                        'sk_pangkat_pejabat' => $riwayatPangkat->pejabat_penetap ?? '-',
                        'sk_pangkat_tmt_gaji' => $riwayatGaji->tmt_berlaku ?? $tmtKgbBaru,
                        'sk_pangkat_masa_kerja' => $pegawai->mkg_golongan,
                        
                        'status' => 'pending',
                        'tanggal_pengajuan' => Carbon::now(),
                    ]);

                    // Log aktivitas
                    KgbLog::create([
                        'pengurusan_sk_kgb_id' => $pengurusan->id,
                        'pegawai_id' => $pegawai->id,
                        'nip' => $pegawai->nip,
                        'nama' => $pegawai->fullname,
                        'aktivitas' => 'pengajuan',
                        'deskripsi' => 'Pengajuan KGB otomatis dari sistem untuk TMT ' . $tmtKgbBaru->format('d-m-Y'),
                        'dilakukan_oleh' => 1,
                        'ip_address' => '127.0.0.1',
                        'waktu' => Carbon::now()
                    ]);

                    $this->info("   ✓ Pengurusan dibuat untuk {$pegawai->fullname}");
                    $successCount++;
                });
            } catch (\Exception $e) {
                $this->error("   ❌ Error untuk {$pegawai->fullname}: " . $e->getMessage());
                $errorCount++;
                Log::channel('kgb')->error("Gagal buat pengurusan KGB untuk NIP {$pegawai->nip}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // ============ 5. STATISTIK ============
        $this->newLine();
        $this->info('========================================');
        $this->info('📊 STATISTIK');
        $this->info('========================================');
        $this->info("✅ Berhasil: {$successCount}");
        $this->info("⚠️  Skip (duplikat): {$skipCount}");
        $this->info("❌ Error: {$errorCount}");
        $this->newLine();

        // ============ 6. NOTIFIKASI WHATSAPP ============
        if ($successCount > 0) {
            $this->info('📧 Mengirim notifikasi ke operator...');
            $this->kirimNotifikasiWhatsApp($pegawais, $bulanTarget, $successCount);
        }

        // ============ 7. LOG ============
        Log::channel('kgb')->info("Cron KGB selesai", [
            'bulan_target' => $bulanTarget->format('Y-m'),
            'total_pegawai' => $pegawais->count(),
            'success' => $successCount,
            'skip' => $skipCount,
            'error' => $errorCount,
            'daftar_nip' => $pegawais->pluck('nip')->toArray()
        ]);

        $this->info('✅ Selesai!');
        return 0;
    }

    /**
     * Kirim notifikasi WhatsApp ke operator
     */
    protected function kirimNotifikasiWhatsApp($pegawais, $bulanTarget, $successCount)
    {
        $today = now();
        $daftarNama = $pegawais->pluck('fullname')->implode("\n• ");
        
        $pesan = "*Morin : Peringatan Pengurusan Kenaikan Gaji Berkala (KGB)*\n\n";
        $pesan .= "Saat ini tanggal {$today->translatedFormat('d F Y')}.\n";
        $pesan .= "Terdapat *{$successCount} pegawai* yang akan naik KGB pada bulan *{$bulanTarget->format('F Y')}*.\n\n";
        $pesan .= "*Daftar Pegawai:*\n";
        $pesan .= "• {$daftarNama}\n\n";
        $pesan .= "Mohon segera diproses pengurusan SK KGB-nya di aplikasi.\n\n";
        $pesan .= "_Pesan ini dikirimkan oleh Aplikasi *Morin (Money Reminder)* sebagai Aplikasi Notifikasi Keuangan BPS Kota Padang Panjang pada {$today->format('d-m-Y H:i:s')} WIB_";
        
        $this->sendWhatsAppMessage($pesan);
        $this->info("   📨 Notifikasi WhatsApp dikirim ke operator");
    }

    /**
     * Kirim pesan WhatsApp
     */
    protected function sendWhatsAppMessage($message)
    {
        $responsiblePhone = $this->getResponsiblePhoneNumber();
        
        if (empty($responsiblePhone)) {
            Log::channel('kgb')->warning('No responsible phone number found for KGB notification.');
            return;
        }
        
        $details = [
            'message' => $message,
            'no_hp' => $responsiblePhone,
            'id' => null,
        ];
        
        $queue = new \App\Jobs\SendNotification($details);
        dispatch($queue);
    }

    /**
     * Ambil nomor HP penanggung jawab
     */
    protected function getResponsiblePhoneNumber()
    {
        $user = \App\Models\User::find(1);
        if ($user && !empty($user->no_hp)) {
            return $user->no_hp;
        }
        
        $pegawai = \App\Models\Pegawai::where('id', 1)->first();
        if ($pegawai && !empty($pegawai->no_hp)) {
            return $pegawai->no_hp;
        }
        
        return 'gagal dapatkan no hape';
    }
}