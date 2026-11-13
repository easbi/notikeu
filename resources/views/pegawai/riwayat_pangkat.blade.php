@extends('template')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-history"></i> Riwayat Pangkat</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('pegawai.edit', $pegawai->id) }}">Edit Pegawai</a>
                            </li>
                            <li class="breadcrumb-item active">Riwayat Pangkat</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Histori SK Pangkat</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>TMT Mulai</th>
                                            <th>Golongan</th>
                                            <th>Pangkat</th>
                                            <th>Nomor SK</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pegawai->riwayatPangkat as $r)
                                            <tr>
                                                <td>{{ $r->tmt_mulai->format('d-m-Y') }}</td>
                                                <td><span class="badge badge-secondary">{{ $r->golongan }}</span></td>
                                                <td>{{ $r->pangkat }}</td>
                                                <td>{{ $r->nomor_sk }}</td>
                                                <td>
                                                    <a href="{{ route('pegawai.edit-riwayat-pangkat', $r->id) }}"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('pegawai.destroy-riwayat-pangkat', $r->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"><i
                                                                class="fas fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-success">
                            <div class="card-header">
                                <h5 class="mb-0">Tambah Riwayat</h5>
                            </div>
                            <form action="{{ route('pegawai.store-riwayat-pangkat', $pegawai->id) }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Golongan</label>
                                        <select name="golongan" id="golongan" class="form-control" required
                                            onchange="updatePangkat()">
                                            <option value="">-- Pilih Golongan --</option>
                                            <option value="II/a">II/a</option>
                                            <option value="II/b">II/b</option>
                                            <option value="II/c">II/c</option>
                                            <option value="II/d">II/d</option>
                                            <option value="III/a">III/a</option>
                                            <option value="III/b">III/b</option>
                                            <option value="III/c">III/c</option>
                                            <option value="III/d">III/d</option>
                                            <option value="IV/a">IV/a</option>
                                            <option value="IV/b">IV/b</option>
                                            <option value="IV/c">IV/c</option>
                                            <option value="IV/d">IV/d</option>
                                            <option value="IV/e">IV/e</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Pangkat</label>
                                        <select name="pangkat" id="pangkat" class="form-control" required>
                                            <option value="">-- Pilih Pangkat --</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>TMT Mulai</label>
                                        <input type="date" name="tmt_mulai" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nomor SK</label>
                                        <input type="text" name="nomor_sk" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal SK</label>
                                        <input type="date" name="tanggal_sk" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Pejabat Penetap</label>
                                        <input type="text" name="pejabat_penetap" class="form-control" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success btn-block">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updatePangkat() {
                const golongan = document.getElementById('golongan').value;
                const pangkatSelect = document.getElementById('pangkat');

                // Data mapping Golongan ke Pangkat
                const dataPangkat = {
                    'II/a': 'Pengatur Muda',
                    'II/b': 'Pengatur Muda Tingkat 1',
                    'II/c': 'Pengatur',
                    'II/d': 'Pengatur Tingkat 1',
                    'III/a': 'Penata Muda',
                    'III/b': 'Penata Muda Tingkat 1',
                    'III/c': 'Penata',
                    'III/d': 'Penata Tingkat 1',
                    'IV/a': 'Pembina',
                    'IV/b': 'Pembina Tingkat 1',
                    'IV/c': 'Pembina Utama Muda',
                    'IV/d': 'Pembina Utama Madya',
                    'IV/e': 'Pembina Utama'
                };

                // Reset opsi pangkat
                pangkatSelect.innerHTML = '<option value="">-- Pilih Pangkat --</option>';

                // Jika golongan dipilih, tambahkan opsi pangkat yang sesuai
                if (golongan && dataPangkat[golongan]) {
                    const option = document.createElement('option');
                    option.value = dataPangkat[golongan];
                    option.text = dataPangkat[golongan];
                    option.selected = true; // Otomatis terpilih
                    pangkatSelect.appendChild(option);
                }
            }
        </script>
    @endpush
@endsection
