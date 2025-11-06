<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage rfid cards',
            'view rfid cards',
            'manage users',
            'view users',
            'manage roles',
            'view roles',
            'manage students',
            'view students',
            'manage parents',
            'view parents',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles and assign created permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->syncPermissions([
            'view rfid cards',
            'view users',
            'view students',
            'view parents',
        ]);

        $parent = Role::firstOrCreate(['name' => 'parent']);
        $parent->syncPermissions([
            'view students',
        ]);

        Role::firstOrCreate(['name' => 'student']);
    }
}