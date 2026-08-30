<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\KgbPengurusan;
use App\Models\KgbRiwayatGaji;
use App\Models\KgbLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class KgbDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard Utama KGB
     */
    public function index()
    {
        $user = Auth::user();
        $pegawaiUser = Pegawai::where('nip', $user->nip)->first();

        // Cek apakah user adalah admin/operator
        $isOperator = $pegawaiUser && in_array($pegawaiUser->id, [1, 2, 4, 15]);

        // ============ STATISTIK ============
        $stats = [];

        if ($isOperator) {
            // Statistik untuk operator/admin
            $stats = [
                'total_pegawai' => Pegawai::where('status_aktif', true)->count(),
                'total_pegawai_nonaktif' => Pegawai::where('status_aktif', false)->count(),
                
                'kgb_ongoing' => KgbPengurusan::onGoing()->count(),
                'kgb_pending' => KgbPengurusan::where('status', 'pending')->count(),
                'kgb_proses' => KgbPengurusan::where('status', 'proses')->count(),
                'kgb_selesai' => KgbPengurusan::selesai()->count(),
                'kgb_bulan_ini' => KgbPengurusan::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
                
                'total_riwayat_gaji' => KgbRiwayatGaji::count(),
                'total_log' => KgbLog::count(),
            ];

            // ============ DATA KGB MENDATANG (BULAN+2) ============
            $bulanTarget = Carbon::now()->addMonths(2);
            $kgbMendatang = Pegawai::where('status_aktif', true)
                ->whereNotNull('golongan')
                ->get()
                ->filter(function ($pegawai) use ($bulanTarget) {
                    $tmtKgb = $pegawai->tmt_kgb_berikutnya;
                    if (!$tmtKgb) return false;
                    
                    return $tmtKgb->month == $bulanTarget->month && 
                           $tmtKgb->year == $bulanTarget->year;
                })
                ->values();

            // ============ GRAFIK KGB PER BULAN ============
            $grafikKgb = KgbPengurusan::select(
                    DB::raw('YEAR(tmt_kgb_baru) as tahun'),
                    DB::raw('MONTH(tmt_kgb_baru) as bulan'),
                    DB::raw('COUNT(*) as total')
                )
                ->where('status', 'selesai')
                ->whereYear('tmt_kgb_baru', '>=', Carbon::now()->subYears(2)->year)
                ->groupBy('tahun', 'bulan')
                ->orderBy('tahun', 'asc')
                ->orderBy('bulan', 'asc')
                ->get()
                ->map(function ($item) {
                    $item->label = Carbon::create($item->tahun, $item->bulan, 1)->format('M Y');
                    return $item;
                });

            // ============ 5 PEGAWAI TERAKHIR KGB ============
            $kgbTerakhir = KgbPengurusan::selesai()
                ->with(['pegawai'])
                ->orderBy('tanggal_selesai', 'desc')
                ->limit(5)
                ->get();

            // ============ LOG TERAKHIR ============
            $logTerakhir = KgbLog::with(['pegawai', 'dilakukanOleh'])
                ->orderBy('waktu', 'desc')
                ->limit(10)
                ->get();

            // ============ PEGAWAI YANG PERLU DIPERHATIKAN ============
            $pegawaiPerluKgb = Pegawai::where('status_aktif', true)
                ->whereNotNull('golongan')
                ->get()
                ->filter(function ($pegawai) {
                    $tmtKgb = $pegawai->tmt_kgb_berikutnya;
                    if (!$tmtKgb) return false;
                    
                    // KGB dalam 3 bulan ke depan
                    return Carbon::now()->diffInMonths($tmtKgb) <= 3 && 
                           Carbon::now()->diffInMonths($tmtKgb) >= 0;
                })
                ->values();

            // ============ DATA UNTUK VIEW ============
            $data = [
                'stats' => $stats,
                'kgb_mendatang' => $kgbMendatang,
                'kgb_terakhir' => $kgbTerakhir,
                'grafik_kgb' => $grafikKgb,
                'log_terakhir' => $logTerakhir,
                'pegawai_perlu_kgb' => $pegawaiPerluKgb,
                'bulan_target' => $bulanTarget->format('F Y'),
                'is_operator' => true,
            ];

        } else {
            // ============ USER BIASA ============
            // Hanya lihat data sendiri
            $pegawai = $pegawaiUser;

            if ($pegawai) {
                $riwayatKgb = KgbPengurusan::where('pegawai_id', $pegawai->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $tmtKgbBerikutnya = $pegawai->tmt_kgb_berikutnya;
                $gajiBaru = $pegawai->gaji_pokok_baru;

                $data = [
                    'pegawai' => $pegawai,
                    'riwayat_kgb' => $riwayatKgb,
                    'tmt_kgb_berikutnya' => $tmtKgbBerikutnya,
                    'gaji_baru' => $gajiBaru,
                    'is_operator' => false,
                ];
            } else {
                $data = [
                    'pegawai' => null,
                    'riwayat_kgb' => collect([]),
                    'tmt_kgb_berikutnya' => null,
                    'gaji_baru' => 0,
                    'is_operator' => false,
                ];
            }
        }

        return view('kgb.dashboard', $data);
    }

    /**
     * API: Data untuk grafik KGB (JSON)
     */
    public function grafikData(Request $request)
    {
        $tahun = $request->tahun ?? Carbon::now()->year;

        $data = KgbPengurusan::select(
                DB::raw('MONTH(tmt_kgb_baru) as bulan'),
                DB::raw('COUNT(*) as total')
            )
            ->where('status', 'selesai')
            ->whereYear('tmt_kgb_baru', $tahun)
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get()
            ->pluck('total', 'bulan')
            ->toArray();

        // Isi bulan yang kosong dengan 0
        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[$i] = $data[$i] ?? 0;
        }

        return response()->json([
            'tahun' => $tahun,
            'data' => array_values($result),
            'bulan' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
        ]);
    }

    /**
     * API: Data KGB mendatang (JSON)
     */
    public function kgbMendatangData()
    {
        $bulanTarget = Carbon::now()->addMonths(2);

        $data = Pegawai::where('status_aktif', true)
            ->whereNotNull('golongan')
            ->get()
            ->filter(function ($pegawai) use ($bulanTarget) {
                $tmtKgb = $pegawai->tmt_kgb_berikutnya;
                if (!$tmtKgb) return false;
                
                return $tmtKgb->month == $bulanTarget->month && 
                       $tmtKgb->year == $bulanTarget->year;
            })
            ->map(function ($pegawai) {
                return [
                    'nip' => $pegawai->nip,
                    'nama' => $pegawai->fullname,
                    'golongan' => $pegawai->golongan,
                    'tmt_kgb' => $pegawai->tmt_kgb_berikutnya->format('d-m-Y'),
                ];
            })
            ->values();

        return response()->json([
            'bulan_target' => $bulanTarget->format('F Y'),
            'total' => $data->count(),
            'data' => $data
        ]);
    }

    /**
     * API: Statistik Ringkas (JSON)
     */
    public function statsData()
    {
        $stats = [
            'total_pegawai' => Pegawai::where('status_aktif', true)->count(),
            'kgb_ongoing' => KgbPengurusan::onGoing()->count(),
            'kgb_selesai' => KgbPengurusan::selesai()->count(),
            'kgb_bulan_ini' => KgbPengurusan::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export Laporan KGB (Excel/PDF)
     */
    public function laporan(Request $request)
    {
        $request->validate([
            'tahun' => 'nullable|integer|min:2020|max:2099',
            'status' => 'nullable|in:all,pending,proses,selesai',
        ]);

        $tahun = $request->tahun ?? Carbon::now()->year;
        $status = $request->status ?? 'all';

        $query = KgbPengurusan::with(['pegawai', 'diprosesOleh']);

        if ($tahun) {
            $query->whereYear('created_at', $tahun);
        }

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Jika format PDF
        if ($request->format == 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kgb.laporan_pdf', [
                'data' => $data,
                'tahun' => $tahun,
                'status' => $status,
                'tanggal_cetak' => Carbon::now()->format('d-m-Y H:i:s')
            ]);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download("Laporan_KGB_{$tahun}.pdf");
        }

        // Jika format Excel (gunakan Maatwebsite Excel)
        // return (new KgbLaporanExport($data))->download("Laporan_KGB_{$tahun}.xlsx");

        // Default: tampilkan HTML
        return view('kgb.laporan', compact('data', 'tahun', 'status'));
    }

    /**
     * Refresh data KGB (trigger cron manual)
     */
    public function refresh()
    {
        // Jalankan command secara manual
        \Artisan::call('kgb:check-bulanan');

        return redirect()->route('kgb.dashboard')
            ->with('success', 'Data KGB berhasil di-refresh!');
    }
}