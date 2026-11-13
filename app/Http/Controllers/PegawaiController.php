<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\KgbRiwayatPangkat;
use App\Models\KgbRiwayatGaji;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Cek apakah user adalah admin (id = 1)
     */
    protected function isAdmin()
    {
        return Auth::id() == 1;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($this->isAdmin()) {
            // Admin: lihat semua pegawai
            $pegawai = Pegawai::select(
                'id',
                'fullname',
                'nip',
                'no_hp',
                'no_rek_bsi',
                'no_rek_bni',
                'no_rek_bri',
                'golongan',
                'pangkat',
                'jabatan',
                'tmt_pangkat_terakhir',
                'tmt_kgb_terakhir',
                'gaji_pokok_saat_ini',
                'status_aktif'
            )->get();
        } else {
            // User biasa: hanya lihat data sendiri
            $pegawai = Pegawai::select(
                'id',
                'fullname',
                'nip',
                'no_hp',
                'no_rek_bsi',
                'no_rek_bni',
                'no_rek_bri',
                'golongan',
                'pangkat',
                'jabatan',
                'tmt_pangkat_terakhir',
                'tmt_kgb_terakhir',
                'gaji_pokok_saat_ini'
            )
                ->where('nip', $user->nip)
                ->get();
        }

        if ($pegawai->isEmpty()) {
            return redirect()->route('pegawai.index')
                ->with('error', 'Tidak ada data pegawai yang ditemukan.');
        }

        return view('pegawai.index', compact('pegawai'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     * (Hanya admin yang bisa tambah pegawai)
     */
    public function create()
    {
        if (!$this->isAdmin()) {
            abort(403, 'Hanya admin yang bisa menambah pegawai.');
        }
        return view('pegawai.create');
    }

    /**
     * Store a newly created resource in storage.
     * (Hanya admin yang bisa tambah pegawai)
     */
    public function store(Request $request)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Hanya admin yang bisa menambah pegawai.');
        }

        $request->validate([
            'fullname' => 'required|string|max:70',
            'nip' => 'required|string|size:18|unique:pegawai',
            'golongan' => 'required|string|max:10',
            'pangkat' => 'required|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'tmt_pangkat_terakhir' => 'required|date',
            'gaji_pokok_saat_ini' => 'required|numeric|min:0',
            'organisasi' => 'required|string',
            'unit_kerja' => 'required|string',
            'no_hp' => 'required|string',
            'email' => 'required|email|unique:pegawai',
        ]);

        DB::transaction(function () use ($request) {
            $pegawai = Pegawai::create([
                'fullname' => $request->fullname,
                'username' => $request->username ?? $request->nip,
                'nip' => $request->nip,
                'email' => $request->email,
                'password' => bcrypt($request->password ?? 'password123'),
                'no_hp' => $request->no_hp,
                'organisasi' => $request->organisasi,
                'unit_kerja' => $request->unit_kerja,
                'jabatan' => $request->jabatan,
                'golongan' => $request->golongan,
                'pangkat' => $request->pangkat,
                'kode_instansi' => $request->kode_instansi ?? '13741',
                'tmt_pangkat_terakhir' => $request->tmt_pangkat_terakhir,
                'gaji_pokok_saat_ini' => $request->gaji_pokok_saat_ini,
                'status_aktif' => true,
            ]);

            // Buat riwayat pangkat pertama
            KgbRiwayatPangkat::create([
                'pegawai_id' => $pegawai->id,
                'golongan' => $request->golongan,
                'pangkat' => $request->pangkat,
                'jabatan' => $request->jabatan ?? '-',
                'tmt_mulai' => $request->tmt_pangkat_terakhir,
                'nomor_sk' => $request->nomor_sk_pangkat ?? 'SK-001/2024',
                'tanggal_sk' => $request->tanggal_sk_pangkat ?? now(),
                'pejabat_penetap' => $request->pejabat_penetap ?? 'Kepala Instansi',
            ]);

            // Buat riwayat gaji pertama
            KgbRiwayatGaji::create([
                'pegawai_id' => $pegawai->id,
                'gaji_pokok' => $request->gaji_pokok_saat_ini,
                'tmt_berlaku' => $request->tmt_pangkat_terakhir,
                'jenis' => 'PANGKAT',
                'nomor_sk' => $request->nomor_sk_pangkat ?? 'SK-001/2024',
                'tanggal_sk' => $request->tanggal_sk_pangkat ?? now(),
                'pejabat_penetap' => $request->pejabat_penetap ?? 'Kepala Instansi',
                'dasar_peraturan' => $request->dasar_peraturan ?? 'PP 5/2024',
            ]);
        });

        return redirect()->route('pegawai.index')
            ->with('success', 'Pegawai berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     * (User biasa hanya bisa lihat data sendiri)
     */
    public function show(Pegawai $pegawai)
    {
        $user = Auth::user();

        // Cek akses: user biasa hanya bisa lihat data sendiri
        if (!$this->isAdmin() && $pegawai->nip != $user->nip) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $pegawai->load([
            'riwayatPangkat',
            'riwayatGaji',
            'pengurusanKgb' => function ($q) {
                $q->limit(10);
            }
        ]);

        $tmtCpns = $pegawai->tmt_cpns;
        $tmtKgbBerikutnya = $pegawai->tmt_kgb_berikutnya;
        $mkgEfektif = $pegawai->mkg_efektif;
        $gajiBaru = $pegawai->gaji_pokok_baru;

        return view('pegawai.show', compact(
            'pegawai',
            'tmtCpns',
            'tmtKgbBerikutnya',
            'mkgEfektif',
            'gajiBaru'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     * (User biasa hanya bisa edit data sendiri)
     */
    public function edit(Pegawai $pegawai)
    {
        $user = Auth::user();

        // Cek akses: user biasa hanya bisa edit data sendiri
        if (!$this->isAdmin() && $pegawai->nip != $user->nip) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit data ini.');
        }

        // Ambil riwayat pangkat terakhir (untuk SK pangkat terbaru)
        $riwayatPangkatTerakhir = $pegawai->riwayatPangkat()->first();

        // Ambil riwayat gaji terakhir (untuk SK gaji terbaru)
        $riwayatGajiTerakhir = $pegawai->riwayatGaji()->first();

        return view('pegawai.edit', compact('pegawai', 'riwayatPangkatTerakhir', 'riwayatGajiTerakhir'));
    }

    /**
     * Update the specified resource in storage.
     * (User biasa hanya bisa update data sendiri)
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        $user = Auth::user();

        // Cek akses: user biasa hanya bisa update data sendiri
        if (!$this->isAdmin() && $pegawai->nip != $user->nip) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate data ini.');
        }

        $rules = [
            'fullname' => 'required|string|max:70',
            'no_hp' => 'required|string',
            'no_rek_bsi' => 'nullable',
            'no_rek_bni' => 'nullable',
            'no_rek_bri' => 'nullable',
        ];

        // Hanya admin yang bisa update data kepegawaian (golongan, pangkat, TMT, gaji)
        if ($this->isAdmin()) {
            $rules['golongan'] = 'required|string|max:10';
            $rules['pangkat'] = 'required|string|max:50';
            $rules['jabatan'] = 'nullable|string|max:100';
            $rules['tmt_pangkat_terakhir'] = 'required|date';
            $rules['gaji_pokok_saat_ini'] = 'required|numeric|min:0';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $pegawai) {
            // Update data dasar (semua user bisa)
            $pegawai->fullname = $request->fullname;
            $pegawai->no_hp = $request->no_hp;
            $pegawai->no_rek_bsi = $request->no_rek_bsi;
            $pegawai->no_rek_bni = $request->no_rek_bni;
            $pegawai->no_rek_bri = $request->no_rek_bri;
            $pegawai->updated_at = now();

            // Hanya admin yang bisa update data kepegawaian
            if ($this->isAdmin()) {
                // Cek apakah ada perubahan pangkat
                $pangkatBerubah = (
                    $pegawai->golongan != $request->golongan ||
                    $pegawai->pangkat != $request->pangkat ||
                    $pegawai->tmt_pangkat_terakhir != $request->tmt_pangkat_terakhir
                );

                // Cek apakah ada perubahan gaji
                $gajiBerubah = $pegawai->gaji_pokok_saat_ini != $request->gaji_pokok_saat_ini;

                // Jika pangkat berubah, buat riwayat baru
                if ($pangkatBerubah) {
                    KgbRiwayatPangkat::create([
                        'pegawai_id' => $pegawai->id,
                        'golongan' => $request->golongan,
                        'pangkat' => $request->pangkat,
                        'jabatan' => $request->jabatan ?? $pegawai->jabatan,
                        'tmt_mulai' => $request->tmt_pangkat_terakhir,
                        'nomor_sk' => $request->nomor_sk_pangkat ?? 'SK-001/2024',
                        'tanggal_sk' => $request->tanggal_sk_pangkat ?? now(),
                        'pejabat_penetap' => $request->pejabat_penetap ?? 'Kepala Instansi',
                        'masa_kerja_golongan' => $pegawai->mkg_golongan,
                    ]);
                }

                // Jika gaji berubah, buat riwayat baru
                if ($gajiBerubah) {
                    KgbRiwayatGaji::create([
                        'pegawai_id' => $pegawai->id,
                        'gaji_pokok' => $request->gaji_pokok_saat_ini,
                        'tmt_berlaku' => $request->tmt_pangkat_terakhir,
                        'jenis' => 'PANGKAT',
                        'nomor_sk' => $request->nomor_sk_pangkat ?? 'SK-001/2024',
                        'tanggal_sk' => $request->tanggal_sk_pangkat ?? now(),
                        'pejabat_penetap' => $request->pejabat_penetap ?? 'Kepala Instansi',
                        'dasar_peraturan' => $request->dasar_peraturan ?? 'PP 5/2024',
                    ]);
                }

                // Update data kepegawaian
                $pegawai->golongan = $request->golongan;
                $pegawai->pangkat = $request->pangkat;
                $pegawai->jabatan = $request->jabatan;
                $pegawai->tmt_pangkat_terakhir = $request->tmt_pangkat_terakhir;
                $pegawai->gaji_pokok_saat_ini = $request->gaji_pokok_saat_ini;
                $pegawai->organisasi = $request->organisasi ?? $pegawai->organisasi;
                $pegawai->unit_kerja = $request->unit_kerja ?? $pegawai->unit_kerja;
            }

            $pegawai->save();
        });

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     * (Hanya admin yang bisa nonaktifkan pegawai)
     */
    public function destroy(Pegawai $pegawai)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Hanya admin yang bisa menonaktifkan pegawai.');
        }

        $pegawai->status_aktif = false;
        $pegawai->save();

        return redirect()->route('pegawai.index')
            ->with('success', 'Pegawai dinonaktifkan!');
    }

    /**
     * Tampilkan riwayat KGB pegawai
     */
    public function riwayatKgb($id)
    {
        $pegawai = Pegawai::with(['riwayatPangkat', 'riwayatGaji', 'pengurusanKgb'])->findOrFail($id);

        $user = Auth::user();
        if (!$this->isAdmin() && $pegawai->nip != $user->nip) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('pegawai.riwayat_kgb', compact('pegawai'));
    }

    public function riwayatPangkat($id)
    {
        $pegawai = Pegawai::with([
            'riwayatPangkat' => function ($q) {
                $q->orderBy('tmt_mulai', 'asc'); // Urut dari CPNS ke terbaru
            }
        ])->findOrFail($id);

        return view('pegawai.riwayat_pangkat', compact('pegawai'));
    }

    public function storeRiwayatPangkat(Request $request, $id)
    {
        $request->validate([
            'golongan' => 'required',
            'pangkat' => 'required',
            'tmt_mulai' => 'required|date',
            'nomor_sk' => 'required',
            'tanggal_sk' => 'required|date',
            'pejabat_penetap' => 'required',
        ]);

        $pegawai = Pegawai::findOrFail($id);

        KgbRiwayatPangkat::create([
            'pegawai_id' => $pegawai->id,
            'golongan' => $request->golongan,
            'pangkat' => $request->pangkat,
            'jabatan' => $request->jabatan ?? $pegawai->jabatan ?? '-',
            'tmt_mulai' => $request->tmt_mulai,
            'nomor_sk' => $request->nomor_sk,
            'tanggal_sk' => $request->tanggal_sk,
            'pejabat_penetap' => $request->pejabat_penetap,
            'masa_kerja_golongan' => $pegawai->mkg_golongan,
        ]);

        return back()->with('success', 'Riwayat Pangkat berhasil ditambahkan!');
    }
    public function editRiwayatPangkat($id)
    {
        $riwayat = KgbRiwayatPangkat::findOrFail($id);
        return view('pegawai.edit_riwayat_pangkat', compact('riwayat'));
    }

    public function updateRiwayatPangkat(Request $request, $id)
    {
        $request->validate([
            'golongan' => 'required',
            'pangkat' => 'required',
            'tmt_mulai' => 'required|date',
            'nomor_sk' => 'required',
            'tanggal_sk' => 'required|date',
            'pejabat_penetap' => 'required',
        ]);

        $riwayat = KgbRiwayatPangkat::findOrFail($id);
        $riwayat->update($request->all());

        return redirect()->route('pegawai.riwayat-pangkat', $riwayat->pegawai_id)
            ->with('success', 'Riwayat Pangkat berhasil diperbarui!');
    }

    public function destroyRiwayatPangkat($id)
    {
        $riwayat = KgbRiwayatPangkat::findOrFail($id);
        $pegawai_id = $riwayat->pegawai_id;
        $riwayat->delete();

        return redirect()->route('pegawai.riwayat-pangkat', $pegawai_id)
            ->with('success', 'Riwayat Pangkat berhasil dihapus!');
    }

        public function riwayatGaji($id)
    {
        $pegawai = Pegawai::with(['riwayatGaji' => function($q) {
            $q->orderBy('tmt_berlaku', 'asc'); // Urut dari gaji terlama ke terbaru
        }])->findOrFail($id);

        return view('pegawai.riwayat_gaji', compact('pegawai'));
    }

    public function storeRiwayatGaji(Request $request, $id)
    {
        $request->validate([
            'gaji_pokok' => 'required|numeric|min:0',
            'tmt_berlaku' => 'required|date',
            'jenis' => 'required|in:PANGKAT,KGB,PP,LAINNYA',
            'nomor_sk' => 'required',
            'tanggal_sk' => 'required|date',
            'pejabat_penetap' => 'required',
        ]);

        $pegawai = Pegawai::findOrFail($id);

        KgbRiwayatGaji::create([
            'pegawai_id' => $pegawai->id,
            'gaji_pokok' => $request->gaji_pokok,
            'tmt_berlaku' => $request->tmt_berlaku,
            'jenis' => $request->jenis,
            'nomor_sk' => $request->nomor_sk,
            'tanggal_sk' => $request->tanggal_sk,
            'pejabat_penetap' => $request->pejabat_penetap,
            'dasar_peraturan' => $request->dasar_peraturan ?? '-',
        ]);

        return back()->with('success', 'Riwayat Gaji berhasil ditambahkan!');
    }

    public function editRiwayatGaji($id)
    {
        $riwayat = KgbRiwayatGaji::findOrFail($id);
        return view('pegawai.edit_riwayat_gaji', compact('riwayat'));
    }

    public function updateRiwayatGaji(Request $request, $id)
    {
        $request->validate([
            'gaji_pokok' => 'required|numeric|min:0',
            'tmt_berlaku' => 'required|date',
            'jenis' => 'required|in:PANGKAT,KGB,PP,LAINNYA',
            'nomor_sk' => 'required',
            'tanggal_sk' => 'required|date',
            'pejabat_penetap' => 'required',
        ]);

        $riwayat = KgbRiwayatGaji::findOrFail($id);
        $riwayat->update($request->all());

        return redirect()->route('pegawai.riwayat-gaji', $riwayat->pegawai_id)
            ->with('success', 'Riwayat Gaji berhasil diperbarui!');
    }

    public function destroyRiwayatGaji($id)
    {
        $riwayat = KgbRiwayatGaji::findOrFail($id);
        $pegawai_id = $riwayat->pegawai_id;
        $riwayat->delete();

        return redirect()->route('pegawai.riwayat-gaji', $pegawai_id)
            ->with('success', 'Riwayat Gaji berhasil dihapus!');
    }
}
