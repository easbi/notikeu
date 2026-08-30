<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->enum('jenis_pegawai', ['PNS', 'PPPK'])
                ->default('PNS')
                ->after('nip');  // <-- Default PNS untuk semua data existing
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn('jenis_pegawai');
        });
    }
};