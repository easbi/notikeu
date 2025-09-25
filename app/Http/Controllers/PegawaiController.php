<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Imports\PembayaranImport;
use DB;

class PegawaiController extends Controller
{
    /**
     * Summary of __construct
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = Auth::user(); // user yang login

        // ambil pegawai yang sesuai dengan user nip
        $pegawaiUser = Pegawai::where('nip', $user->nip)->first();

        // kalau user ini ternyata admin (berdasarkan pegawai.id)
        $adminIds = [1, 2, 4, 15]; // id dari tabel pegawai
        if ($pegawaiUser && in_array($pegawaiUser->id, $adminIds)) {
            // admin bisa lihat semua
            $pegawai = Pegawai::select('id', 'fullname', 'no_hp', 'no_rek_bsi', 'no_rek_bni', 'no_rek_bri')->get();
        } else {
            // user biasa hanya lihat data pegawai sesuai nip
            $pegawai = Pegawai::select('id', 'fullname', 'no_hp', 'no_rek_bsi', 'no_rek_bni', 'no_rek_bri')
                ->where('nip', $user->nip) // match nip di user dengan nip di pegawai
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
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pegawai  $pegawai
     * @return \Illuminate\Http\Response
     */
    public function show(Pegawai $pegawai)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pegawai  $pegawai
     * @return \Illuminate\Http\Response
     */
    public function edit(Pegawai $pegawai)
    {
        return view('pegawai.edit', compact('pegawai'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pegawai  $pegawai
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        /// membuat validasi
        $request->validate([
            'fullname' => 'required',
            'no_rek_bsi' => 'nullable',
            'no_rek_bni' => 'nullable',
            'no_rek_bri' => 'nullable',
        ]);

        $pegawai->fullname = $request->fullname;
        $pegawai->no_rek_bsi = $request->no_rek_bsi;
        $pegawai->no_rek_bni = $request->no_rek_bni;
        $pegawai->no_rek_bri = $request->no_rek_bri;
        $pegawai->updated_at = date("Y-m-d H:i:s");
        $pegawai->save();

        // setelah berhasil mengubah data
        return redirect()->route('pegawai.index')
            ->with('success', 'Data updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pegawai  $pegawai
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pegawai $pegawai)
    {
        //
    }
}
