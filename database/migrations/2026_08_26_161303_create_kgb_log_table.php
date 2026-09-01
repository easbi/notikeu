<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kgb_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kgb_pengurusan_id')->constrained('kgb_pengurusan')->onDelete('cascade');
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');

            $table->string('nip', 18);
            $table->string('nama', 100);

            $table->string('aktivitas', 50); // pengajuan, proses, selesai, update_pangkat, update_gaji
            $table->text('deskripsi');

            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();

            $table->foreignId('dilakukan_oleh')->constrained('users');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('waktu')->useCurrent();

            $table->index('kgb_pengurusan_id');
            $table->index('pegawai_id');
            $table->index('aktivitas');
            $table->index('waktu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kgb_log');
    }
};
