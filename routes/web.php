<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\RefpembayaranController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\KgbPengurusanController;
use App\Http\Controllers\KgbDashboardController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [PembayaranController::class, 'index']);
Route::get('/logout', [AuthenticatedSessionController::class, 'destroy']);

// ============ PEMBAYARAN ============
Route::get('pembayaran/sendwa', [PembayaranController::class, 'sendwa'])->name('pembayaran.sendwa');
Route::post('pembayaran/import', [PembayaranController::class, 'import'])->name('pembayaran.import');
Route::resource('pembayaran', PembayaranController::class);

// ============ REF PEMBAYARAN ============
Route::resource('refpembayaran', RefpembayaranController::class);

// ============ PEGAWAI ============
Route::resource('pegawai', PegawaiController::class);
Route::get('/pegawai/{id}/riwayat-pangkat', [PegawaiController::class, 'riwayatPangkat'])->name('pegawai.riwayat-pangkat');
Route::post('/pegawai/{id}/riwayat-pangkat', [PegawaiController::class, 'storeRiwayatPangkat'])->name('pegawai.store-riwayat-pangkat');
Route::get('/pegawai/riwayat-pangkat/{id}/edit', [PegawaiController::class, 'editRiwayatPangkat'])->name('pegawai.edit-riwayat-pangkat');
Route::put('/pegawai/riwayat-pangkat/{id}', [PegawaiController::class, 'updateRiwayatPangkat'])->name('pegawai.update-riwayat-pangkat');
Route::delete('/pegawai/riwayat-pangkat/{id}', [PegawaiController::class, 'destroyRiwayatPangkat'])->name('pegawai.destroy-riwayat-pangkat');
// Riwayat Gaji
Route::get('/pegawai/{id}/riwayat-gaji', [PegawaiController::class, 'riwayatGaji'])->name('pegawai.riwayat-gaji');
Route::post('/pegawai/{id}/riwayat-gaji', [PegawaiController::class, 'storeRiwayatGaji'])->name('pegawai.store-riwayat-gaji');
Route::get('/pegawai/riwayat-gaji/{id}/edit', [PegawaiController::class, 'editRiwayatGaji'])->name('pegawai.edit-riwayat-gaji');
Route::put('/pegawai/riwayat-gaji/{id}', [PegawaiController::class, 'updateRiwayatGaji'])->name('pegawai.update-riwayat-gaji');
Route::delete('/pegawai/riwayat-gaji/{id}', [PegawaiController::class, 'destroyRiwayatGaji'])->name('pegawai.destroy-riwayat-gaji');

// ============ KGB ============
Route::prefix('kgb')->middleware(['auth'])->group(function () {

    // Dashboard & Laporan
    Route::get('/dashboard', [KgbDashboardController::class, 'index'])->name('kgb.dashboard');
    Route::get('/refresh', [KgbDashboardController::class, 'refresh'])->name('kgb.refresh');
    Route::get('/laporan', [KgbDashboardController::class, 'laporan'])->name('kgb.laporan');

    // API (AJAX)
    Route::get('/api/grafik', [KgbDashboardController::class, 'grafikData'])->name('kgb.api.grafik');
    Route::get('/api/mendatang', [KgbDashboardController::class, 'kgbMendatangData'])->name('kgb.api.mendatang');
    Route::get('/api/stats', [KgbDashboardController::class, 'statsData'])->name('kgb.api.stats');

    // Pengurusan KGB
    Route::get('/', [KgbPengurusanController::class, 'index'])->name('kgb.index');
    Route::get('/{id}', [KgbPengurusanController::class, 'show'])->name('kgb.show');
    Route::get('/{id}/proses', [KgbPengurusanController::class, 'prosesForm'])->name('kgb.proses-form');
    Route::post('/{id}/proses', [KgbPengurusanController::class, 'proses'])->name('kgb.proses');
    Route::get('/{id}/word', [KgbPengurusanController::class, 'generateWord'])->name('kgb.word');
    Route::get('/{id}/preview', [KgbPengurusanController::class, 'preview'])->name('kgb.preview');
    Route::post('/{id}/batal', [KgbPengurusanController::class, 'batal'])->name('kgb.batal');
    Route::post('/pegawai/{id}', [KgbPengurusanController::class, 'updatePegawai'])->name('kgb.update-pegawai');

    Route::post('/{id}/batal-proses', [KgbPengurusanController::class, 'batalProses'])->name('kgb.batal-proses');
});

// ============ JETSTREAM DASHBOARD ============
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
