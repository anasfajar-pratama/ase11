# Library Upload — Panduan Upload File di Laravel

> Materi pendamping untuk proyek **belajar-laravel** (Laravel 12, PHP 8.2, MySQL).
> Implementasi contoh: **upload foto pada CRUD Mahasiswa**.
> Modul langkah demi langkah + screenshot ada di `MODUL_UPLOAD.html`.

---

## 1. Upload itu sebenarnya apa?

Bayangkan kamu mengirim paket. Form HTML adalah loket, file yang kamu pilih adalah paketnya, dan server adalah gudang. Tapi server tidak menyimpan paket di meja loket — ia memberi **nomor resi** (path/nama file), menaruh paket di gudang (storage), lalu mencatat nomor resi itu di buku besar (database).

Alurnya kira-kira begini:

1. User memilih file di `<input type="file">`, lalu menekan **Simpan**.
2. Browser mengirim request `multipart/form-data` (bukan form biasa).
3. PHP menerima file sebagai **file sementara** di folder temp.
4. Laravel membungkusnya menjadi object `Illuminate\Http\UploadedFile`.
5. Kita **validasi** (jenis, ukuran, dimensi).
6. Kita pindahkan/olah file ke storage permanen (`Storage::put`).
7. Yang disimpan di database **hanya path-nya**, bukan isi file.
8. Saat ditampilkan, path diubah menjadi URL (`asset('storage/...')`).

Kunci mental model: **database menyimpan alamat, storage menyimpan barang.** Kalau kamu menyimpan isi file (BLOB) di database, aplikasi akan cepat lambat dan backup jadi berat.

---

## 2. CRUD mana yang bisa ditambah fitur upload?

Hampir semua CRUD bisa, asal jelas **file-nya untuk apa**. Di proyek ini ada beberapa kandidat:

| CRUD | Kolom baru | Jenis file | Contoh isi | Tingkat kesulitan |
|---|---|---|---|---|
| **Mahasiswa** | `foto` | gambar | pas foto mahasiswa | Mudah (dipakai di modul ini) |
| **Dosen** | `foto` | gambar | foto dosen | Mudah |
| **Mata Kuliah** | `modul` | PDF | RPS / modul ajar | Mudah |
| **Ruangan** | `foto_ruangan` | gambar | foto ruangan | Mudah |
| **Jadwal Kuliah** | `lampiran` | PDF/gambar | materi pertemuan | Sedang |
| **Pengguna** | `avatar` | gambar | foto profil | Sedang (perlu validasi role) |
| **Kelas** | — | — | biasanya tidak perlu | — |

### Satu file vs banyak file

- **Satu file per baris** (contoh di atas): cukup tambah satu kolom `string` nullable di tabel yang sudah ada. Paling sederhana.
- **Banyak file per baris** (misal satu jadwal punya 5 lampiran): jangan simpan 5 path dalam satu kolom. Buat tabel anak:

```text
lampiran_jadwal
- id
- jadwal_kuliah_id  (foreign key, cascadeOnDelete)
- path
- nama_asli
- timestamps
```

Lalu relasi `JadwalKuliah hasMany LampiranJadwal`. Saat jadwal dihapus, semua file ikut dihapus.

### Rekomendasi urutan belajar

1. **Mahasiswa → `foto`** (satu file, gambar) — paling mudah dan langsung kelihatan hasilnya di tabel.
2. **Mata Kuliah → `modul` PDF** — belajar validasi file non-gambar dan link download.
3. **Jadwal Kuliah → banyak lampiran** — belajar tabel anak dan hapus massal.

---

## 3. Library apa yang dipakai?

### a. Laravel Storage (built-in, wajib)

Tidak perlu install apa pun. Ini "lemari arsip" Laravel:

```php
use Illuminate\Support\Facades\Storage;

Storage::disk('public')->put('mahasiswa/foto.webp', $isiFile);
Storage::disk('public')->delete('mahasiswa/foto.webp');
Storage::disk('public')->exists('mahasiswa/foto.webp');
```

Konfigurasinya ada di `config/filesystems.php`. Untuk file yang boleh diakses publik, pakai disk `public` yang mengarah ke `storage/app/public`.

Agar bisa dibuka lewat browser, jalankan sekali:

```bash
php artisan storage:link
```

Perintah ini membuat `public/storage` sebagai symlink ke `storage/app/public`. Tanpa ini, gambar akan 404.

### b. Intervention Image (untuk resize & convert)

Ini library opsional, tapi sangat membantu kalau kamu ingin **menghemat storage**:

```bash
composer require intervention/image
```

Versi yang cocok dengan PHP 8.2 adalah **3.11.x** (versi 4.x butuh PHP 8.3). API v3:

```php
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

$image = (new ImageManager(new Driver()))->read($file->getRealPath());
$image->scaleDown(width: 600);          // perkecil, rasio tetap, tidak memperbesar
$webp = $image->toWebp(75)->toString(); // kualitas 75
```

Butuh ekstensi PHP **GD** (sudah aktif di XAMPP). Cek dengan `php -m | findstr gd`.

### c. GD native (tanpa dependency)

Kalau tidak mau menambah package, PHP GD bisa dipakai langsung (`imagecreatefromjpeg`, `imagescale`, `imagewebp`). Lebih banyak kode dan lebih mudah salah, jadi untuk belajar saya sarankan Intervention Image dulu.

### d. Kapan perlu library lain?

- **`spatie/laravel-medialibrary`** — kalau butuh banyak file, thumbnail otomatis, konversi banyak ukuran, dan manajemen media yang kompleks. Untuk satu kolom `foto`, ini berlebihan.
- **S3 / object storage** — kalau file banyak atau aplikasi di-deploy ke beberapa server. Cukup ganti disk di `config/filesystems.php`, kode `Storage::disk('s3')` tetap sama.
- **CDN** — kalau gambar dipakai lintas wilayah dan butuh cache global.

---

## 4. Bagaimana validasi file-nya?

Validasi file **wajib di server**. Validasi di HTML (`accept="image/*"`) hanya membantu user, bukan pengaman.

### Aturan yang sering dipakai

| Rule | Arti | Catatan |
|---|---|---|
| `nullable` | boleh tidak upload | Cocok untuk foto opsional |
| `required` | wajib upload | Untuk dokumen wajib |
| `file` | harus berupa file | Dasar, cek upload berhasil |
| `image` | harus gambar | JPG, PNG, GIF, BMP, WebP, SVG* |
| `mimes:jpg,jpeg,png,webp` | ekstensi/MIME tertentu | Lebih spesifik dari `image` |
| `mimetypes:image/jpeg,image/png` | MIME asli file | Paling ketat |
| `max:2048` | maksimal 2048 **KB** (2 MB) | Bukan byte |
| `dimensions:min_width=100` | batas dimensi gambar | Berguna untuk foto profil |
| `extensions:jpg,png` | ekstensi akhir file | Kombinasikan dengan `mimes` |

\* `image` mengizinkan SVG. **Hati-hati:** SVG bisa berisi JavaScript. Untuk upload publik, kecualikan SVG atau sanitasi dulu. Di modul ini kita pakai `image` + `mimes` tanpa SVG.

### Contoh di controller

```php
$validated = $request->validate([
    'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
], [
    'foto.image' => 'Foto harus berupa gambar.',
    'foto.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
    'foto.max'   => 'Ukuran foto maksimal 2 MB.',
]);
```

### Aturan keamanan yang tidak boleh dilupakan

1. **Jangan percaya nama file dari user.** Simpan dengan nama acak (`Str::uuid()`), jangan `foto-asli.jpg`.
2. **Jangan percaya ekstensi.** File `gambar.php.jpg` bisa menipu. Rule `image`/`mimes` memeriksa isi file, bukan cuma nama.
3. **Simpan di folder yang tidak mengeksekusi PHP** (disk `public` hanya untuk aset statis).
4. **Batasi ukuran** — tanpa `max`, user bisa mengirim file 1 GB.
5. **Jangan izinkan SVG** untuk upload publik tanpa sanitasi.
6. **File privat** (KTP, transkrip) jangan ditaruh di disk `public`; simpan di disk `local` dan sajikan lewat route yang mengecek permission.
7. **Hapus file lama** saat data dihapus/diganti agar tidak jadi sampah.

---

## 5. Bagaimana proses upload-nya?

### Langkah 1 — Form harus `multipart/form-data`

```blade
<form action="{{ route('mahasiswa.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
</form>
```

Kalau `enctype` lupa, file tidak akan terkirim. Yang masuk ke server hanya nama file kosong.

### Langkah 2 — Ambil file di controller

```php
if ($request->hasFile('foto')) {
    $file = $request->file('foto'); // Illuminate\Http\UploadedFile
}
```

`hasFile()` memastikan user benar-benar mengirim file dan upload-nya valid.

### Langkah 3 — Validasi, lalu olah

```php
$validated = $request->validate([
    'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
]);

if ($request->hasFile('foto')) {
    $validated['foto'] = $this->imageUpload->store($request->file('foto'));
}
```

### Langkah 4 — Simpan ke storage, catat path ke database

```php
class ImageUploadService
{
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
```

Yang dikembalikan adalah **path relatif** (`mahasiswa/xxxx.webp`), bukan URL. Ini penting agar kalau nanti pindah ke S3/CDN, isi database tidak perlu diubah.

### Langkah 5 — Tampilkan

```php
// app/Models/Mahasiswa.php
protected function fotoUrl(): Attribute
{
    return Attribute::get(
        fn () => $this->foto ? asset('storage/'.$this->foto) : null
    );
}
```

```blade
@if ($mhs->foto)
    <img src="{{ $mhs->foto_url }}" alt="Foto {{ $mhs->nama }}" class="foto-thumb">
@endif
```

> Catatan: `Storage::url()` juga bisa dipakai, tetapi nilainya bergantung pada `APP_URL` di `.env`. Kalau `APP_URL` tidak sama dengan alamat yang dibuka (misal aplikasi jalan di port 8000), gambar bisa menunjuk ke host yang salah. `asset('storage/...')` mengikuti alamat request sehingga lebih aman untuk `php artisan serve`.

---

## 6. Bagaimana kalau digabung dengan form input biasa?

Tidak ada yang istimewa — file hanyalah salah satu field. Tapi ada beberapa jebakan:

### a. `enctype` wajib

```blade
<form action="{{ route('mahasiswa.update', $mahasiswa->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    ...
</form>
```

### b. File tidak bisa di-`old()`

Browser tidak mengizinkan mengisi ulang `<input type="file">` karena alasan keamanan. Jadi kalau validasi gagal, user harus memilih ulang filenya. Yang bisa di-`old()` hanya field teks:

```blade
<input type="text" name="nama" value="{{ old('nama', $mahasiswa->nama) }}">
```

Beri tahu user di UI: “Pilih ulang foto jika ingin menggantinya.”

### c. Saat edit, file lama harus dipertahankan

Jangan sampai user mengedit nama saja, lalu fotonya hilang. Polanya:

```php
public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
{
    $validated = $this->validateMahasiswa($request, $mahasiswa);
    $fotoLama = $mahasiswa->foto;

    if ($request->hasFile('foto')) {
        $validated['foto'] = $this->imageUpload->store($request->file('foto'));
    }

    $mahasiswa->update($validated);

    if ($request->hasFile('foto')) {
        $this->imageUpload->delete($fotoLama);
    }

    return redirect()->route('mahasiswa.index')->with('success', 'Data berhasil diubah.');
}
```

Urutannya penting:

1. Simpan file **baru** dulu.
2. Update database.
3. Baru hapus file **lama**.

Kalau file lama dihapus duluan lalu update database gagal, data jadi menunjuk file yang sudah tidak ada. Kalau kamu ingin lebih aman lagi, bungkus update database dan penghapusan dalam `DB::transaction()`.

### d. Saat hapus data, hapus filenya juga

```php
public function destroy(Mahasiswa $mahasiswa): RedirectResponse
{
    $this->imageUpload->delete($mahasiswa->foto);
    $mahasiswa->delete();

    return redirect()->route('mahasiswa.index')->with('success', 'Data berhasil dihapus.');
}
```

Tanpa ini, file akan menumpuk di storage meskipun datanya sudah tidak ada (orphan file).

### e. Tampilkan preview di form edit

```blade
@if ($mahasiswa->foto)
    <img src="{{ $mahasiswa->foto_url }}" alt="Foto {{ $mahasiswa->nama }}" class="foto-preview">
@endif
<input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
<span class="hint">Kosongkan jika tidak ingin mengganti foto.</span>
```

---

## 7. Bagaimana convert hasil upload supaya hemat storage?

Ini bagian yang paling sering diabaikan. Foto kamera HP bisa 3–8 MB; kalau 1.000 mahasiswa upload, storage bisa bengkak tanpa manfaat.

### Strategi yang dipakai proyek ini

1. **Resize** — `scaleDown(width: 600)`: lebar maksimal 600px, rasio tetap, gambar kecil tidak diperbesar.
2. **Konversi ke WebP** — `toWebp(75)`: ukuran jauh lebih kecil dari JPG/PNG pada kualitas visual yang masih bagus.
3. **Nama file UUID** — tidak bentrok, tidak membocorkan nama asli.
4. **Database hanya menyimpan path** — bukan isi file.
5. **Hapus file lama** saat ganti/hapus data — tidak ada file yatim.
6. **Symlink** `storage:link` — file tetap bisa diakses publik tanpa menaruhnya di folder kode.

### Bukti dari proyek ini

| Asli | Hasil |
|---|---|
| JPG 1200 × 1600, **225.106 byte** (±220 KB) | WebP 600 × 800, **39.316 byte** (±38 KB) |
| Hemat | **±82,5%** |

Pada percobaan gambar gradien sederhana: 68.207 byte → 3.442 byte (hemat ±95%). Angka persisnya tergantung isi gambar, tapi polanya jelas: resize + WebP memotong ukuran secara signifikan.

### Kalau butuh beberapa ukuran

Simpan satu file asli yang sudah dioptimasi, lalu buat turunan saat dibutuhkan:

```text
mahasiswa/xxxx.webp        -> 600px (untuk tabel/detail)
mahasiswa/xxxx-thumb.webp  -> 150px (untuk thumbnail)
```

Jangan simpan versi 4000px kalau aplikasi tidak pernah menampilkannya.

### Hal yang perlu diingat

- Pastikan GD/Imagick mendukung WebP. Kalau tidak, fallback ke `toJpeg(80)`.
- `scaleDown` berbeda dengan `resize`: `scaleDown` tidak akan memperbesar gambar kecil.
- Kompresi berlebihan (kualitas < 50) membuat foto terlihat pecah. 70–80 biasanya aman.
- Untuk file PDF, jangan dikonversi; cukup validasi ukuran dan simpan apa adanya. Yang bisa dioptimasi adalah gambar.

---

## 8. Ringkasan kode inti

```php
// Validasi
'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']

// Simpan (store)
if ($request->hasFile('foto')) {
    $validated['foto'] = $this->imageUpload->store($request->file('foto'));
}

// Update: simpan baru -> update DB -> hapus lama
// Destroy: hapus file -> hapus baris

// Tampilkan
<img src="{{ $mhs->foto_url }}" alt="Foto {{ $mhs->nama }}">
```

---

## 9. Checklist & troubleshooting

### Checklist implementasi

- [ ] Kolom `foto` (nullable) sudah ada di migration dan sudah `migrate`.
- [ ] `foto` masuk `$fillable`.
- [ ] Form memakai `enctype="multipart/form-data"`.
- [ ] Ada validasi `image`/`mimes`/`max`.
- [ ] File disimpan dengan nama acak.
- [ ] Database menyimpan path, bukan BLOB.
- [ ] `php artisan storage:link` sudah dijalankan.
- [ ] File lama dihapus saat update/destroy.
- [ ] Ada preview di form edit.
- [ ] Sudah diuji: upload valid, upload file salah, ganti foto, hapus data.

### Masalah umum

| Gejala | Penyebab | Solusi |
|---|---|---|
| Gambar 404 | Symlink belum ada | `php artisan storage:link` |
| URL gambar menunjuk `localhost` padahal aplikasi di port lain | `APP_URL` tidak sesuai | Samakan `APP_URL`, atau pakai `asset('storage/...')` |
| File tidak terkirim | Form tanpa `enctype` | Tambahkan `enctype="multipart/form-data"` |
| Error 419 | CSRF token hilang/kedaluwarsa | Pastikan `@csrf` ada |
| “The foto failed to upload” | Melebihi `upload_max_filesize` / `post_max_size` | Naikkan di `php.ini` atau kecilkan batas validasi |
| `Class "Intervention\Image\ImageManager" not found` | Autoload belum diperbarui | `composer dump-autoload` |
| File lama menumpuk | Tidak ada penghapusan | Hapus di `update` dan `destroy` |
| Foto pecah | Kualitas terlalu rendah | Naikkan kualitas WebP ke 75–85 |

---

## 10. Penutup

Upload bukan sekadar `move_uploaded_file`. Yang membedakan implementasi rapi dan berantakan adalah:

1. **Validasi di server** — jangan percaya input.
2. **Nama file acak** — jangan percaya nama dari user.
3. **Optimasi ukuran** — resize + WebP.
4. **Bersihkan file lama** — jangan tinggalkan sampah.
5. **Pisahkan path dan URL** — database simpan path, view yang membentuk URL.

Kalau lima hal ini sudah benar, kamu bisa menambahkan upload ke CRUD apa pun dengan pola yang sama.
