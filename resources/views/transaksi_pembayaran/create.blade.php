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
                <div class="row mb-2">
                    <div class="col-sm-6">                        
                        <ol class="breadcrumb float-sm-left">
                            <a href="{{ url('/pembayaran/create') }}" class="btn btn-primary float-sm-right">Import Data</a>
                        </ol>
                    </div>
                    
                </div>
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
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Import Export Excel</h5>
                                <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-folder-open"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Import Data</span>
                                                <span class="info-box-number">Klik tombol di bawah ini untuk mengupload file Excel yang berisi data Pembayaran.</span>
                                                <span class="info-box-number"><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importExcel2">IMPORT EXCEL</button></span>
                                            </div>
                                            
                                            <!-- Import Excel Modal -->
                                            <div class="modal fade" id="importExcel2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <form action="{{route('pembayaran.import')}}" method="POST" enctype="multipart/form-data">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLabel2">Import Data Excel Pembayaran</h5>
                                                            </div>
                                                            <div class="modal-body"> 
                                                                @csrf
                                                                <label>Pilih file excel</label>
                                                                <div class="form-group">
                                                                    <input type="file" name="file_pembayaran" required="required">
                                                                </div> 
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Import</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                        <strong><label>Nominal Kotor :</label></strong>
                                        <input type="text" id="rupiah1" name="bersih" class="form-control" placeholder="10000">
                                    </div>
                                    <div class="form-group">
                                        <strong><label>Nominal Potongan :</label></strong>
                                        <input type="text" id="rupiah2" name="potongan" class="form-control" placeholder="10000">
                                    </div>  
                                    <div class="form-group">
                                        <strong><label>Nominal Yang Dibayarkan :</label></strong>
                                        <input type="text" id="rupiah3" name="jumlah_bayar" class="form-control" placeholder="10000">
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

    <script type="text/javascript">     
        var rupiah1 = document.getElementById('rupiah1');
        rupiah1.addEventListener('keyup', function(e){
            // tambahkan 'Rp.' pada saat form di ketik
            // gunakan fungsi formatRupiah() untuk mengubah angka yang di ketik menjadi format angka
            rupiah1.value = formatRupiah(this.value, 'Rp. ');
        });

        var rupiah2 = document.getElementById('rupiah2');
        rupiah2.addEventListener('keyup', function(e){
            rupiah2.value = formatRupiah(this.value, 'Rp. ');
        });

        var rupiah3 = document.getElementById('rupiah3');
        rupiah3.addEventListener('keyup', function(e){
            rupiah3.value = formatRupiah(this.value, 'Rp. ');
        });
 
        /* Fungsi formatRupiah */
        function formatRupiah(angka, prefix){
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split           = number_string.split(','),
            sisa            = split[0].length % 3,
            rupiah          = split[0].substr(0, sisa),
            ribuan          = split[0].substr(sisa).match(/\d{3}/gi);
 
            // tambahkan titik jika yang di input sudah menjadi angka ribuan
            if(ribuan){
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
 
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        }
    </script>
@endsection
