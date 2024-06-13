<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('register', 'App\Http\Controllers\AuthController@register');
Route::post('login', 'App\Http\Controllers\AuthController@login');

Route::post('registerPenyedia', 'App\Http\Controllers\AuthController@registerPenyedia');
Route::post('loginPenyedia', 'App\Http\Controllers\AuthController@loginPenyedia');

Route::post('registerAdmin', 'App\Http\Controllers\AuthController@registerAdmin');
Route::post('loginAdmin', 'App\Http\Controllers\AuthController@loginAdmin');

Route::post('registerOwner', 'App\Http\Controllers\OwnerController@registerOwner');
Route::post('loginOwner', 'App\Http\Controllers\OwnerController@loginOwner');

Route::delete('gambar/{gambar}', 'App\Http\Controllers\GambarPortoController@delete');

Route::post('logout', 'App\Http\Controllers\AuthController@logout');

Route::post('paket', 'App\Http\Controllers\PaketController@store')->middleware('auth:sanctum');
Route::put('paket/{paket}', 'App\Http\Controllers\PaketController@update')->middleware('auth:sanctum');
Route::delete('paket/{paket}', 'App\Http\Controllers\PaketController@destroy')->middleware('auth:sanctum');

Route::post('forgotPassword', 'App\Http\Controllers\AuthController@forgotPasswordPengguna');
Route::post('resetPassword', 'App\Http\Controllers\AuthController@resetPassword');

Route::post('test', 'App\Http\Controllers\ChatController@storePengguna');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('updateStatusDetailTransaksi/{updateStatusDetailTransaksi}', 'App\Http\Controllers\DetailTransaksiController@updateStatus');

    Route::post('saldoDeposit', 'App\Http\Controllers\SaldoController@deposit');
    Route::post('saldoWithdraw', 'App\Http\Controllers\SaldoController@withdraw');

    Route::post('confirmDeposit', 'App\Http\Controllers\SaldoController@confirmDeposit');

    Route::post('createPaymentLink', 'App\Http\Controllers\SaldoController@createPaymentLink');
    Route::post('depositMidtrans', 'App\Http\Controllers\SaldoController@depositMidtrans');
    Route::post('midtrans/webhook', 'App\Http\Controllers\MidtransWebhookController@handle');
    Route::post('/confirmDepositMidtrans/{id}', 'App\Http\Controllers\SaldoController@confirmDepositMidtrans');

});

Route::middleware(['auth:sanctum', 'ability:penyedia'])->group(function(){
Route::get('paket', 'App\Http\Controllers\PaketController@index');

Route::post('gambar', 'App\Http\Controllers\GambarPortoController@store');
Route::post('gambar/{gambar}', 'App\Http\Controllers\GambarPortoController@update');


Route::get('penyedia', 'App\Http\Controllers\PenyediaController@index');
Route::put('penyedia', 'App\Http\Controllers\AuthController@updatePenyedia');
Route::post('updatePenyediaGambar', 'App\Http\Controllers\PenyediaController@updateGambar');

Route::get('detailTransaksi', 'App\Http\Controllers\DetailTransaksiController@index');

Route::get('ulasan', 'App\Http\Controllers\UlasanController@index');

Route::get('jadwal', 'App\Http\Controllers\JadwalController@index');
Route::post('jadwal', 'App\Http\Controllers\JadwalController@store');
Route::put('jadwal/{jadwal}', 'App\Http\Controllers\JadwalController@update');
Route::delete('jadwal/{jadwal}', 'App\Http\Controllers\JadwalController@destroy');

Route::get('tanggalLibur', 'App\Http\Controllers\TanggalLiburController@index');
Route::post('tanggalLibur', 'App\Http\Controllers\TanggalLiburController@store');
Route::put('tanggalLibur/{tanggalLibur}', 'App\Http\Controllers\TanggalLiburController@update');
Route::delete('tanggalLibur/{tanggalLibur}', 'App\Http\Controllers\TanggalLiburController@destroy');

Route::get('paket', 'App\Http\Controllers\PaketController@index');
Route::post('paket', 'App\Http\Controllers\PaketController@store');
Route::put('paket/{paket}', 'App\Http\Controllers\PaketController@update');
Route::delete('paket/{paket}', 'App\Http\Controllers\PaketController@destroy');

Route::post('chatPenyedia', 'App\Http\Controllers\ChatController@storePenyedia');
Route::post('isiChatPenyedia', 'App\Http\Controllers\ChatController@chatPenyedia');
Route::get('listChatPenyedia', 'App\Http\Controllers\ChatController@listPenggunaForPenyedia');

Route::get('saldoPenyedia', 'App\Http\Controllers\SaldoController@indexPenyedia');

Route::put('confirmDetailTransaksi/{confirmDetailTransaksi}', 'App\Http\Controllers\DetailTransaksiController@confirmDetailTransaksi');
Route::put('cancelDetailTransaksi/{cancelDetailTransaksi}', 'App\Http\Controllers\DetailTransaksiController@cancelDetailTransaksi');

});


Route::middleware(['auth:sanctum', 'ability:pengguna'])->group(function(){
    Route::get('pengguna', 'App\Http\Controllers\PenggunaController@index');
    Route::put('pengguna', 'App\Http\Controllers\PenggunaController@updatePengguna');
    Route::post('updatePenggunaGambar', 'App\Http\Controllers\PenggunaController@updateGambar');

    Route::get('detailTransaksiPengguna', 'App\Http\Controllers\DetailTransaksiController@indexPengguna');

    Route::get('transaksi', 'App\Http\Controllers\TransaksiController@index');
    Route::post('updateStatusTransaksi', 'App\Http\Controllers\TransaksiController@updateStatus');

    
    Route::post('/filter', 'App\Http\Controllers\FilterController@filter');

    Route::post('ulasan', 'App\Http\Controllers\UlasanController@store');

    Route::post('chatPengguna', 'App\Http\Controllers\ChatController@storePengguna');
    Route::post('isiChatPengguna', 'App\Http\Controllers\ChatController@chatPengguna');
    Route::get('listChatPengguna', 'App\Http\Controllers\ChatController@listPenyediaForPengguna');
    Route::post('chatPenggunaFirst', 'App\Http\Controllers\ChatController@storePenggunaFirst');
    
    Route::post('PenyediaSpecific', 'App\Http\Controllers\PenyediaController@indexPenyediaSpecific');

    Route::post('tambahKeranjang', 'App\Http\Controllers\DetailTransaksiController@tambahKeranjang');
    Route::get('/penyedia/{id_penyedia}/paket', 'App\Http\Controllers\PaketController@getPaketsByPenyedia');
    Route::get('/keranjang', 'App\Http\Controllers\DetailTransaksiController@indexKeranjang');
    Route::delete('/keranjang/{id_detail_transaksi}', 'App\Http\Controllers\DetailTransaksiController@deleteKeranjang');
    
    Route::put('updateBerlangsung/{updateBerlangsung}', 'App\Http\Controllers\DetailTransaksiController@updateStatusBerlangsung');

    Route::get('saldoPengguna', 'App\Http\Controllers\SaldoController@indexPengguna');

    Route::post('applyVoucher', 'App\Http\Controllers\VoucherController@applyVoucher');

});

Route::middleware(['auth:sanctum', 'ability:admin'])->group(function(){
    Route::get('admin', 'App\Http\Controllers\AdminController@index');

    Route::post('confirmWithdraw/{id_saldo}', 'App\Http\Controllers\SaldoController@confirmWithdraw');
    Route::post('rejectWithdraw/{id_saldo}', 'App\Http\Controllers\SaldoController@rejectWithdraw');
    Route::get('pendingWithdraw', 'App\Http\Controllers\SaldoController@indexPendingWithdraw');

    Route::get('voucher', 'App\Http\Controllers\VoucherController@index');
    Route::post('voucher', 'App\Http\Controllers\VoucherController@store');
    Route::put('voucher/{voucher}', 'App\Http\Controllers\VoucherController@updateStatus');
    Route::delete('voucher/{voucher}', 'App\Http\Controllers\VoucherController@destroy');

});


Route::middleware(['auth:sanctum', 'ability:owner'])->group(function(){
    Route::get('owner', 'App\Http\Controllers\OwnerController@index');

    Route::get('loginReal', 'App\Http\Controllers\AnalyticsController@getRealtimeData');
    Route::get('loginLate', 'App\Http\Controllers\AnalyticsController@getLateTimeData');
    Route::get('eventCount', 'App\Http\Controllers\AnalyticsController@getEventCountData');
    Route::get('newVsReturning', 'App\Http\Controllers\AnalyticsController@getNewVsReturningUsers');

    Route::get('topPengguna', 'App\Http\Controllers\LaporanController@top5PenggunaWithMostTransaksi');
    Route::get('successfulDetailTransaksi', 'App\Http\Controllers\LaporanController@countSuccessfulDetailTransaksi');
});