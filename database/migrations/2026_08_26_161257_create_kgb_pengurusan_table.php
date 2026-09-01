<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kgb_pengurusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');

            // Denormalisasi data pegawai
            $table->string('nip', 18);
            $table->string('nama', 100);
            $table->string('golongan', 10);
            $table->string('pangkat', 50);
            $table->string('jabatan', 100);
            $table->string('instansi', 100)->default('BPS Kota Padang Panjang');
            $table->string('kode_instansi', 10)->default('13741');

            // TMT KGB
            $table->date('tmt_kgb_lama')->nullable();
            $table->date('tmt_kgb_baru');
            $table->date('tmt_gaji_lama')->nullable();
            $table->date('tmt_gaji_baru');
            $table->date('tmt_kgb_berikutnya'); // y.a.d

            // Gaji
            $table->decimal('gaji_pokok_lama', 15, 2)->nullable();
            $table->decimal('gaji_pokok_baru', 15, 2);
            $table->string('dasar_peraturan', 50);
            $table->string('masa_kerja_golongan', 20);
            $table->string('masa_kerja_kgb', 20);

            // SK Pangkat Terakhir
            $table->string('sk_pangkat_nomor', 50);
            $table->date('sk_pangkat_tanggal');
            $table->string('sk_pangkat_pejabat', 100);
            $table->date('sk_pangkat_tmt_gaji');
            $table->string('sk_pangkat_masa_kerja', 20);

            // SK KGB yang dibuat
            $table->string('nomor_sk', 50)->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->string('pejabat_penetap', 100)->nullable();
            $table->string('nip_pejabat', 18)->nullable();

            // Status & Timestamp
            $table->enum('status', ['pending', 'proses', 'selesai'])->default('pending');
            $table->text('keterangan')->nullable();
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_selesai')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('tmt_kgb_baru');
            $table->index('nip');
            $table->index('tanggal_pengajuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kgb_pengurusan');
    }
};
