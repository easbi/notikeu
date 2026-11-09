@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Preview SK KGB</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('kgb.word', $pengurusan->id) }}" class="btn btn-primary float-right">
                        <i class="fas fa-file-word"></i> Download Word
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><th>Nomor SK</th><td>{{ $data['nomor_naskah'] }}</td></tr>
                        <tr><th>Nama</th><td>{{ $data['nama_pegawai'] }}</td></tr>
                        <tr><th>NIP</th><td>{{ $data['nip'] }}</td></tr>
                        <tr><th>Pangkat/Jabatan</th><td>{{ $data['pangkat_jabatan'] }}</td></tr>
                        <tr><th>Gaji Pokok Lama</th><td>{{ $data['gaji_pokok_lama'] }}</td></tr>
                        <tr><th>Gaji Pokok Baru</th><td>{{ $data['gaji_pokok_baru'] }}</td></tr>
                        <tr><th>Masa Kerja Golongan</th><td>{{ $data['masa_kerja_golongan'] }}</td></tr>
                        <tr><th>Masa Kerja KGB</th><td>{{ $data['masa_kerja_kgb'] }}</td></tr>
                        <tr><th>TMT KGB Baru</th><td>{{ $data['tmt_kgb_baru'] }}</td></tr>
                        <tr><th>Kenaikan y.a.d</th><td>{{ $data['tmt_kgb_berikutnya'] }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
