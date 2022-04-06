@extends('template')
 
@section('content')
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Input Data Pembayaran</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Input Data</li>
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
                                <h4 class="card-title">Input Data Pembayaran :</h4>                         
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <strong>Nama Pembayaran :</strong>
                                        <select class="form-control" id="id_pembayaran" name="id_pembayaran">
                                            <option value="" selected disabled>Select</option>
                                            @foreach($jenispembayaran as $jp)
                                            <option value="{{$jp->id}}"> {{$jp->nama_pembayaran}} {{$jp->bulan}} {{$jp->tahun}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <strong>Nama Pegawai :</strong>
                                        <select class="form-control" id="id_pegawai" name="id_pegawai">
                                            <option value="" selected disabled>Select</option>
                                            @foreach($pegawai as $peg)
                                            <option value="{{$peg->id}}"> {{$peg->fullname}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <strong><label>Nominal Bersih :</label></strong>
                                        <input type="text" name="bersih" class="form-control" placeholder="10000">
                                    </div>
                                    <div class="form-group">
                                        <strong><label>Nominal Potongan :</label></strong>
                                        <input type="text" name="potongan" class="form-control" placeholder="10000">
                                    </div>  
                                    <div class="form-group">
                                        <strong><label>Nominal Yang Dibayarkan :</label></strong>
                                        <input type="text" name="jumlah_bayar" class="form-control" placeholder="10000">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Kirim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
