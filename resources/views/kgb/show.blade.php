@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-file-contract"></i> Detail Pengurusan KGB</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('kgb.index') }}">Pengurusan KGB</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    {{-- DATA PEGAWAI --}}
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Data Pegawai</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <tr><th width="35%">Nama</th><td>{{ $pengurusan->nama }}</td></tr>
                                <tr><th>NIP</th><td>{{ $pengurusan->nip }}</td></tr>
                                <tr><th>Golongan</th><td>{{ $pengurusan->golongan }}</td></tr>
                                <tr><th>Pangkat</th><td>{{ $pengurusan->pangkat }}</td></tr>
                                <tr><th>Jabatan</th><td>{{ $pengurusan->jabatan }}</td></tr>
                                <tr><th>Instansi</th><td>{{ $pengurusan->instansi }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    {{-- DATA KGB --}}
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Data KGB</h5>
                            <span class="badge badge-{{ $pengurusan->status_badge }} float-right">
                                {{ $pengurusan->status_label }}
                            </span>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <tr><th width="40%">TMT KGB Lama</th><td>{{ $pengurusan->tmt_kgb_lama ? $pengurusan->tmt_kgb_lama->format('d-m-Y') : '-' }}</td></tr>
                                <tr><th>TMT KGB Baru</th><td><strong>{{ $pengurusan->tmt_kgb_baru->format('d-m-Y') }}</strong></td></tr>
                                <tr><th>Kenaikan y.a.d</th><td>{{ $pengurusan->tmt_kgb_berikutnya->format('d-m-Y') }}</td></tr>
                                <tr><th>Gaji Pokok Lama</th><td>{{ $pengurusan->gaji_lama_formatted }}</td></tr>
                                <tr><th>Gaji Pokok Baru</th><td><strong>{{ $pengurusan->gaji_baru_formatted }}</strong></td></tr>
                                <tr><th>Dasar Peraturan</th><td>{{ $pengurusan->dasar_peraturan }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SK KGB --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> SK KGB</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <tr><th width="20%">Nomor SK</th><td><strong>{{ $pengurusan->nomor_sk ?? 'Belum dibuat' }}</strong></td></tr>
                                <tr><th>Tanggal SK</th><td>{{ $pengurusan->tanggal_sk ? $pengurusan->tanggal_sk->format('d-m-Y') : '-' }}</td></tr>
                                <tr><th>Pejabat Penetap</th><td>{{ $pengurusan->pejabat_penetap ?? '-' }}</td></tr>
                                <tr><th>Masa Kerja Golongan</th><td>{{ $pengurusan->masa_kerja_golongan }}</td></tr>
                                <tr><th>Masa Kerja KGB</th><td>{{ $pengurusan->masa_kerja_kgb }}</td></tr>
                            </table>

                            @if($pengurusan->status == 'selesai' && $pengurusan->nomor_sk)
                            <div class="mt-3">
                                <a href="{{ route('kgb.pdf', $pengurusan->id) }}" class="btn btn-danger" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Download SK
                                </a>
                                <a href="{{ route('kgb.preview', $pengurusan->id) }}" class="btn btn-info" target="_blank">
                                    <i class="fas fa-eye"></i> Preview
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- LOG AKTIVITAS --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-list"></i> Log Aktivitas</h5>
                        </div>
                        <div class="card-body">
                            @if($pengurusan->logs->count() > 0)
                                <div class="timeline">
                                    @foreach($pengurusan->logs as $log)
                                    <div class="time-label">
                                        <span class="bg-info">{{ $log->waktu->format('d-m-Y H:i') }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-user bg-primary"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="fas fa-clock"></i> {{ $log->waktu->diffForHumans() }}</span>
                                            <h3 class="timeline-header">
                                                <strong>{{ $log->nama }}</strong>
                                                <span class="badge badge-secondary">{{ $log->aktivitas }}</span>
                                            </h3>
                                            <div class="timeline-body">
                                                {{ $log->deskripsi }}
                                                @if($log->dilakukan_oleh)
                                                    <br><small class="text-muted">Oleh: {{ $log->dilakukanOleh->name ?? 'System' }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-center text-muted">Belum ada log</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOMBOL --}}
            <div class="row mt-3">
                <div class="col-md-12">
                    <a href="{{ route('kgb.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    @if($pengurusan->status == 'pending')
                        <a href="{{ route('kgb.proses-form', $pengurusan->id) }}" class="btn btn-success">
                            <i class="fas fa-edit"></i> Proses KGB
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection