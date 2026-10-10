<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\JadwalKuliahController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\RuanganController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Route Autentikasi
|--------------------------------------------------------------------------
| Halaman login hanya untuk tamu (guest). Logout butuh status login.
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Route Khusus Admin (create, store, edit, update, destroy)
|--------------------------------------------------------------------------
| PENTING: grup ini didaftarkan LEBIH DULU agar route "create"
| (mis. mahasiswa/create) terdaftar sebelum route "show"
| (mahasiswa/{mahasiswa}). Jika tidak, GET mahasiswa/create akan
| ditangkap oleh route show dengan parameter {mahasiswa} = "create".
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('mahasiswa', MahasiswaController::class)->except(['index', 'show']);
    Route::resource('dosen', DosenController::class)->except(['index', 'show']);
    Route::resource('mata-kuliah', MataKuliahController::class)->except(['index', 'show']);
    Route::resource('ruangan', RuanganController::class)->except(['index', 'show']);
    Route::resource('kelas', KelasController::class)->except(['index', 'show'])->parameters([
        'kelas' => 'kls',
    ]);
    Route::resource('jadwal-kuliah', JadwalKuliahController::class)->except(['index', 'show']);
});

/*
|--------------------------------------------------------------------------
| Route Semua User Login (read-only: index & show)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::resource('mahasiswa', MahasiswaController::class)->only(['index', 'show']);
    Route::resource('dosen', DosenController::class)->only(['index', 'show']);
    Route::resource('mata-kuliah', MataKuliahController::class)->only(['index', 'show']);
    Route::resource('ruangan', RuanganController::class)->only(['index', 'show']);
    Route::resource('kelas', KelasController::class)->only(['index', 'show'])->parameters([
        'kelas' => 'kls',
    ]);
    Route::resource('jadwal-kuliah', JadwalKuliahController::class)->only(['index', 'show']);
});
