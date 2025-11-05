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

        // create permissions
        Permission::create(['name' => 'manage rfid cards']);
        Permission::create(['name' => 'view rfid cards']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'manage roles']);
        Permission::create(['name' => 'view roles']);
        Permission::create(['name' => 'manage students']);
        Permission::create(['name' => 'view students']);
        Permission::create(['name' => 'manage parents']);
        Permission::create(['name' => 'view parents']);

        // create roles and assign created permissions
        $role = Role::create(['name' => 'admin'])
            ->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'teacher'])
            ->givePermissionTo([
                'view rfid cards',
                'view users',
                'view students',
                'view parents'
            ]);

        $role = Role::create(['name' => 'parent'])
            ->givePermissionTo([
                'view students'
            ]);

        $role = Role::create(['name' => 'student']);
    }
}