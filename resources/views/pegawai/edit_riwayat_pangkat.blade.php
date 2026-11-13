@extends('template')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-edit"></i> Edit Riwayat Pangkat</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pegawai.riwayat-pangkat', $riwayat->pegawai_id) }}">Riwayat Pangkat</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h5 class="mb-0">Form Edit Riwayat Pangkat</h5>
                        </div>
                        <form action="{{ route('pegawai.update-riwayat-pangkat', $riwayat->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    {{-- Baris 1 --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Golongan</label>
                                            <select name="golongan" id="golongan" class="form-control" required onchange="updatePangkat()">
                                                <option value="">-- Pilih Golongan --</option>
                                                @foreach(['II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','IV/d','IV/e'] as $gol)
                                                    <option value="{{ $gol }}" {{ $riwayat->golongan == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Pangkat</label>
                                            <select name="pangkat" id="pangkat" class="form-control" required>
                                                <option value="{{ $riwayat->pangkat }}">{{ $riwayat->pangkat }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Baris 2 --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>TMT Mulai</label>
                                            <input type="date" name="tmt_mulai" class="form-control" value="{{ $riwayat->tmt_mulai->format('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Nomor SK</label>
                                            <input type="text" name="nomor_sk" class="form-control" value="{{ $riwayat->nomor_sk }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tanggal SK</label>
                                            <input type="date" name="tanggal_sk" class="form-control" value="{{ $riwayat->tanggal_sk->format('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Baris 3 --}}
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Pejabat Penetap</label>
                                            <input type="text" name="pejabat_penetap" class="form-control" value="{{ $riwayat->pejabat_penetap }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('pegawai.riwayat-pangkat', $riwayat->pegawai_id) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-warning float-right">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePangkat() {
    const golongan = document.getElementById('golongan').value;
    const pangkatSelect = document.getElementById('pangkat');

    const dataPangkat = {
        'II/a': 'Pengatur Muda', 'II/b': 'Pengatur Muda Tingkat 1',
        'II/c': 'Pengatur', 'II/d': 'Pengatur Tingkat 1',
        'III/a': 'Penata Muda', 'III/b': 'Penata Muda Tingkat 1',
        'III/c': 'Penata', 'III/d': 'Penata Tingkat 1',
        'IV/a': 'Pembina', 'IV/b': 'Pembina Tingkat 1',
        'IV/c': 'Pembina Utama Muda', 'IV/d': 'Pembina Utama Madya',
        'IV/e': 'Pembina Utama'
    };

    pangkatSelect.innerHTML = '<option value="">-- Pilih Pangkat --</option>';

    if (golongan && dataPangkat[golongan]) {
        const option = document.createElement('option');
        option.value = dataPangkat[golongan];
        option.text = dataPangkat[golongan];
        option.selected = true;
        pangkatSelect.appendChild(option);
    }
}
</script>
@endsection
