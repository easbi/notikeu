<?php

namespace App\Imports;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class PembayaranImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Pembayaran([
            'id_pegawai'  => $row[1],
            'id_pembayaran'  => $row[1],
            'bersih' => $row[1],
            'potongan' => $row[1],
            'jumlah_bayar' => $row[1],
        ]);
    }
}
