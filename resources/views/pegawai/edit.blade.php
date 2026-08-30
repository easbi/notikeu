@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-edit"></i> Edit Pegawai</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>Whoops!</strong> Ada masalah dengan input Anda.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <i class="fas fa-check-circle"></i> {{ $message }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-user"></i> Edit Data Pegawai :
                                <strong>{{ $pegawai->fullname }}</strong>
                            </h4>
                            <div class="card-tools">
                                <span class="badge badge-info">{{ $pegawai->nip }}</span>
                            </div>
                        </div>

                        <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                {{-- ============ DATA DIRI ============ --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card card-outline card-primary">
                                            <div class="card-header">
                                                <h5 class="card-title"><i class="fas fa-id-card"></i> Data Diri</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                                                    <input type="text" name="fullname" class="form-control" value="{{ old('fullname', $pegawai->fullname) }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>NIP</label>
                                                    <input type="text" class="form-control" value="{{ $pegawai->nip }}" disabled>
                                                    <small class="text-muted">NIP tidak dapat diubah</small>
                                                </div>
                                                <div class="form-group">
                                                    <label>No HP <span class="text-danger">*</span></label>
                                                    <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $pegawai->no_hp) }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ============ REKENING ============ --}}
                                    <div class="col-md-6">
                                        <div class="card card-outline card-secondary">
                                            <div class="card-header">
                                                <h5 class="card-title"><i class="fas fa-wallet"></i> Data Rekening</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Rekening BSI</label>
                                                    <input type="text" name="no_rek_bsi" class="form-control" value="{{ old('no_rek_bsi', $pegawai->no_rek_bsi) }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Rekening BNI</label>
                                                    <input type="text" name="no_rek_bni" class="form-control" value="{{ old('no_rek_bni', $pegawai->no_rek_bni) }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Rekening BRI</label>
                                                    <input type="text" name="no_rek_bri" class="form-control" value="{{ old('no_rek_bri', $pegawai->no_rek_bri) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ============ DATA KEPEGAWAIAN (HANYA ADMIN) ============ --}}
                                @if(auth()->id() == 1)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card card-outline card-success">
                                            <div class="card-header">
                                                <h5 class="card-title"><i class="fas fa-briefcase"></i> Data Kepegawaian <span class="text-muted">(Hanya Admin)</span></h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Organisasi/Instansi</label>
                                                            <input type="text" name="organisasi" class="form-control" value="{{ old('organisasi', $pegawai->organisasi) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Unit Kerja</label>
                                                            <input type="text" name="unit_kerja" class="form-control" value="{{ old('unit_kerja', $pegawai->unit_kerja) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Kode Instansi</label>
                                                            <input type="text" name="kode_instansi" class="form-control" value="{{ old('kode_instansi', $pegawai->kode_instansi ?? '13741') }}">
                                                        </div>
                                                    </div>                                                    
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Jabatan <span class="text-danger">*</span></label>
                                                            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $pegawai->jabatan ?? '-') }}" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Golongan <span class="text-danger">*</span></label>
                                                            <input type="text" name="golongan" class="form-control" value="{{ old('golongan', $pegawai->golongan) }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Pangkat <span class="text-danger">*</span></label>
                                                            <input type="text" name="pangkat" class="form-control" value="{{ old('pangkat', $pegawai->pangkat) }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>TMT Pangkat <span class="text-danger">*</span></label>
                                                            <input type="date" name="tmt_pangkat_terakhir" class="form-control" value="{{ old('tmt_pangkat_terakhir', optional($pegawai->tmt_pangkat_terakhir)->format('Y-m-d')) }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Gaji Pokok Terakhir<span class="text-danger">*</span></label>
                                                            <input type="number" name="gaji_pokok_saat_ini" class="form-control" value="{{ old('gaji_pokok_saat_ini', $pegawai->gaji_pokok_saat_ini) }}" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Nomor SK Pangkat</label>
                                                            <input type="text" name="nomor_sk_pangkat" class="form-control" value="{{ old('nomor_sk_pangkat', $riwayatPangkatTerakhir->nomor_sk ?? $riwayatGajiTerakhir->nomor_sk ?? '') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Tanggal SK Pangkat</label>
                                                            <input type="date" name="tanggal_sk_pangkat" class="form-control" 
                                                                value="{{ old('tanggal_sk_pangkat', optional($riwayatPangkatTerakhir)->tanggal_sk ? optional($riwayatPangkatTerakhir)->tanggal_sk->format('Y-m-d') : '') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Pejabat Penetap</label>
                                                            <input type="text" name="pejabat_penetap" class="form-control" value="{{ old('pejabat_penetap', $riwayatPangkatTerakhir->pejabat_penetap ?? $riwayatGajiTerakhir->pejabat_penetap ?? 'Kepala Instansi') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ============ INFO TAMBAHAN (ADMIN) ============ --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card card-outline card-info">
                                            <div class="card-header">
                                                <h5 class="card-title"><i class="fas fa-calendar"></i> Info KGB</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm table-bordered">
                                                    <tr>
                                                        <th width="50%">TMT CPNS (dari NIP)</th>
                                                        <td>{{ $pegawai->tmt_cpns ? $pegawai->tmt_cpns->format('d-m-Y') : '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Masa Kerja Golongan</th>
                                                        <td>{{ $pegawai->mkg_golongan }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>TMT KGB Berikutnya</th>
                                                        <td>
                                                            @if($pegawai->tmt_kgb_berikutnya)
                                                                <span class="badge badge-warning">{{ $pegawai->tmt_kgb_berikutnya->format('d-m-Y') }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Gaji Pokok Baru (estimasi)</th>
                                                        <td>
                                                            @if($pegawai->gaji_pokok_baru)
                                                                Rp {{ number_format($pegawai->gaji_pokok_baru, 0, ',', '.') }}
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card card-outline card-warning">
                                            <div class="card-header">
                                                <h5 class="card-title"><i class="fas fa-clock"></i> Status</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm table-bordered">
                                                    <tr>
                                                        <th width="50%">Status</th>
                                                        <td>
                                                            @if($pegawai->status_aktif)
                                                                <span class="badge badge-success">Aktif</span>
                                                            @else
                                                                <span class="badge badge-danger">Nonaktif</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>TMT KGB Terakhir</th>
                                                        <td>{{ $pegawai->tmt_kgb_terakhir ? $pegawai->tmt_kgb_terakhir->format('d-m-Y') : 'Belum pernah KGB' }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="card-footer">
                                <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary float-right">
                                    <i class="fas fa-save"></i> Update Data
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