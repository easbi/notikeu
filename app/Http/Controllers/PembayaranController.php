<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use DB;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pembayaran = DB::table('transaksi_pembayaran')
            ->join('pegawai', 'transaksi_pembayaran.id_pegawai','=', 'pegawai.id')
            ->join('referensi_pembayaran', 'referensi_pembayaran.id','=', 'transaksi_pembayaran.id_pembayaran')
            ->select('transaksi_pembayaran.*', 'pegawai.fullname', 'referensi_pembayaran.nama_pembayaran')
            ->get();
        return view('transaksi_pembayaran.index', compact('pembayaran'))->with('i', (request()->input('page', 1) - 1) * 5);;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $pegawai = DB::table('pegawai')->select('id', 'fullname', 'no_rek', 'no_hp')->get();
        $jenispembayaran = DB::table('referensi_pembayaran')->get();
        return view('transaksi_pembayaran.create', compact('pegawai', 'jenispembayaran'));
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
            'id_pembayaran' => 'required',
            'id_pegawai' => 'required',
            'bersih' => 'required',
            'potongan' => 'required',
            'jumlah_bayar' => 'required',
        ]);

        Pembayaran::create($request->all());

        /*$token = "sfkCEvboXrecQAZDMXm2m9jt5ptU3agwZTyUpxoCWU1U7gCmie";

        $hp = array("085265513571", "085265513571", "085265513571");
        foreach ($hp as $phone) { 
            $message = "Testing by {$phone}";

            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://app.ruangwa.id/api/send_message',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => 'token='.$token.'&number='.$phone.'&message='.$message,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            echo $response;
        }*/
        $pembayaran = DB::table('transaksi_pembayaran')
            ->where('id_pegawai', $request->id_pegawai)
            ->where('id_pembayaran', $request->id_pembayaran)
            ->join('pegawai', 'transaksi_pembayaran.id_pegawai','=', 'pegawai.id')
            ->join('referensi_pembayaran', 'referensi_pembayaran.id','=', 'transaksi_pembayaran.id_pembayaran')
            ->select('transaksi_pembayaran.*', 'pegawai.fullname', 'pegawai.no_rek', 'referensi_pembayaran.nama_pembayaran', 'referensi_pembayaran.bulan', 'referensi_pembayaran.tahun')
            ->first();
        
        // dd($pembayaran->nama_pembayaran);

        $token = "sfkCEvboXrecQAZDMXm2m9jt5ptU3agwZTyUpxoCWU1U7gCmie";
        $phone = DB::table('pegawai')->where('id', $request->id_pegawai)->select('no_hp')->first()->no_hp;

        $message = 
"*Notifikasi {$pembayaran->nama_pembayaran} Bulan {$pembayaran->bulan} Tahun {$pembayaran->tahun}*.
Nama Pegawai : *{$pembayaran->fullname}* 

Bersih : Rp.{$request->bersih} 
Potongan : Rp.{$request->potongan} 
 -----------
Jumlah yang di Bayarkan : *Rp.{$request->jumlah_bayar}* ke nomor rekening *{$pembayaran->no_rek}*. 

_Pesan ini dikirimkan oleh *Sistem Notifikasi Keuangan* BPS Kota Padang Panjang Pada waktu XXXX_";

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://app.ruangwa.id/api/send_message',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'token='.$token.'&number='.$phone.'&message='.$message,
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        /// redirect 
        return redirect()->route('pembayaran.index')
                        ->with('success','Data Successfuly inserted');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pembayaran  $pembayaran
     * @return \Illuminate\Http\Response
     */
    public function show(Pembayaran $pembayaran)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pembayaran  $pembayaran
     * @return \Illuminate\Http\Response
     */
    public function edit(Pembayaran $pembayaran)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pembayaran  $pembayaran
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pembayaran $pembayaran)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pembayaran  $pembayaran
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Hapus Data
        $pembayaran = Pembayaran::find($id); 
        $pembayaran->delete();

        // setelah berhasil hapus
        return redirect('/pembayaran');
    }
}
