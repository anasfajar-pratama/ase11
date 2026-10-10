<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Jalankan seeder role, permission, dan user default.
     */
    public function run(): void
    {
        // Reset cache permission agar data terbaru langsung terbaca.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Buat permission dasar.
        $permissions = ['lihat-data', 'tambah-data', 'ubah-data', 'hapus-data'];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Buat role dan assigns permission.
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->syncPermissions($permissions);

        $roleMahasiswa = Role::firstOrCreate(['name' => 'mahasiswa']);
        $roleMahasiswa->syncPermissions(['lihat-data']);

        // 3. Buat user default (password otomatis di-hash oleh cast 'hashed' pada model User).
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.test'],
            ['name' => 'Administrator', 'password' => 'password']
        );
        $admin->syncRoles(['admin']);

        $mahasiswa = User::firstOrCreate(
            ['email' => 'mahasiswa@example.test'],
            ['name' => 'Mahasiswa Demo', 'password' => 'password']
        );
        $mahasiswa->syncRoles(['mahasiswa']);

        // Reset cache kembali setelah data tersimpan.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
