<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

# [Fillable(['nim', 'nama', 'jurusan', 'email', 'angkatan'])]
class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'nama',
        'jurusan',
        'email',
        'angkatan',
    ];

    protected function casts(): array
    {
        return [
            'angkatan' => 'integer',
        ];
    }
}