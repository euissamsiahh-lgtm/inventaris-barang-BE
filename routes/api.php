<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\MutasiController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\SatuanController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('barangs', BarangController::class);
    Route::apiResource('kategoris', KategoriController::class);
    Route::apiResource('satuans', SatuanController::class);
    Route::get('/laporan/stok', [LaporanController::class, 'stok']);
    Route::get('/laporan/barang-keluar', [LaporanController::class, 'barangKeluar']);
    Route::apiResource('mutasi', MutasiController::class);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
