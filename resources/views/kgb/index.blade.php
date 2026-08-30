@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-file-contract"></i> Pengurusan KGB</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Pengurusan KGB</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <i class="fas fa-check-circle"></i> {{ $message }}
                </div>
            @endif

            @if ($message = Session::get('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </div>
            @endif

            {{-- ============ STATISTIK ============ --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['total_ongoing'] ?? 0 }}</h3>
                            <p>On Going</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['total_selesai'] ?? 0 }}</h3>
                            <p>Selesai</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['total_pegawai'] ?? 0 }}</h3>
                            <p>Total Pegawai</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $stats['total_bulan_ini'] ?? 0 }}</h3>
                            <p>Bulan Ini</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ ON GOING ============ --}}
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-spinner"></i> On Going</h5>
                </div>
                <div class="card-body">
                    @if($onGoing->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>NIP</th>
                                        <th>Golongan</th>
                                        <th>TMT KGB</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($onGoing as $key => $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->nama }}</td>
                                        <td>{{ $item->nip }}</td>
                                        <td><span class="badge badge-secondary">{{ $item->golongan }}</span></td>
                                        <td>{{ $item->tmt_kgb_baru->format('d-m-Y') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $item->status_badge }}">
                                                {{ $item->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('kgb.show', $item->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($item->status == 'pending')
                                            <a href="{{ route('kgb.proses-form', $item->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-edit"></i> Proses
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted py-3">
                            <i class="fas fa-check-circle text-success"></i> Tidak ada pengurusan on going
                        </p>
                    @endif
                </div>
            </div>

            {{-- ============ RIWAYAT ============ --}}
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat</h5>
                </div>
                <div class="card-body">
                    @if($riwayat->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>NIP</th>
                                        <th>TMT KGB</th>
                                        <th>Nomor SK</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riwayat as $key => $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->nama }}</td>
                                        <td>{{ $item->nip }}</td>
                                        <td>{{ $item->tmt_kgb_baru->format('d-m-Y') }}</td>
                                        <td><span class="badge badge-success">{{ $item->nomor_sk ?? '-' }}</span></td>
                                        <td>{{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d-m-Y') : '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('kgb.show', $item->id) }}" class="btn btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($item->status == 'selesai')
                                                    <a href="{{ route('kgb.word', $item->id) }}" class="btn btn-primary" title="Download Word" target="_blank">
                                                        <i class="fas fa-file-word"></i>
                                                    </a>
                                                    <a href="{{ route('kgb.preview', $item->id) }}" class="btn btn-secondary" title="Preview" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted">Belum ada riwayat</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection