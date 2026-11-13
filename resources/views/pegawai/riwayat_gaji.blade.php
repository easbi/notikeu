@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-money-bill-wave"></i> Riwayat Gaji / KGB</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pegawai.edit', $pegawai->id) }}">Edit Pegawai</a></li>
                        <li class="breadcrumb-item active">Riwayat Gaji</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row">
                {{-- Tabel Riwayat --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Histori Perubahan Gaji</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>TMT Berlaku</th>
                                        <th>Gaji Pokok</th>
                                        <th>Jenis</th>
                                        <th>Nomor SK</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->riwayatGaji as $r)
                                    <tr>
                                        <td>{{ $r->tmt_berlaku->format('d-m-Y') }}</td>
                                        <td>Rp {{ number_format($r->gaji_pokok, 0, ',', '.') }}</td>
                                        <td><span class="badge badge-info">{{ $r->jenis }}</span></td>
                                        <td>{{ $r->nomor_sk }}</td>
                                        <td>
                                            <a href="{{ route('pegawai.edit-riwayat-gaji', $r->id) }}" class="btn btn-xs btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('pegawai.destroy-riwayat-gaji', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Form Tambah --}}
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h5 class="mb-0">Tambah Riwayat Gaji</h5>
                        </div>
                        <form action="{{ route('pegawai.store-riwayat-gaji', $pegawai->id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Jenis Perubahan</label>
                                    <select name="jenis" class="form-control" required>
                                        <option value="KGB">KGB</option>
                                        <option value="PANGKAT">Kenaikan Pangkat</option>
                                        <option value="PP">Penyesuaian PP</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Gaji Pokok Baru (Rp)</label>
                                    <input type="number" name="gaji_pokok" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>TMT Berlaku</label>
                                    <input type="date" name="tmt_berlaku" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Nomor SK</label>
                                    <input type="text" name="nomor_sk" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal SK</label>
                                    <input type="date" name="tanggal_sk" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Pejabat Penetap</label>
                                    <input type="text" name="pejabat_penetap" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Dasar Peraturan (Opsional)</label>
                                    <input type="text" name="dasar_peraturan" class="form-control" value="PP 5/2024">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success btn-block">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
