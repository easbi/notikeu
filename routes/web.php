<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\RefpembayaranController;
use App\Http\Controllers\PegawaiController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [PembayaranController::class, 'index']);
Route::get('/logout', [AuthenticatedSessionController::class, 'destroy']);

Route::get('pembayaran/sendwa', [PembayaranController::class, 'sendwa'])->name('pembayaran.sendwa');
Route::post('pembayaran/import', [PembayaranController::class, 'import'])->name('pembayaran.import');
Route::resource('pembayaran', PembayaranController::class);

Route::resource('refpembayaran', RefpembayaranController::class);
Route::resource('pegawai', PegawaiController::class);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
