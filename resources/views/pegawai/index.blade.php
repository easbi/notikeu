@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-users"></i> Daftar Pegawai</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Pegawai</li>
                    </ol>
                </div>
            </div>
            @if(auth()->id() == 1)
            <div class="row mb-2">
                <div class="col-sm-12">
                    <a href="{{ route('pegawai.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Tambah Pegawai
                    </a>
                    <a href="{{ route('kgb.dashboard') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-chart-line"></i> Dashboard KGB
                    </a>
                </div>
            </div>
            @endif
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

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Daftar Pegawai
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $pegawai->count() }} Pegawai</span>
                        <span class="badge badge-success">{{ $pegawai->where('status_aktif', true)->count() }} Aktif</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tblPegawai" class="table table-bordered table-hover table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:5%;">No</th>
                                    <th style="width:20%;">Nama</th>
                                    <th style="width:15%;">NIP</th>
                                    <th class="text-center" style="width:10%;">Golongan</th>
                                    <th style="width:15%;">Pangkat</th>
                                    <th class="text-center" style="width:12%;">TMT Pangkat</th>
                                    <th class="text-right" style="width:12%;">Gaji</th>
                                    <th class="text-center" style="width:8%;">Status</th>
                                    <th class="text-center" style="width:13%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pegawai as $key => $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $item->fullname }}</strong>
                                        @if($item->gelar)
                                            <br><small class="text-muted"><i class="fas fa-graduation-cap"></i> {{ $item->gelar }}</small>
                                        @endif
                                    </td>
                                    <td><span class="text-monospace">{{ $item->nip }}</span></td>
                                    <td class="text-center">
                                        @if($item->golongan)
                                            <span class="badge badge-secondary">{{ $item->golongan }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->pangkat ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($item->tmt_pangkat_terakhir)
                                            {{ \Carbon\Carbon::parse($item->tmt_pangkat_terakhir)->format('d-m-Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($item->gaji_pokok_saat_ini)
                                            <span class="text-success font-weight-bold">
                                                Rp {{ number_format($item->gaji_pokok_saat_ini, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->status_aktif)
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                                        @else
                                            <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('pegawai.show', $item->id) }}" class="btn btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('pegawai.edit', $item->id) }}" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(auth()->id() == 1 && $item->status_aktif)
                                            <button type="button" class="btn btn-danger" title="Nonaktifkan" onclick="confirmNonaktif({{ $item->id }}, '{{ addslashes($item->fullname) }}')">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                            <form id="delete-form-{{ $item->id }}" action="{{ route('pegawai.destroy', $item->id) }}" method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div class="row">
                        <div class="col-sm-6">
                            <span class="text-muted">Menampilkan {{ $pegawai->count() }} data</span>
                        </div>
                        <div class="col-sm-6 text-right">
                            @if(method_exists($pegawai, 'links'))
                                {{ $pegawai->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script>
$(document).ready(function() {
    // Hancurkan instance sebelumnya jika ada
    if ($.fn.DataTable.isDataTable('#tblPegawai')) {
        $('#tblPegawai').DataTable().destroy();
    }

    var table = $('#tblPegawai').DataTable({
        "scrollX": true,
        "responsive": true,
        "pageLength": 10,
        "order": [[1, 'asc']],
        "autoWidth": false,
        "language": {
            "emptyTable": "Tidak ada data pegawai",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "zeroRecords": "Tidak ada data yang cocok"
        },
        "columnDefs": [
            { "orderable": false, "targets": [8] },
            { "className": "text-center", "targets": [0, 3, 5, 7, 8] },
            { "className": "text-right", "targets": [6] }
        ]
    });

    // Refresh setelah render
    setTimeout(function() {
        table.columns.adjust().draw();
    }, 200);

    $(window).on('resize', function() {
        table.columns.adjust();
    });
});

function confirmNonaktif(id, nama) {
    Swal.fire({
        title: 'Nonaktifkan Pegawai?',
        html: 'Yakin ingin menonaktifkan <strong>' + nama + '</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Nonaktifkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endpush