@extends('template')

@section('content')
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Rekening Pegawai</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->


        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h4 class="card-title">Edit Data Rekening Pembayaran :</h4>
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <form action="{{ route('pegawai.update',$pegawai->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="card-body">
                                    <div class="form-group">
                                        <strong>Nama Pegawai :</strong>
                                        <input type="text" name="fullname" class="form-control" placeholder="Nama Pegawai" value="{{$pegawai->fullname}}">
                                    </div>
                                    <div class="form-group">
                                        <strong>Rekening BSI:</strong>
                                        <input type="text" name="no_rek_bsi" class="form-control" value="{{$pegawai->no_rek_bsi}}">
                                    </div>
                                    <div class="form-group">
                                        <strong>Rekening BNI:</strong>
                                        <input type="text" name="no_rek_bni" class="form-control" value="{{$pegawai->no_rek_bni}}">
                                    </div>
                                    <div class="form-group">
                                        <strong>Rekening BSI:</strong>
                                        <input type="text" name="no_rek_bri" class="form-control" value="{{$pegawai->no_rek_bri}}">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Edit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
