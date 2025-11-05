<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin' => 'Administrator',
            'teacher' => 'Teacher',
            'parent' => 'Parent',
            'student' => 'Student'
        ];

        foreach ($roles as $name => $description) {
            Role::create([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }
    }
}
