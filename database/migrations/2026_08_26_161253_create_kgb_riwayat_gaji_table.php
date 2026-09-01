<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kgb_riwayat_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');

            $table->decimal('gaji_pokok', 15, 2);
            $table->date('tmt_berlaku');
            $table->enum('jenis', ['PANGKAT', 'KGB', 'PP', 'LAINNYA'])->default('KGB');

            $table->string('nomor_sk', 50);
            $table->date('tanggal_sk');
            $table->string('pejabat_penetap', 100);
            $table->string('dasar_peraturan', 50)->nullable();

            $table->timestamps();

            $table->index('pegawai_id');
            $table->index('tmt_berlaku');
            $table->index('jenis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kgb_riwayat_gaji');
    }
};
