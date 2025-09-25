@extends('template')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Pegawai</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Pegawai</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
                <div class="row mb-2">
                </div>
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
                                    <th>Nama Pegawai</th>
                                    <th>Nomor Rekening BSI</th>
                                    <th>Nomor Rekening BNI</th>
                                    <th>Nomor Rekening BRI</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pegawai as $pb)
                                    <tr>
                                        <td class="text-center">{{ ++$i }}</td>
                                        <td>{{ $pb->fullname }}</td>
                                        <td>{{ $pb->no_rek_bsi }}</td>
                                        <td>{{ $pb->no_rek_bni }}</td>
                                        <td>{{ $pb->no_rek_bri }}</td>
                                        <td class="text-center">
                                            <a class="btn btn-primary btn-sm"
                                                href="{{ route('pegawai.edit', $pb->id) }}">Edit</a>
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
