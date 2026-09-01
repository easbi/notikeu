<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // Pengecekan kolom sebelum ditambahkan
            if (!Schema::hasColumn('pegawai', 'gelar')) {
                $table->string('gelar', 50)->nullable()->after('fullname');
            }

            if (!Schema::hasColumn('pegawai', 'golongan')) {
                $table->string('golongan', 10)->nullable()->after('unit_kerja');
                $table->index('golongan');
            }

            if (!Schema::hasColumn('pegawai', 'pangkat')) {
                $table->string('pangkat', 50)->nullable()->after('golongan');
            }

            if (!Schema::hasColumn('pegawai', 'kode_instansi')) {
                $table->string('kode_instansi', 10)->default('13741')->after('organisasi');
            }

            if (!Schema::hasColumn('pegawai', 'tmt_pangkat_terakhir')) {
                $table->date('tmt_pangkat_terakhir')->nullable()->after('pangkat');
                $table->index('tmt_pangkat_terakhir');
            }

            if (!Schema::hasColumn('pegawai', 'tmt_kgb_terakhir')) {
                $table->date('tmt_kgb_terakhir')->nullable()->after('tmt_pangkat_terakhir');
                $table->index('tmt_kgb_terakhir');
            }

            if (!Schema::hasColumn('pegawai', 'tmt_gaji_terakhir')) {
                $table->date('tmt_gaji_terakhir')->nullable()->after('tmt_kgb_terakhir');
            }

            if (!Schema::hasColumn('pegawai', 'gaji_pokok_saat_ini')) {
                $table->decimal('gaji_pokok_saat_ini', 15, 2)->nullable()->after('tmt_gaji_terakhir');
            }

            if (!Schema::hasColumn('pegawai', 'status_aktif')) {
                $table->boolean('status_aktif')->default(true)->after('gaji_pokok_saat_ini');
                $table->index('status_aktif');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                'gelar', 'golongan', 'pangkat', 'kode_instansi',
                'tmt_pangkat_terakhir', 'tmt_kgb_terakhir', 'tmt_gaji_terakhir',
                'gaji_pokok_saat_ini', 'status_aktif'
            ], fn($col) => Schema::hasColumn('pegawai', $col));

            if (!empty($columnsToDrop)) {
                $table->dropColumn(array_values($columnsToDrop));
            }
        });
    }
};
