<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kgb_riwayat_pangkat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');

            $table->string('golongan', 10);
            $table->string('pangkat', 50);
            $table->string('jabatan', 100);

            $table->date('tmt_mulai');
            $table->string('nomor_sk', 50);
            $table->date('tanggal_sk');
            $table->string('pejabat_penetap', 100);
            $table->string('masa_kerja_golongan', 20)->nullable();

            $table->timestamps();

            $table->index('pegawai_id');
            $table->index('tmt_mulai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kgb_riwayat_pangkat');
    }
};
