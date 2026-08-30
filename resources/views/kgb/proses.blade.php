@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-edit"></i> Proses Kenaikan Gaji Berkala</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('kgb.index') }}">Pengurusan KGB</a></li>
                        <li class="breadcrumb-item active">Proses</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <form action="{{ route('kgb.proses', $pengurusan->id) }}" method="POST">
                @csrf

                {{-- ============ DATA PEGAWAI ============ --}}
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Data Pegawai</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr><th width="35%">Nama</th><td>{{ $pegawai->fullname }}</td></tr>
                                    <tr><th>NIP</th><td>{{ $pegawai->nip }}</td></tr>
                                    <tr><th>Golongan</th><td><span class="badge badge-secondary">{{ $pegawai->golongan }}</span></td></tr>
                                    <tr><th>Pangkat</th><td>{{ $pegawai->pangkat }}</td></tr>
                                    <tr><th>Jabatan</th><td>{{ $pegawai->jabatan ?? '-' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr><th width="35%">TMT CPNS</th><td>{{ $tmt_cpns ? $tmt_cpns->format('d-m-Y') : '-' }}</td></tr>
                                    <tr><th>TMT Pangkat</th><td>{{ $pegawai->tmt_pangkat_terakhir ? $pegawai->tmt_pangkat_terakhir->format('d-m-Y') : '-' }}</td></tr>
                                    <tr><th>TMT KGB Baru</th><td><strong>{{ $pengurusan->tmt_kgb_baru->format('d-m-Y') }}</strong></td></tr>
                                    <tr><th>Kenaikan y.a.d</th><td>{{ $pengurusan->tmt_kgb_berikutnya->format('d-m-Y') }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ DATA KENAIKAN GAJI ============ --}}
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator"></i> Data Kenaikan Gaji</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gaji Pokok Lama <span class="text-danger">*</span></label>
                                    <input type="number" name="gaji_pokok_lama" class="form-control" 
                                           value="{{ old('gaji_pokok_lama', $gaji_lama) }}" required>
                                    <small class="text-muted">Dari database: Rp {{ number_format($gaji_lama, 0, ',', '.') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gaji Pokok Baru <span class="text-danger">*</span></label>
                                    <input type="number" name="gaji_pokok_baru" class="form-control" 
                                           value="{{ old('gaji_pokok_baru', $gaji_baru) }}" required>
                                    <small class="text-muted">Dari tabel referensi: Rp {{ number_format($gaji_baru, 0, ',', '.') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Masa Kerja Golongan <span class="text-danger">*</span></label>
                                    <input type="text" name="masa_kerja_golongan" class="form-control" 
                                           value="{{ old('masa_kerja_golongan', $masa_kerja_golongan ?? '') }}" required>
                                    <small class="text-muted">TMT Pangkat - TMT CPNS</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Masa Kerja KGB <span class="text-danger">*</span></label>
                                    <input type="text" name="masa_kerja_kgb" class="form-control" 
                                           value="{{ old('masa_kerja_kgb', $masa_kerja_kgb ?? '') }}" required>
                                    <small class="text-muted">TMT KGB - TMT CPNS</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Dasar Peraturan <span class="text-danger">*</span></label>
                                    <input type="text" name="dasar_peraturan" class="form-control" 
                                           value="{{ old('dasar_peraturan', 'PP 5/2024') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>TMT Gaji Baru <span class="text-danger">*</span></label>
                                    <input type="date" name="tmt_gaji_baru" class="form-control" 
                                           value="{{ old('tmt_gaji_baru', $pengurusan->tmt_kgb_baru->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ DATA SK KGB ============ --}}
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Data SK KGB</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nomor SK <span class="text-danger">*</span></label>
                                    <input type="text" name="nomor_sk" class="form-control" 
                                           value="{{ old('nomor_sk', $nomor_sk_saran) }}" required>
                                    <small class="text-muted">Saran: {{ $nomor_sk_saran }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal SK <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_sk" class="form-control" 
                                           value="{{ old('tanggal_sk', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Pejabat Penetap <span class="text-danger">*</span></label>
                                    <input type="text" name="pejabat_penetap" class="form-control" 
                                           value="{{ old('pejabat_penetap', $pejabat_default) }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>NIP Pejabat</label>
                                    <input type="text" name="nip_pejabat" class="form-control" 
                                           value="{{ old('nip_pejabat') }}" placeholder="NIP Pejabat">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>SK Pangkat Nomor</label>
                                    <input type="text" name="sk_pangkat_nomor" class="form-control" 
                                           value="{{ old('sk_pangkat_nomor', $riwayat_pangkat->nomor_sk ?? '-') }}">
                                    <small class="text-muted">Dari riwayat pangkat terakhir</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>SK Pangkat Tanggal</label>
                                    <input type="date" name="sk_pangkat_tanggal" class="form-control" 
                                           value="{{ old('sk_pangkat_tanggal', optional($riwayat_pangkat)->tanggal_sk ? optional($riwayat_pangkat->tanggal_sk)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Pejabat SK Pangkat</label>
                                    <input type="text" name="sk_pangkat_pejabat" class="form-control" 
                                           value="{{ old('sk_pangkat_pejabat', $riwayat_pangkat->pejabat_penetap ?? 'Kepala Instansi') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TMT Gaji SK Pangkat</label>
                                    <input type="date" name="sk_pangkat_tmt_gaji" class="form-control" 
                                           value="{{ old('sk_pangkat_tmt_gaji', optional($riwayat_pangkat)->tmt_mulai ? optional($riwayat_pangkat->tmt_mulai)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Masa Kerja Golongan (SK Pangkat)</label>
                                    <input type="text" name="sk_pangkat_masa_kerja" class="form-control" 
                                           value="{{ old('sk_pangkat_masa_kerja', $masa_kerja_golongan ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ TOMBOL ============ --}}
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('kgb.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success float-right">
                            <i class="fas fa-file-word"></i> Generate SK KGB
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection