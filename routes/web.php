<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayaranController; 
use App\Http\Controllers\RefpembayaranController; 

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

Route::get('/', function () {
    return view('welcome');
});


Route::get('pembayaran/sendwa', [PembayaranController::class, 'sendwa'])->name('pembayaran.sendwa');
Route::post('pembayaran/import', [PembayaranController::class, 'import'])->name('pembayaran.import');
Route::resource('pembayaran', PembayaranController::class);

Route::resource('refpembayaran', RefpembayaranController::class);