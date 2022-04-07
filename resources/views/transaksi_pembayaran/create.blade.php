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
