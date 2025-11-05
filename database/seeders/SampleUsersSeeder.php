<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SampleUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $teacherRole = Role::where('name', 'teacher')->first();
        $parentRole = Role::where('name', 'parent')->first();
        $studentRole = Role::where('name', 'student')->first();

        // Create 3 admin users
        foreach (range(1, 3) as $i) {
            $admin = User::factory()->create([
                'name' => "Admin User {$i}",
                'email' => "admin{$i}@example.com",
                'password' => Hash::make('password123'),
            ]);
            $admin->assignRole('admin');
        }

        // Create 3 teachers
        foreach (range(1, 3) as $i) {
            $teacher = User::factory()->create([
                'name' => "Teacher User {$i}",
                'email' => "teacher{$i}@example.com",
                'password' => Hash::make('password123'),
            ]);
            $teacher->assignRole('teacher');
        }

        // Create 3 parents
        foreach (range(1, 3) as $i) {
            $parent = User::factory()->create([
                'name' => "Parent User {$i}",
                'email' => "parent{$i}@example.com",
                'password' => Hash::make('password123'),
            ]);
            $parent->assignRole('parent');
        }

        // Create 30 students
        foreach (range(1, 30) as $i) {
            $student = User::factory()->create([
                'name' => "Student User {$i}",
                'email' => "student{$i}@example.com",
                'password' => Hash::make('password123'),
            ]);
            $student->assignRole('student');
        }
    }
}
