<?php

namespace App\Imports;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PembayaranImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    private $id_pembayaran;

    public function __construct(int $id_pembayaran)
    {
        $this->id_pembayaran = $id_pembayaran;
    }


    public function model(array $row)
    {

        return new Pembayaran([
            'id_pegawai'    => $row['id'],
            'id_pembayaran' => $this->id_pembayaran ?? null,
            'bersih'        => $row['bersih'] ?? null,
            'potongan'      => $row['potongan'] ?? null,
            'jumlah_bayar'  => $row['jumlah_bayar'] ?? null,
            'send_notif'    => 0 ?? null,
        ]);
    }
}
