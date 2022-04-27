<?php

namespace App\Http\Controllers;

use App\Models\Refpembayaran;
use Illuminate\Http\Request;
use DB;

class RefpembayaranController extends Controller
{
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
        $refpe = DB::table('referensi_pembayaran')->get();
        // dd($refpe);
        return view('ref_pembayaran.index', compact('refpe'))->with('i', (request()->input('page', 1) - 1) * 5);;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ref_pembayaran.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pembayaran' => 'required',
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        Refpembayaran::create($request->all());
         
        /// redirect 
        return redirect()->route('refpembayaran.index')
                        ->with('success','Jenis Data pembayaran Successfuly inserted');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Refpembayaran  $refpembayaran
     * @return \Illuminate\Http\Response
     */
    public function show(Refpembayaran $refpembayaran)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Refpembayaran  $refpembayaran
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $jenispembayaran = DB::table('referensi_pembayaran')->find($id);
        return view('ref_pembayaran.edit', compact('jenispembayaran'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Refpembayaran  $refpembayaran
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /// membuat validasi 
        $request->validate([
            'nama_pembayaran' => 'required',
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $jenispembayaran = Refpembayaran::find($id);
        $jenispembayaran->nama_pembayaran = $request->nama_pembayaran;
        $jenispembayaran->bulan = $request->bulan;
        $jenispembayaran->tahun = $request->tahun;
        $jenispembayaran->updated_at = date("Y-m-d H:i:s"); 
        $jenispembayaran->save();

        // setelah berhasil mengubah data
        return redirect()->route('refpembayaran.index')
                        ->with('success', 'Data updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Refpembayaran  $refpembayaran
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Hapus Data
        $jenispembayaran = Refpembayaran::find($id); 
        $jenispembayaran->delete();

        // setelah berhasil hapus
        return redirect('/refpembayaran');
    }
}
