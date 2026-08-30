<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_gaji_pokok', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_pegawai', ['PNS', 'PPPK'])->comment('Pembeda tabel 1 (PPPK) dan tabel 2 (PNS)');
            $table->string('golongan', 10)->comment('Format: III/a, IV/b (PNS) ATAU IX, XVII (PPPK)');
            $table->integer('mkg')->unsigned()->comment('Masa Kerja Golongan (0 sampai 32)');
            $table->decimal('nominal_gaji', 15, 2)->comment('Nominal gaji dalam Rupiah');
            $table->year('tahun_berlaku')->default(2024)->comment('Tahun dasar aturan');
            $table->timestamps();
            
            $table->unique(['jenis_pegawai', 'golongan', 'mkg', 'tahun_berlaku'], 'uniq_kombinasi');
            $table->index(['jenis_pegawai', 'golongan', 'mkg'], 'idx_cari_gaji');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_gaji_pokok');
    }
};