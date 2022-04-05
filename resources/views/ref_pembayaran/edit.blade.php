@extends('template')
 
@section('content')
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Edit Master Survei</h1>
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
                                <h4 class="card-title">Edit Data Survei :</h4>                         
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <form action="{{ route('survei.update',$jenissurvei->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="card-body">
                                    <div class="form-group">
                                        <strong>Nama Survei :</strong>
                                        <input type="text" name="nama_ss" value="{{ $jenissurvei->nama_ss }}" class="form-control" placeholder="Title">
                                    </div>
                                    <div class="form-group">
                                        <strong>Alias :</strong>
                                        <input type="text" name="alias_ss" value="{{ $jenissurvei->alias_ss }}" class="form-control" placeholder="Title">
                                    </div>
                                    <div class="form-group">
                                        <strong>Tanggal Mulai :</strong>
                                        <input type="text" name="tgl_mulai" value="{{ $jenissurvei->tgl_mulai }}" class="form-control" placeholder="Title">
                                    </div>
                                    <div class="form-group">
                                        <strong>Tanggal Selesai :</strong>
                                        <input type="text" name="tgl_selesai" value="{{ $jenissurvei->tgl_selesai }}" class="form-control" placeholder="Title">
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