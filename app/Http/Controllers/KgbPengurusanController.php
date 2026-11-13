<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\KgbPengurusan;
use App\Models\KgbRiwayatGaji;
use App\Models\KgbRiwayatPangkat;
use App\Models\KgbLog;
use App\Models\RefGajiPokok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class KgbPengurusanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard Pengurusan KGB
     */
    public function index()
    {
        $user = Auth::user();
        $pegawaiUser = Pegawai::where('nip', $user->nip)->first();

        $isOperator = $pegawaiUser && in_array($pegawaiUser->id, [1, 2, 4, 15]);

        if ($isOperator) {
            $onGoing = KgbPengurusan::onGoing()
                ->with(['pegawai', 'diprosesOleh'])
                ->orderBy('created_at', 'desc')
                ->get();

            $riwayat = KgbPengurusan::selesai()
                ->with(['pegawai', 'diprosesOleh'])
                ->orderBy('tanggal_selesai', 'desc')
                ->limit(20)
                ->get();

            $stats = [
                'total_ongoing' => KgbPengurusan::onGoing()->count(),
                'total_selesai' => KgbPengurusan::selesai()->count(),
                'total_pegawai' => Pegawai::where('status_aktif', true)->count(),
                'total_bulan_ini' => KgbPengurusan::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
            ];
        } else {
            $onGoing = collect([]);
            $riwayat = KgbPengurusan::selesai()
                ->where('pegawai_id', $pegawaiUser->id ?? 0)
                ->with(['pegawai', 'diprosesOleh'])
                ->orderBy('tanggal_selesai', 'desc')
                ->limit(20)
                ->get();

            $stats = [
                'total_ongoing' => 0,
                'total_selesai' => $riwayat->count(),
                'total_pegawai' => 1,
                'total_bulan_ini' => 0,
            ];
        }

        return view('kgb.index', compact('onGoing', 'riwayat', 'stats'));
    }

    /**
     * Detail pengurusan KGB
     */
    public function show($id)
    {
        $pengurusan = KgbPengurusan::with([
            'pegawai',
            'diprosesOleh',
            'logs.dilakukanOleh'
        ])->findOrFail($id);

        $pegawai = $pengurusan->pegawai;

        if ($pegawai) {
            // 1. Ambil data KGB terakhir dari riwayat gaji (jika ada)
            $riwayatKgbTerakhir = $pegawai->riwayatGaji->where('jenis', 'KGB')->first();

            // 2. Tentukan SK Terakhir (Bandingkan TMT Gaji vs TMT Pangkat)
            $riwayatGaji = $pegawai->riwayatGaji->first();
            $tmtPangkat = $pegawai->tmt_pangkat_terakhir;
            $tmtGaji = $pegawai->tmt_gaji_terakhir ?? ($riwayatGaji ? $riwayatGaji->tmt_berlaku : null);

            $gunakanGaji = false;
            if ($tmtGaji && $tmtPangkat) {
                $gunakanGaji = $tmtGaji > $tmtPangkat;
            } elseif ($tmtGaji) {
                $gunakanGaji = true;
            }

            $tglAcuanMKG = $gunakanGaji ? $tmtGaji : $tmtPangkat;

            // 3. OVERRIDE NILAI DARI DATABASE DENGAN HASIL HITUNG / DATA TERBARU
            $pengurusan->masa_kerja_golongan = $pegawai->hitungMasaKerjaSegaris($tglAcuanMKG);
            $pengurusan->masa_kerja_kgb = $pegawai->hitungMasaKerjaSegaris($pengurusan->tmt_kgb_baru);

            // OVERRIDE TMT KGB LAMA
            // Jika di database pengurusan masih NULL, tapi di riwayat gaji sudah ada
            if (!$pengurusan->tmt_kgb_lama && $riwayatKgbTerakhir) {
                $pengurusan->tmt_kgb_lama = $riwayatKgbTerakhir->tmt_berlaku;
            }
            // Jika di database masih NULL dan di tabel pegawai juga NULL, tapi riwayat gaji ada
            if (!$pengurusan->tmt_gaji_lama && $riwayatGaji) {
                $pengurusan->tmt_gaji_lama = $riwayatGaji->tmt_berlaku;
            }
        }

        return view('kgb.show', compact('pengurusan'));
    }

    /**
     * Form Proses KGB
     */
    public function prosesForm($id)
    {
        $pengurusan = KgbPengurusan::findOrFail($id);

        if ($pengurusan->status != 'pending') {
            return redirect()->route('kgb.index')->with('error', 'Pengurusan ini sudah diproses atau selesai.');
        }

        $pegawai = $pengurusan->pegawai;

        // =========================================================
        // 1. TENTUKAN SK TERAKHIR (BANDINGKAN TMT GAJI vs TMT PANGKAT)
        // =========================================================
        $riwayatPangkat = $pegawai->riwayatPangkat->first();
        $riwayatGaji = $pegawai->riwayatGaji->first();

        $tmtPangkat = $pegawai->tmt_pangkat_terakhir;
        $tmtGaji = $pegawai->tmt_gaji_terakhir ?? ($riwayatGaji ? $riwayatGaji->tmt_berlaku : null);

        $gunakanGaji = false;
        if ($tmtGaji && $tmtPangkat) {
            $gunakanGaji = $tmtGaji > $tmtPangkat; // Jika Gaji lebih baru dari Pangkat
        } elseif ($tmtGaji) {
            $gunakanGaji = true;
        }

        if ($gunakanGaji && $riwayatGaji) {
            // Pakai Data SK Gaji/KGB Terakhir
            $skTerakhir = [
                'nomor' => $riwayatGaji->nomor_sk,
                'tanggal' => $riwayatGaji->tanggal_sk,
                'pejabat' => $riwayatGaji->pejabat_penetap,
                'tmt' => $riwayatGaji->tmt_berlaku,
            ];
            $tglAcuanMKG = $riwayatGaji->tmt_berlaku;
        } else {
            // Pakai Data SK Pangkat Terakhir
            $skTerakhir = [
                'nomor' => $riwayatPangkat->nomor_sk ?? '-',
                'tanggal' => $riwayatPangkat->tanggal_sk ?? null,
                'pejabat' => $riwayatPangkat->pejabat_penetap ?? '-',
                'tmt' => $riwayatPangkat->tmt_mulai ?? null,
            ];
            $tglAcuanMKG = $pegawai->tmt_pangkat_terakhir;
        }

        // =========================================================
        // 2. HITUNG MASA KERJA (Berdasarkan TMT Acuan Terbaru)
        // =========================================================
        // Poin e: Hitung MKG s.d TMT SK Terakhir
        $masaKerjaGolongan = $pegawai->hitungMasaKerjaSegaris($tglAcuanMKG);

        // Poin 7: Hitung MKS s.d TMT KGB Baru
        $masaKerjaKGB = $pegawai->hitungMasaKerjaSegaris($pengurusan->tmt_kgb_baru);

        $data = [
            'pengurusan' => $pengurusan,
            'pegawai' => $pegawai,
            'gaji_lama' => $pegawai->gaji_pokok_saat_ini ?? 0,
            'gaji_baru' => $pegawai->gaji_pokok_baru ?? 0,
            'tmt_cpns' => $pegawai->tmt_cpns,
            'masa_kerja_golongan' => $masaKerjaGolongan,
            'masa_kerja_kgb' => $masaKerjaKGB,
            'tmt_kgb_berikutnya' => $pegawai->tmt_kgb_berikutnya,
            'sk_terakhir' => $skTerakhir, // Kirim variabel ini ke View
            'nomor_sk_saran' => $this->generateNomorSk($pengurusan),
            'pejabat_default' => 'Kepala Badan Pusat Statistik',
        ];

        return view('kgb.proses', $data);
    }

    /**
     * Proses KGB (Generate SK)
     */
    public function proses(Request $request, $id)
    {
        $pengurusan = KgbPengurusan::findOrFail($id);

        if ($pengurusan->status != 'pending') {
            return redirect()->route('kgb.index')
                ->with('error', 'Pengurusan ini sudah diproses.');
        }

        $request->validate([
            'nomor_sk' => 'required|string|max:50',
            'tanggal_sk' => 'required|date',
            'pejabat_penetap' => 'required|string|max:100',
            'nip_pejabat' => 'nullable|string|max:18',
            'gaji_pokok_lama' => 'required|numeric|min:0',
            'gaji_pokok_baru' => 'required|numeric|min:0',
            'masa_kerja_golongan' => 'required|string|max:20',
            'masa_kerja_kgb' => 'required|string|max:20',
            'dasar_peraturan' => 'required|string|max:50',
        ]);

        DB::transaction(function () use ($request, $pengurusan) {
            $pegawai = $pengurusan->pegawai;
            $dataLama = $pegawai->toArray();

            // Hitung masa kerja dari data pegawai
            $masaKerjaGolongan = $pegawai->hitungMasaKerjaSegaris($pegawai->tmt_pangkat_terakhir);
            $masaKerjaKGB = $pegawai->hitungMasaKerjaSegaris($pengurusan->tmt_kgb_baru);

            // Update pegawai
            $pegawai->tmt_kgb_terakhir = $pengurusan->tmt_kgb_baru;
            $pegawai->tmt_gaji_terakhir = $pengurusan->tmt_gaji_baru;
            $pegawai->gaji_pokok_saat_ini = $request->gaji_pokok_baru;
            $pegawai->save();

            // Riwayat gaji baru
            KgbRiwayatGaji::create([
                'pegawai_id' => $pegawai->id,
                'gaji_pokok' => $request->gaji_pokok_baru,
                'tmt_berlaku' => $pengurusan->tmt_gaji_baru,
                'jenis' => 'KGB',
                'nomor_sk' => $request->nomor_sk,
                'tanggal_sk' => $request->tanggal_sk,
                'pejabat_penetap' => $request->pejabat_penetap,
                'dasar_peraturan' => $request->dasar_peraturan,
            ]);

            // Update pengurusan dengan data yang sudah dihitung
            $pengurusan->update([
                'status' => 'selesai',
                'nomor_sk' => $request->nomor_sk,
                'tanggal_sk' => $request->tanggal_sk,
                'pejabat_penetap' => $request->pejabat_penetap,
                'nip_pejabat' => $request->nip_pejabat,
                'gaji_pokok_lama' => $request->gaji_pokok_lama,
                'gaji_pokok_baru' => $request->gaji_pokok_baru,
                'masa_kerja_golongan' => $request->masa_kerja_golongan, // Ambil dari form
                'masa_kerja_kgb' => $request->masa_kerja_kgb, // Ambil dari form
                'dasar_peraturan' => $request->dasar_peraturan,
                'tmt_gaji_baru' => $pengurusan->tmt_gaji_baru,
                'tanggal_selesai' => Carbon::now(),
                'diproses_oleh' => Auth::id(),
                'keterangan' => 'SK KGB selesai diproses',


                'sk_pangkat_nomor' => $request->sk_pangkat_nomor,
                'sk_pangkat_tanggal' => $request->sk_pangkat_tanggal,
                'sk_pangkat_pejabat' => $request->sk_pangkat_pejabat,
                'sk_pangkat_tmt_gaji' => $request->sk_pangkat_tmt_gaji,
                'sk_pangkat_masa_kerja' => $request->sk_pangkat_masa_kerja,
            ]);

            // Log
            KgbLog::create([
                'pengurusan_sk_kgb_id' => $pengurusan->id,
                'pegawai_id' => $pegawai->id,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->fullname,
                'aktivitas' => 'selesai',
                'deskripsi' => 'SK KGB selesai diproses dengan nomor: ' . $request->nomor_sk,
                'data_lama' => $dataLama,
                'data_baru' => $pegawai->toArray(),
                'dilakukan_oleh' => Auth::id(),
                'ip_address' => $request->ip(),
                'waktu' => Carbon::now()
            ]);
        });

        return redirect()->route('kgb.show', $pengurusan->id)
            ->with('success', '✅ SK KGB berhasil diproses!');
    }

    /**
     * Generate Word (.docx) dari template
     */
    public function generateWord($id)
    {
        $pengurusan = KgbPengurusan::with(['pegawai'])->findOrFail($id);

        if ($pengurusan->status != 'selesai') {
            return redirect()->route('kgb.index')
                ->with('error', 'SK belum selesai diproses.');
        }

        $templatePath = storage_path('app/templates/template_sk_kgb.docx');

        if (!file_exists($templatePath)) {
            return back()->with('error', 'Template SK KGB tidak ditemukan! Letakkan di storage/app/templates/template_sk_kgb.docx');
        }

        $templateProcessor = new TemplateProcessor($templatePath);
        $data = $this->generateSkContent($pengurusan);

        // Isi semua variabel
        $templateProcessor->setValue('nomor_naskah', $data['nomor_naskah']);
        $templateProcessor->setValue('tanggal_naskah', $data['tanggal_naskah']);
        $templateProcessor->setValue('sifat', $data['sifat']);
        $templateProcessor->setValue('hal', $data['hal']);

        $templateProcessor->setValue('nama_pegawai', $data['nama_pegawai']);
        $templateProcessor->setValue('nip', $data['nip']);
        $templateProcessor->setValue('pangkat_jabatan', $data['pangkat_jabatan']);
        $templateProcessor->setValue('instansi', $data['instansi']);
        $templateProcessor->setValue('gaji_pokok_lama', $data['gaji_pokok_lama']);

        $templateProcessor->setValue('pejabat_sk_lama', $data['pejabat_sk_lama']);
        $templateProcessor->setValue('tanggal_sk_lama', $data['tanggal_sk_lama']);
        $templateProcessor->setValue('nomor_sk_lama', $data['nomor_sk_lama']);
        $templateProcessor->setValue('tmt_gaji_lama', $data['tmt_gaji_lama']);
        $templateProcessor->setValue('masa_kerja_golongan', $data['masa_kerja_golongan']);

        $templateProcessor->setValue('gaji_pokok_baru', $data['gaji_pokok_baru']);
        $templateProcessor->setValue('dasar_peraturan', $data['dasar_peraturan']);
        $templateProcessor->setValue('masa_kerja_kgb', $data['masa_kerja_kgb']);
        $templateProcessor->setValue('golongan', $data['golongan']);
        $templateProcessor->setValue('tmt_kgb_baru', $data['tmt_kgb_baru']);
        $templateProcessor->setValue('tmt_kgb_berikutnya', $data['tmt_kgb_berikutnya']);

        $templateProcessor->setValue('jabatan_pengirim', $data['jabatan_pengirim']);
        $templateProcessor->setValue('nama_pengirim', $data['nama_pengirim']);

        // Nama file
        $fileName = "sk_kgb_" . strtolower(str_replace(' ', '_', $pengurusan->nama)) . "_" . date('Y') . ".docx";
        $tempPath = storage_path("app/temp/{$fileName}");

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }

        $templateProcessor->saveAs($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Preview SK KGB (tampilan HTML)
     */
    public function preview($id)
    {
        $pengurusan = KgbPengurusan::with(['pegawai'])->findOrFail($id);

        if ($pengurusan->status != 'selesai') {
            return redirect()->route('kgb.index')
                ->with('error', 'SK belum selesai diproses.');
        }

        $data = $this->generateSkContent($pengurusan);
        return view('kgb.preview_sk', compact('pengurusan', 'data'));
    }

    /**
     * Update data pegawai dari form KGB
     */
    public function updatePegawai(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $request->validate([
            'golongan' => 'required|string|max:10',
            'pangkat' => 'required|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'tmt_pangkat_terakhir' => 'required|date',
            'gaji_pokok_saat_ini' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $pegawai) {
            $berubah = (
                $pegawai->golongan != $request->golongan ||
                $pegawai->pangkat != $request->pangkat ||
                $pegawai->jabatan != $request->jabatan ||
                $pegawai->tmt_pangkat_terakhir != $request->tmt_pangkat_terakhir ||
                $pegawai->gaji_pokok_saat_ini != $request->gaji_pokok_saat_ini
            );

            if ($berubah) {
                KgbRiwayatPangkat::create([
                    'pegawai_id' => $pegawai->id,
                    'golongan' => $request->golongan,
                    'pangkat' => $request->pangkat,
                    'jabatan' => $request->jabatan ?? $pegawai->jabatan,
                    'tmt_mulai' => $request->tmt_pangkat_terakhir,
                    'nomor_sk' => $request->nomor_sk_pangkat ?? '-',
                    'tanggal_sk' => $request->tanggal_sk_pangkat ?? now(),
                    'pejabat_penetap' => $request->pejabat_penetap ?? '-',
                    'masa_kerja_golongan' => $pegawai->mkg_golongan,
                ]);

                KgbRiwayatGaji::create([
                    'pegawai_id' => $pegawai->id,
                    'gaji_pokok' => $request->gaji_pokok_saat_ini,
                    'tmt_berlaku' => $request->tmt_pangkat_terakhir,
                    'jenis' => 'PANGKAT',
                    'nomor_sk' => $request->nomor_sk_pangkat ?? '-',
                    'tanggal_sk' => $request->tanggal_sk_pangkat ?? now(),
                    'pejabat_penetap' => $request->pejabat_penetap ?? '-',
                    'dasar_peraturan' => 'PP 5/2024',
                ]);

                $pegawai->update([
                    'golongan' => $request->golongan,
                    'pangkat' => $request->pangkat,
                    'jabatan' => $request->jabatan,
                    'tmt_pangkat_terakhir' => $request->tmt_pangkat_terakhir,
                    'gaji_pokok_saat_ini' => $request->gaji_pokok_saat_ini,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data pegawai berhasil diperbarui!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada perubahan data.'
            ]);
        });
    }

    /**
     * Batalkan pengurusan KGB
     */
    public function batal($id)
    {
        $pengurusan = KgbPengurusan::findOrFail($id);

        if ($pengurusan->status != 'pending') {
            return redirect()->route('kgb.index')
                ->with('error', 'Pengurusan ini tidak bisa dibatalkan.');
        }

        $pengurusan->update([
            'status' => 'dibatalkan',
            'keterangan' => 'Dibatalkan oleh operator',
        ]);

        KgbLog::create([
            'pengurusan_sk_kgb_id' => $pengurusan->id,
            'pegawai_id' => $pengurusan->pegawai_id,
            'nip' => $pengurusan->nip,
            'nama' => $pengurusan->nama,
            'aktivitas' => 'batal',
            'deskripsi' => 'Pengurusan KGB dibatalkan',
            'dilakukan_oleh' => Auth::id(),
            'ip_address' => request()->ip(),
            'waktu' => Carbon::now()
        ]);

        return redirect()->route('kgb.index')
            ->with('success', 'Pengurusan KGB berhasil dibatalkan.');
    }

    // ============================================================
    // HELPER FUNCTIONS
    // ============================================================

    /**
     * Generate Nomor SK Otomatis
     */
    protected function generateNomorSk($pengurusan)
    {
        $tahun = Carbon::now()->year;
        $bulan = Carbon::now()->format('m');
        $tanggal = Carbon::now()->format('d');

        $urutan = KgbPengurusan::whereYear('created_at', $tahun)
            ->where('status', 'selesai')
            ->count() + 1;

        $urutan = str_pad($urutan, 4, '0', STR_PAD_LEFT);
        $kodeInstansi = $pengurusan->kode_instansi ?? '13741';

        return "{$tanggal}{$bulan}{$tahun}/{$kodeInstansi}/KPG-{$urutan} Tahun {$tahun}";
    }
    /**
     * Generate isi SK KGB untuk template
     */
    protected function generateSkContent($pengurusan)
    {
        $bulanIndo = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $formatTanggal = function ($tanggal) use ($bulanIndo) {
            if (!$tanggal)
                return '';
            $carbon = Carbon::parse($tanggal);
            return $carbon->format('d') . ' ' . $bulanIndo[(int) $carbon->format('m')] . ' ' . $carbon->format('Y');
        };

        return [
            'nomor_naskah' => $pengurusan->nomor_sk ?? '',
            'tanggal_naskah' => $formatTanggal($pengurusan->tanggal_sk),
            'sifat' => 'Penting',
            'hal' => 'Kenaikan Gaji Berkala',
            'nama_pegawai' => $pengurusan->nama,
            'nip' => $pengurusan->nip,
            'pangkat_jabatan' => $pengurusan->pangkat . ' (' . $pengurusan->golongan . ') / ' . $pengurusan->jabatan,
            'instansi' => $pengurusan->instansi,
            'gaji_pokok_lama' => 'Rp ' . number_format($pengurusan->gaji_pokok_lama, 0, ',', '.') . ',-',

            // =======================================================
            // LANGSUNG AMBIL DARI TABEL PENGURUSAN (Hasil input form)
            // =======================================================
            'pejabat_sk_lama' => $pengurusan->sk_pangkat_pejabat ?? '-',
            'tanggal_sk_lama' => $formatTanggal($pengurusan->sk_pangkat_tanggal),
            'nomor_sk_lama' => $pengurusan->sk_pangkat_nomor ?? '-',
            'tmt_gaji_lama' => $formatTanggal($pengurusan->sk_pangkat_tmt_gaji),
            'masa_kerja_golongan' => $pengurusan->sk_pangkat_masa_kerja ?? '-',

            'gaji_pokok_baru' => 'Rp ' . number_format($pengurusan->gaji_pokok_baru, 0, ',', '.') . ',-',
            'dasar_peraturan' => $pengurusan->dasar_peraturan ?? '',
            'masa_kerja_kgb' => $pengurusan->masa_kerja_kgb ?? '-',
            'golongan' => $pengurusan->golongan,
            'tmt_kgb_baru' => $formatTanggal($pengurusan->tmt_kgb_baru),
            'tmt_kgb_berikutnya' => $formatTanggal($pengurusan->tmt_kgb_berikutnya),
            'jabatan_pengirim' => $pengurusan->pejabat_penetap ?? '',
            'nama_pengirim' => $pengurusan->pejabat_penetap ?? '',
        ];
    }

    public function batalProses($id)
    {
        $pengurusan = KgbPengurusan::findOrFail($id);

        if ($pengurusan->status != 'selesai') {
            return back()->with('error', 'Hanya pengurusan yang sudah selesai yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($pengurusan) {
            $pegawai = $pengurusan->pegawai;

            // 1. Hapus riwayat gaji yang tercipta dari SK KGB ini
            KgbRiwayatGaji::where('pegawai_id', $pegawai->id)
                ->where('nomor_sk', $pengurusan->nomor_sk)
                ->where('jenis', 'KGB')
                ->delete();

            // 2. Kembalikan data pegawai ke kondisi sebelum KGB
            $pegawai->tmt_kgb_terakhir = $pengurusan->tmt_kgb_lama;
            $pegawai->tmt_gaji_terakhir = $pengurusan->tmt_gaji_lama;
            $pegawai->gaji_pokok_saat_ini = $pengurusan->gaji_pokok_lama;
            $pegawai->save();

            // 3. Kembalikan status pengurusan ke pending & kosongkan data SK
            $pengurusan->update([
                'status' => 'pending',
                'nomor_sk' => null,
                'tanggal_sk' => null,
                'pejabat_penetap' => null,
                'nip_pejabat' => null,
                'tanggal_selesai' => null,
                'keterangan' => 'Proses dibatalkan, dikembalikan ke pending',
                'diproses_oleh' => null
            ]);

            // 4. Log aktivitas
            KgbLog::create([
                'pengurusan_sk_kgb_id' => $pengurusan->id,
                'pegawai_id' => $pegawai->id,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->fullname,
                'aktivitas' => 'batal_proses',
                'deskripsi' => 'Proses KGB dibatalkan oleh operator',
                'dilakukan_oleh' => Auth::id(),
                'ip_address' => request()->ip(),
                'waktu' => Carbon::now()
            ]);
        });

        return back()->with('success', 'Proses KGB berhasil dibatalkan dan dikembalikan ke status Pending.');
    }
}
