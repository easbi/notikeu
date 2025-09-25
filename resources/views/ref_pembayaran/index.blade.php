@extends('template')


@php
    // daftar admin berdasarkan pegawai.id
    $adminIds = [1, 2, 4, 15];
    // cari pegawai dari user yang login
    $pegawaiUser = \App\Models\Pegawai::where('nip', Auth::user()->nip)->first();
@endphp

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Jenis Pembayaran</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Jenis Pembayaran</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
                @if ($pegawaiUser && in_array($pegawaiUser->id, $adminIds))
                    <div class="row mb-2">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6">
                            <a href="{{ url('/refpembayaran/create') }}" class="btn btn-primary float-sm-right">Input Jenis
                                Pembayaran</a>
                        </div>
                    </div>
                @endif
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        <p>{{ $message }}</p>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <table id="example" class="display responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama Pembayaran</th>
                                    <th>Bulan</th>
                                    <th>Tahun</th>
                                    <th>Berkas</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($refpe as $rp)
                                    <tr>
                                        <td class="text-center">{{ ++$i }}</td>
                                        <td>{{ $rp->nama_pembayaran }}</td>
                                        <td>{{ $rp->bulan }}</td>
                                        <td>{{ $rp->tahun }}</td>
                                        <td><a class="btn btn-primary btn-sm" href="">berkas</a></td>
                                        <td class="text-center">
                                            @if ($pegawaiUser && in_array($pegawaiUser->id, $adminIds))
                                                <form action="{{ route('refpembayaran.destroy', $rp->id) }}" method="POST">

                                                    <a class="btn btn-primary btn-sm"
                                                        href="{{ route('refpembayaran.edit', $rp->id) }}">Edit</a>

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</button>

                                                </form>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable({
                "scrollX": true,
                responsive: true
            });
        });
    </script>
@endpush
