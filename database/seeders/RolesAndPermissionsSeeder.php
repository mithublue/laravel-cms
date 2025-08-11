<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view admin',
            'manage users',
            'manage roles',
            'manage pages',
            'manage posts',
            'manage news',
            'manage products',
            'manage media',
            'manage menus',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $editor = Role::firstOrCreate(['name' => 'Editor']);
        $author = Role::firstOrCreate(['name' => 'Author']);
        $customer = Role::firstOrCreate(['name' => 'Customer']);

        // Assign permissions
        $admin->syncPermissions(Permission::all());

        $editor->syncPermissions([
            'view admin',
            'manage pages',
            'manage posts',
            'manage news',
            'manage media',
            'manage menus',
        ]);

        $author->syncPermissions([
            'view admin',
            'manage posts',
            'manage news',
        ]);

        $customer->syncPermissions([]);
    }
}
