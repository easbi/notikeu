<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // Tambahkan field baru tanpa merujuk ke 'jabatan'
            $table->string('gelar', 50)->nullable()->after('fullname');
            $table->string('golongan', 10)->nullable()->after('unit_kerja'); // unit_kerja pasti ada
            $table->string('pangkat', 50)->nullable()->after('golongan');
            $table->string('kode_instansi', 10)->default('13741')->after('organisasi');

            // TMT
            $table->date('tmt_pangkat_terakhir')->nullable()->after('pangkat');
            $table->date('tmt_kgb_terakhir')->nullable()->after('tmt_pangkat_terakhir');
            $table->date('tmt_gaji_terakhir')->nullable()->after('tmt_kgb_terakhir');

            // Gaji
            $table->decimal('gaji_pokok_saat_ini', 15, 2)->nullable()->after('tmt_gaji_terakhir');

            // Status
            $table->boolean('status_aktif')->default(true)->after('gaji_pokok_saat_ini');

            // Index
            $table->index('golongan');
            $table->index('tmt_pangkat_terakhir');
            $table->index('tmt_kgb_terakhir');
            $table->index('status_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn([
                'gelar', 'golongan', 'pangkat', 'kode_instansi',
                'tmt_pangkat_terakhir', 'tmt_kgb_terakhir', 'tmt_gaji_terakhir',
                'gaji_pokok_saat_ini', 'status_aktif'
            ]);
        });
    }
};
