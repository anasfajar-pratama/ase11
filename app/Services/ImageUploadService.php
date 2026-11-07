<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    /**
     * Simpan gambar ke disk public sebagai WebP yang sudah diperkecil.
     *
     * @return string Path relatif terhadap disk public, mis. "mahasiswa/uuid.webp".
     */
    public function store(UploadedFile $file, string $folder = 'mahasiswa', int $maxWidth = 600): string
    {
        $image = (new ImageManager(new Driver()))->read($file->getRealPath());

        $image->scaleDown(width: $maxWidth);

        $path = $folder.'/'.Str::uuid().'.webp';

        Storage::disk('public')->put($path, $image->toWebp(75)->toString());

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
