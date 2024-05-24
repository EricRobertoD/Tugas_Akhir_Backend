<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('register', 'App\Http\Controllers\AuthController@register');
Route::post('login', 'App\Http\Controllers\AuthController@login');

Route::post('registerPenyedia', 'App\Http\Controllers\AuthController@registerPenyedia');
Route::post('loginPenyedia', 'App\Http\Controllers\AuthController@loginPenyedia');

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

Route::middleware(['auth:sanctum', 'ability:penyedia'])->group(function(){
Route::get('paket', 'App\Http\Controllers\PaketController@index');

Route::post('gambar', 'App\Http\Controllers\GambarPortoController@store');
Route::post('gambar/{gambar}', 'App\Http\Controllers\GambarPortoController@update');


Route::get('penyedia', 'App\Http\Controllers\PenyediaController@index');
Route::put('penyedia', 'App\Http\Controllers\AuthController@updatePenyedia');
Route::post('updatePenyediaGambar', 'App\Http\Controllers\PenyediaController@updateGambar');

Route::get('detailTransaksi', 'App\Http\Controllers\DetailTransaksiController@index');
Route::put('updateStatusDetailTransaksi/{updateStatusDetailTransaksi}', 'App\Http\Controllers\DetailTransaksiController@updateStatus');

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

Route::post('PenyediaSpecific', 'App\Http\Controllers\PenyediaController@indexPenyediaSpecific');

});


Route::middleware(['auth:sanctum', 'ability:pengguna'])->group(function(){
    Route::get('pengguna', 'App\Http\Controllers\PenggunaController@index');
    Route::put('pengguna', 'App\Http\Controllers\PenggunaController@updatePengguna');
    Route::post('updatePenggunaGambar', 'App\Http\Controllers\PenggunaController@updateGambar');

    Route::get('detailTransaksiPengguna', 'App\Http\Controllers\DetailTransaksiController@indexPengguna');

    Route::get('transaksi', 'App\Http\Controllers\TransaksiController@index');
    Route::put('updateStatusTransaksi/{updateStatusTransaksi}', 'App\Http\Controllers\TransaksiController@updateStatus');

    
    Route::post('/filter', 'App\Http\Controllers\FilterController@filter');

    Route::put('updateStatusDetailTransaksi/{updateStatusDetailTransaksi}', 'App\Http\Controllers\DetailTransaksiController@updateStatus');
    Route::post('ulasan', 'App\Http\Controllers\UlasanController@store');

    Route::post('chatPengguna', 'App\Http\Controllers\ChatController@storePengguna');
    Route::post('isiChatPengguna', 'App\Http\Controllers\ChatController@chatPengguna');
    Route::get('listChatPengguna', 'App\Http\Controllers\ChatController@listPenyediaForPengguna');
    Route::post('chatPenggunaFirst', 'App\Http\Controllers\ChatController@storePenggunaFirst');
    
});