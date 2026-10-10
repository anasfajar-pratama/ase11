<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use Illuminate\Database\Seeder;

class MahasiswaPerKelasSeeder extends Seeder
{
    /**
     * Buat kelas ase11 & ase12, lalu isi masing-masing 25 mahasiswa.
     */
    public function run(): void
    {
        // Daftar kelas yang akan dibuat: nama_kelas => [kode nim, angkatan, jurusan].
        $kelasList = [
            'ase11' => ['kode' => '1', 'angkatan' => 2026, 'jurusan' => 'Informatika'],
            'ase12' => ['kode' => '2', 'angkatan' => 2026, 'jurusan' => 'Informatika'],
        ];

        foreach ($kelasList as $namaKelas => $meta) {
            // Pastikan kelas ada (firstOrCreate agar aman dijalankan ulang).
            $kelas = Kelas::firstOrCreate(
                ['nama_kelas' => $namaKelas],
                ['angkatan' => $meta['angkatan'], 'jurusan' => $meta['jurusan']]
            );

            // Buat 25 mahasiswa untuk kelas ini.
            for ($i = 1; $i <= 25; $i++) {
                $nim = '2026' . $meta['kode'] . str_pad((string) $i, 3, '0', STR_PAD_LEFT);

                Mahasiswa::firstOrCreate(
                    ['nim' => $nim],
                    [
                        'nama' => fake()->name(),
                        'jurusan' => $meta['jurusan'],
                        'email' => fake()->unique()->safeEmail(),
                        'angkatan' => $meta['angkatan'],
                        'kelas_id' => $kelas->id,
                    ]
                );
            }
        }
    }
}
