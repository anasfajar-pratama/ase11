<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\JadwalKuliahController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\RuanganController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('mahasiswa', MahasiswaController::class);
Route::resource('dosen', DosenController::class);
Route::resource('mata-kuliah', MataKuliahController::class);
Route::resource('ruangan', RuanganController::class);
Route::resource('kelas', KelasController::class)->parameters([
    'kelas' => 'kls',
]);
Route::resource('jadwal-kuliah', JadwalKuliahController::class);