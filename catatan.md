# Catatan Perintah Laravel

Dokumen ini mencatat **setiap perintah Laravel/Composer** yang dijalankan untuk membangun fitur:
**Seeder Mahasiswa per Kelas**, **Pagination**, dan **Login + RBAC (Spatie)**.

> Projekt: `belajar-laravel` · Laravel 12 · PHP 8.2 · MySQL `db_ase11`
> Tanggal: 3 Oktober 2026

---

## Urutan Perintah

### 1. Install package RBAC (Spatie Laravel Permission)

```bash
composer require spatie/laravel-permission
```

- Menginstall package `spatie/laravel-permission`.
- Versi yang terpasang: **6.25.0** (versi 8.x membutuhkan PHP ^8.3, sedangkan environment ini PHP 8.2).
- Menambahkan dependency ke `composer.json`.

### 2. Regenerasi autoloader & daftarkan provider

```bash
composer dump-autoload --no-scripts
php artisan package:discover
```

- `dump-autoload` memperbarui autoloader Composer agar class `Spatie\Permission\*` dikenali.
- `package:discover` mendaftarkan `PermissionServiceProvider` ke cache paket Laravel (`bootstrap/cache/packages.php`).

### 3. Publish config & migration Spatie

```bash
php artisan vendor:publish --tag=permission-config
php artisan vendor:publish --tag=permission-migrations
```

- `permission-config` → menyalin `config/permission.php`.
- `permission-migrations` → menyalin migration `database/migrations/2026_10_03_014705_create_permission_tables.php`
  (membuat tabel `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`).

### 4. Buat controller login

```bash
php artisan make:controller AuthController
```

- Membuat `app/Http/Controllers/AuthController.php` (kemudian diisi method `showLoginForm`, `login`, `logout`).

### 5. Buat seeder

```bash
php artisan make:seeder RolePermissionSeeder
php artisan make:seeder MahasiswaPerKelasSeeder
```

- `RolePermissionSeeder` → membuat permission, role (admin & mahasiswa), dan user default.
- `MahasiswaPerKelasSeeder` → membuat kelas `ase11` & `ase12` lalu 25 mahasiswa per kelas.

### 6. Jalankan migration

```bash
php artisan migrate
```

- Membuat tabel-tabel RBAC milik Spatie di database `db_ase11`.

### 7. Jalankan seeder

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=MahasiswaPerKelasSeeder
```

- Mengisi role/permission/user default, lalu data kelas & mahasiswa.
- Kedua seeder idempoten (memakai `firstOrCreate`) sehingga aman dijalankan ulang.

### 8. Bersihkan cache

```bash
php artisan optimize:clear
php artisan permission:cache-reset
```

- `optimize:clear` → membersihkan cache config, route, view, event, dan compiled.
- `permission:cache-reset` → mereset cache permission Spatie agar role/permission terbaru langsung terbaca.

### 9. Verifikasi route

```bash
php artisan route:list --path=mahasiswa -v
```

- Memastikan route resource terbagi dua: `index`/`show` hanya `auth`, sedangkan `create`/`store`/`edit`/`update`/`destroy` memakai `auth` + `role:admin`.

### 10. Jalankan server development

```bash
php artisan serve
```

- Menjalankan aplikasi di `http://127.0.0.1:8000` untuk pengujian manual.

---

## Ringkasan Tabel

| No | Perintah | Fungsi |
|----|----------|--------|
| 1 | `composer require spatie/laravel-permission` | Install package RBAC |
| 2 | `composer dump-autoload --no-scripts` | Perbarui autoloader |
| 3 | `php artisan package:discover` | Daftarkan provider Spatie |
| 4 | `php artisan vendor:publish --tag=permission-config` | Publish `config/permission.php` |
| 5 | `php artisan vendor:publish --tag=permission-migrations` | Publish migration tabel RBAC |
| 6 | `php artisan make:controller AuthController` | Buat controller login |
| 7 | `php artisan make:seeder RolePermissionSeeder` | Buat seeder role & permission |
| 8 | `php artisan make:seeder MahasiswaPerKelasSeeder` | Buat seeder mahasiswa per kelas |
| 9 | `php artisan migrate` | Jalankan migration |
| 10 | `php artisan db:seed --class=RolePermissionSeeder` | Seed role, permission, user |
| 11 | `php artisan db:seed --class=MahasiswaPerKelasSeeder` | Seed kelas & mahasiswa |
| 12 | `php artisan optimize:clear` | Bersihkan semua cache |
| 13 | `php artisan permission:cache-reset` | Reset cache permission |
| 14 | `php artisan route:list --path=mahasiswa -v` | Verifikasi route & middleware |
| 15 | `php artisan serve` | Jalankan server development |

---

## Akun Default (hasil seeder)

| Email | Password | Role | Akses |
|-------|----------|------|-------|
| `admin@example.test` | `password` | admin | Full CRUD |
| `mahasiswa@example.test` | `password` | mahasiswa | Read-only |

---

# Catatan Perintah — Fitur Upload Foto Mahasiswa (Pertemuan 7)

Dokumen ini juga mencatat perintah untuk menambahkan **upload foto** pada CRUD mahasiswa.

> Tanggal: 23 Oktober 2026 · Library: `intervention/image` 3.11.10

## Urutan Perintah

### 1. Install library Intervention Image

```bash
composer require intervention/image
```

- Menginstall `intervention/image` untuk resize dan konversi gambar ke WebP.
- Composer memilih versi **3.11.10** karena versi 4.x mensyaratkan PHP ^8.3, sedangkan proyek ini memakai PHP 8.2.
- Ikut terpasang dependency `intervention/gif` 4.2.4.

### 2. Regenerasi autoloader

```bash
composer dump-autoload --no-scripts
```

- Memperbarui autoloader agar class `Intervention\Image\*` langsung dikenali (wajib jika proses install terputus).

### 3. Buat migration kolom foto

```bash
php artisan make:migration add_foto_to_mahasiswa_table --table=mahasiswa
```

- Membuat `database/migrations/2026_10_23_174328_add_foto_to_mahasiswa_table.php`.
- Isi: `$table->string('foto')->nullable()->after('email');`

### 4. Jalankan migration

```bash
php artisan migrate
```

- Menambahkan kolom `foto` (nullable) pada tabel `mahasiswa`.

### 5. Buat symbolic link storage

```bash
php artisan storage:link
```

- Menghubungkan `public/storage` → `storage/app/public`.
- Tanpa langkah ini, file hasil upload tidak bisa diakses lewat browser.

### 6. Bersihkan cache

```bash
php artisan optimize:clear
```

- Membersihkan cache config, route, view, dan compiled agar perubahan `.env`/config langsung terbaca.

### 7. Verifikasi route

```bash
php artisan route:list --path=mahasiswa -v
```

- Memastikan `store`, `update`, dan `destroy` tetap dijaga `auth` + `permission:menu-mahasiswa` + `role:admin`.

### 8. Jalankan server development

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

- Menjalankan aplikasi untuk pengujian upload dan pengambilan screenshot.

### 9. Jalankan test regresi

```bash
php artisan test
```

- Memastikan perubahan tidak merusak fitur yang sudah ada (2 test lulus).

## Ringkasan Perintah Upload

| No | Perintah | Fungsi |
|----|----------|--------|
| 1 | `composer require intervention/image` | Install library pengolah gambar |
| 2 | `composer dump-autoload --no-scripts` | Perbarui autoloader Composer |
| 3 | `php artisan make:migration add_foto_to_mahasiswa_table --table=mahasiswa` | Buat migration kolom `foto` |
| 4 | `php artisan migrate` | Jalankan migration |
| 5 | `php artisan storage:link` | Buat symlink `public/storage` |
| 6 | `php artisan optimize:clear` | Bersihkan cache config/route/view |
| 7 | `php artisan route:list --path=mahasiswa -v` | Verifikasi route & middleware |
| 8 | `php artisan serve --host=127.0.0.1 --port=8000` | Jalankan server untuk pengujian |
| 9 | `php artisan test` | Test regresi |
