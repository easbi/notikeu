@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-edit"></i> Edit Riwayat Gaji / KGB</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pegawai.riwayat-gaji', $riwayat->pegawai_id) }}">Riwayat Gaji</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h5 class="mb-0">Form Edit Riwayat Gaji</h5>
                        </div>
                        <form action="{{ route('pegawai.update-riwayat-gaji', $riwayat->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    {{-- Baris 1 --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Jenis Perubahan</label>
                                            <select name="jenis" class="form-control" required>
                                                <option value="KGB" {{ $riwayat->jenis == 'KGB' ? 'selected' : '' }}>KGB</option>
                                                <option value="PANGKAT" {{ $riwayat->jenis == 'PANGKAT' ? 'selected' : '' }}>Kenaikan Pangkat</option>
                                                <option value="PP" {{ $riwayat->jenis == 'PP' ? 'selected' : '' }}>Penyesuaian PP</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Gaji Pokok Baru (Rp)</label>
                                            <input type="number" name="gaji_pokok" class="form-control" value="{{ $riwayat->gaji_pokok }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>TMT Berlaku</label>
                                            <input type="date" name="tmt_berlaku" class="form-control" value="{{ $riwayat->tmt_berlaku->format('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Baris 2 --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Nomor SK</label>
                                            <input type="text" name="nomor_sk" class="form-control" value="{{ $riwayat->nomor_sk }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tanggal SK</label>
                                            <input type="date" name="tanggal_sk" class="form-control" value="{{ $riwayat->tanggal_sk->format('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Pejabat Penetap</label>
                                            <input type="text" name="pejabat_penetap" class="form-control" value="{{ $riwayat->pejabat_penetap }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Baris 3 --}}
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Dasar Peraturan</label>
                                            <input type="text" name="dasar_peraturan" class="form-control" value="{{ $riwayat->dasar_peraturan }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('pegawai.riwayat-gaji', $riwayat->pegawai_id) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-warning float-right">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
