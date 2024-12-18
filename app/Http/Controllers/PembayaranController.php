<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Jobs\SendNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Imports\PembayaranImport;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class PembayaranController extends Controller
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
        $pembayaran = DB::table('transaksi_pembayaran')
            ->join('pegawai', 'transaksi_pembayaran.id_pegawai','=', 'pegawai.id')
            ->join('referensi_pembayaran', 'referensi_pembayaran.id','=', 'transaksi_pembayaran.id_pembayaran')
            ->select('transaksi_pembayaran.*', 'pegawai.fullname', 'pegawai.no_hp', 'referensi_pembayaran.nama_pembayaran', 'referensi_pembayaran.bulan', 'referensi_pembayaran.tahun')
            ->get();
        return view('transaksi_pembayaran.index', compact('pembayaran'))->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function sendwa()
    {
        $notsend = DB::table('transaksi_pembayaran')->where('send_notif', 0)
            ->join('pegawai', 'transaksi_pembayaran.id_pegawai','=', 'pegawai.id')
            ->join('referensi_pembayaran', 'referensi_pembayaran.id','=', 'transaksi_pembayaran.id_pembayaran')
            ->select('transaksi_pembayaran.*', 'pegawai.no_hp', 'pegawai.no_rek', 'pegawai.fullname', 'referensi_pembayaran.nama_pembayaran', 'referensi_pembayaran.bulan', 'referensi_pembayaran.tahun')
            ->get();

        // dd($notsend);

        foreach ($notsend as $ns) { 
            $timestamp = date('d-m-y h:i:s');
            $monthName = date('F', mktime(0, 0, 0, $ns->bulan, 10));
            $dt = $monthName;
            $nmeng = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
            $nmtur = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
            $dt = str_ireplace($nmeng, $nmtur, $dt);
            $kotor = number_format($ns->bersih, 2, ",", ".");
            $potongan = number_format($ns->potongan, 2, ",", ".");
            $jumlah_bayar = number_format($ns->jumlah_bayar, 2, ",", ".");
            $message = 
"*Notifikasi {$ns->nama_pembayaran} Bulan {$dt}  Tahun {$ns->tahun}*.
Nama Pegawai : *{$ns->fullname}* 

Jumlah awal : Rp.{$kotor} 
Potongan : Rp.{$potongan} 
 -----------
Jumlah yang di Bayarkan : *Rp.{$jumlah_bayar}* ke nomor rekening *{$ns->no_rek}*. 

_Pesan ini dikirimkan oleh *Sistem Notifikasi Keuangan* BPS Kota Padang Panjang Pada waktu {$timestamp} WIB_
";
            $details = [
                'message' => $message,
                'no_hp' => $ns->no_hp,
                'id' => $ns->id,
            ];

            $delay = \DB::table('jobs')->count()*10;
            $queue = new SendNotification($details);

            // send all notification whatsapp in the queue.
            dispatch($queue->delay($delay));
        }

        

        // redirect 
        return redirect()->route('pembayaran.index')
                        ->with('success','Notifikasi Sukses Terkirim ke Nomor WA Pegawai');
    }

    public function import(Request $request) 
    {
        $this->validate($request, [
            'file_pembayaran' => 'required|mimes:csv,xls,xlsx'
        ]);

        // menangkap file excel
        $file = $request->file('file_pembayaran');
 
        // membuat nama file unik
        $nama_file = rand().$file->getClientOriginalName();
 
        // upload ke folder di dalam folder public
        $file->move('file_import',$nama_file);
 
        // import data
        Excel::import(new PembayaranImport($request->id_pembayaran), public_path('/file_import/'.$nama_file));


        
        // alihkan halaman kembali
        return redirect('/pembayaran')->with('success', 'All good!');
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

        $result = Pembayaran::create([
                'id_pembayaran' => $request->id_pembayaran,
                'id_pegawai' =>  $request->id_pegawai,
                'bersih' => preg_replace('/[^0-9]/', '', $request->bersih),
                'potongan' => preg_replace('/[^0-9]/', '', $request->potongan),
                'jumlah_bayar' => preg_replace('/[^0-9]/', '', $request->jumlah_bayar),
                'send_notif' => 0,
            ]);


        // redirect 
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
