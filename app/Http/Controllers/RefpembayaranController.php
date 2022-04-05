<?php

namespace App\Http\Controllers;

use App\Models\Refpembayaran;
use Illuminate\Http\Request;
use DB;

class RefpembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $refpe = DB::table('referensi_pembayaran')->get();
        dd($refpe);
        // return view('refpembayaran.index', compact('refpe'))->with('i', (request()->input('page', 1) - 1) * 5);;
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
    public function edit(Refpembayaran $refpembayaran)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Refpembayaran  $refpembayaran
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Refpembayaran $refpembayaran)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Refpembayaran  $refpembayaran
     * @return \Illuminate\Http\Response
     */
    public function destroy(Refpembayaran $refpembayaran)
    {
        //
    }
}
