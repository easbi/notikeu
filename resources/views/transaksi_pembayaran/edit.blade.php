@extends('template')
 
@section('content')
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Jenis Pembayaran</h1>
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
                                <h4 class="card-title">Edit Data Jenis Pembayaran :</h4>                         
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <form action="{{ route('refpembayaran.update',$jenispembayaran->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="card-body">
                                    <div class="form-group">
                                        <strong>Nama Pembayaran :</strong>
                                        <input type="text" name="nama_pembayaran" class="form-control" placeholder="Nama Pembayaran" value="{{$jenispembayaran->nama_pembayaran}}">
                                    </div>
                                    <div class="form-group">
                                        <strong>Bulan :</strong>
                                        <select class="form-control" id="bulan" name="bulan">
                                            <option value='1' @if($jenispembayaran->bulan==1) selected @endif>Januari</option>
                                            <option value='2' @if($jenispembayaran->bulan==2) selected @endif>Februari</option>
                                            <option value='3' @if($jenispembayaran->bulan==3) selected @endif>Maret</option>
                                            <option value='4' @if($jenispembayaran->bulan==4) selected @endif>April</option>
                                            <option value='5' @if($jenispembayaran->bulan==5) selected @endif>Mei</option>
                                            <option value='6' @if($jenispembayaran->bulan==6) selected @endif>Juni</option>
                                            <option value='7' @if($jenispembayaran->bulan==7) selected @endif>Juli</option>
                                            <option value='8' @if($jenispembayaran->bulan==8) selected @endif>Agustus</option>
                                            <option value='9' @if($jenispembayaran->bulan==9) selected @endif>September</option>
                                            <option value='10' @if($jenispembayaran->bulan==10) selected @endif>Oktober</option>
                                            <option value='11' @if($jenispembayaran->bulan==11) selected @endif>November</option>
                                            <option value='12' @if($jenispembayaran->bulan==12) selected @endif>Desember</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <strong>Tahun :</strong>
                                        <select class="form-control" id="tahun" name="tahun">
                                            <option value='2022' @if($jenispembayaran->tahun==2022) selected @endif>2022</option>
                                            <option value='2023' @if($jenispembayaran->tahun==2023) selected @endif>2023</option>
                                            <option value='2024' @if($jenispembayaran->tahun==2024) selected @endif>2024</option>
                                            <option value='2025' @if($jenispembayaran->tahun==2025) selected @endif>2025</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <strong>File (jika ada) :</strong>
                                        <input type="file" name="berkas" class="form-control" >
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