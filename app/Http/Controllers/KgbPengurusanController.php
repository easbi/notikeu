<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\KgbPengurusan;
use App\Models\KgbRiwayatGaji;
use App\Models\KgbLog;
use App\Models\RefGajiPokok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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

        // Cek apakah user adalah admin/operator
        $isOperator = $pegawaiUser && in_array($pegawaiUser->id, [1, 2, 4, 15]);

        if ($isOperator) {
            // Operator/Admin lihat semua
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
            ];
        } else {
            // User biasa hanya lihat riwayat KGB sendiri
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

        return view('kgb.show', compact('pengurusan'));
    }

    /**
     * Form Proses KGB
     */
    public function prosesForm($id)
    {
        $pengurusan = KgbPengurusan::findOrFail($id);

        // Validasi status
        if ($pengurusan->status != 'pending') {
            return redirect()->route('kgb.index')
                ->with('error', 'Pengurusan ini sudah diproses atau selesai.');
        }

        $pegawai = $pengurusan->pegawai;

        // Ambil data dari pegawai
        $gajiLama = $pegawai->gaji_pokok_saat_ini ?? 0;
        $gajiBaru = $pegawai->gaji_pokok_baru ?? 0;
        $tmtCpns = $pegawai->tmt_cpns;
        $mkgGolongan = $pegawai->mkg_golongan;
        $tmtKgbBerikutnya = $pegawai->tmt_kgb_berikutnya;

        // Ambil riwayat pangkat terakhir
        $riwayatPangkatTerakhir = $pegawai->riwayatPangkat->first();

        // Data untuk form
        $data = [
            'pengurusan' => $pengurusan,
            'pegawai' => $pegawai,
            'gaji_lama' => $gajiLama,
            'gaji_baru' => $gajiBaru,
            'tmt_cpns' => $tmtCpns,
            'mkg_golongan' => $mkgGolongan,
            'tmt_kgb_berikutnya' => $tmtKgbBerikutnya,
            'riwayat_pangkat' => $riwayatPangkatTerakhir,
            'nomor_sk_saran' => $pengurusan->generateNomorSk(),
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

            // Simpan data lama untuk log
            $dataLama = $pegawai->toArray();

            // 1. Update TMT KGB di pegawai
            $pegawai->tmt_kgb_terakhir = $pengurusan->tmt_kgb_baru;
            $pegawai->tmt_gaji_terakhir = $pengurusan->tmt_gaji_baru;
            $pegawai->gaji_pokok_saat_ini = $request->gaji_pokok_baru;
            $pegawai->save();

            // 2. Simpan riwayat gaji baru
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

            // 3. Update pengurusan
            $pengurusan->update([
                'status' => 'selesai',
                'nomor_sk' => $request->nomor_sk,
                'tanggal_sk' => $request->tanggal_sk,
                'pejabat_penetap' => $request->pejabat_penetap,
                'nip_pejabat' => $request->nip_pejabat,
                'gaji_pokok_lama' => $request->gaji_pokok_lama,
                'gaji_pokok_baru' => $request->gaji_pokok_baru,
                'masa_kerja_golongan' => $request->masa_kerja_golongan,
                'masa_kerja_kgb' => $request->masa_kerja_kgb,
                'dasar_peraturan' => $request->dasar_peraturan,
                'tanggal_selesai' => Carbon::now(),
                'diproses_oleh' => Auth::id(),
                'keterangan' => 'SK KGB selesai diproses'
            ]);

            // 4. Log aktivitas
            KgbLog::create([
                'kgb_pengurusan_id' => $pengurusan->id,
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
     * Generate PDF SK KGB
     */
    public function generatePdf($id)
    {
        $pengurusan = KgbPengurusan::with(['pegawai'])->findOrFail($id);

        if ($pengurusan->status != 'selesai') {
            return redirect()->route('kgb.index')
                ->with('error', 'SK belum selesai diproses.');
        }

        $pdf = Pdf::loadView('kgb.pdf_sk', compact('pengurusan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("SK_KGB_{$pengurusan->nip}_{$pengurusan->nomor_sk}.pdf");
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

        return view('kgb.preview_sk', compact('pengurusan'));
    }

    /**
     * Update data pegawai dari form KGB (jika ada perubahan pangkat)
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
            // Cek apakah ada perubahan
            $berubah = (
                $pegawai->golongan != $request->golongan ||
                $pegawai->pangkat != $request->pangkat ||
                $pegawai->jabatan != $request->jabatan ||
                $pegawai->tmt_pangkat_terakhir != $request->tmt_pangkat_terakhir ||
                $pegawai->gaji_pokok_saat_ini != $request->gaji_pokok_saat_ini
            );

            if ($berubah) {
                // Simpan riwayat pangkat baru
                \App\Models\KgbRiwayatPangkat::create([
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

                // Simpan riwayat gaji baru
                \App\Models\KgbRiwayatGaji::create([
                    'pegawai_id' => $pegawai->id,
                    'gaji_pokok' => $request->gaji_pokok_saat_ini,
                    'tmt_berlaku' => $request->tmt_pangkat_terakhir,
                    'jenis' => 'PANGKAT',
                    'nomor_sk' => $request->nomor_sk_pangkat ?? '-',
                    'tanggal_sk' => $request->tanggal_sk_pangkat ?? now(),
                    'pejabat_penetap' => $request->pejabat_penetap ?? '-',
                    'dasar_peraturan' => 'PP 5/2024',
                ]);

                // Update pegawai
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
     * Batalkan pengurusan KGB (jika salah)
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
            'kgb_pengurusan_id' => $pengurusan->id,
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
}