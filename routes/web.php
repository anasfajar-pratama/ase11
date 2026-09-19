<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\DosenController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/mahasiswa',
//     [MahasiswaController::class, 'index']
// );

Route::resource('mahasiswa', MahasiswaController::class);
