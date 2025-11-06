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

        // First names pool
        $firstNames = [
            'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda',
            'William', 'Barbara', 'David', 'Elizabeth', 'Richard', 'Susan', 'Joseph', 'Jessica',
            'Thomas', 'Sarah', 'Charles', 'Karen', 'Christopher', 'Nancy', 'Daniel', 'Lisa',
            'Matthew', 'Betty', 'Anthony', 'Margaret', 'Mark', 'Sandra', 'Donald', 'Ashley',
            'Steven', 'Kimberly', 'Paul', 'Emily', 'Andrew', 'Donna', 'Joshua', 'Michelle',
            'Kenneth', 'Carol', 'Kevin', 'Amanda', 'Brian', 'Dorothy', 'George', 'Melissa',
            'Edward', 'Deborah', 'Ronald', 'Stephanie', 'Timothy', 'Rebecca', 'Jason', 'Sharon',
            'Jeffrey', 'Laura', 'Ryan', 'Cynthia', 'Jacob', 'Kathleen', 'Gary', 'Amy',
        ];

        // Last names pool
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas',
            'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White',
            'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young',
            'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
            'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell',
            'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz', 'Parker',
            'Cruz', 'Edwards', 'Collins', 'Reyes', 'Stewart', 'Morris', 'Morales', 'Murphy',
        ];

        // Create 3 admin users
        foreach (range(1, 3) as $i) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            $admin = User::factory()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => "{$firstName} {$lastName}",
                'email' => "admin{$i}@example.com",
                'date_of_birth' => now()->subYears(rand(30, 55))->subDays(rand(0, 364)),
                'password' => Hash::make('password123'),
            ]);
            $admin->assignRole('admin');
        }

        // Create 30 teachers
        foreach (range(1, 30) as $i) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            $teacher = User::factory()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => "{$firstName} {$lastName}",
                'email' => "teacher{$i}@example.com",
                'date_of_birth' => now()->subYears(rand(25, 60))->subDays(rand(0, 364)),
                'password' => Hash::make('password123'),
            ]);
            $teacher->assignRole('teacher');
        }

        // Create 50 parents
        foreach (range(1, 50) as $i) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            $parent = User::factory()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => "{$firstName} {$lastName}",
                'email' => "parent{$i}@example.com",
                'date_of_birth' => now()->subYears(rand(30, 50))->subDays(rand(0, 364)),
                'password' => Hash::make('password123'),
            ]);
            $parent->assignRole('parent');
        }

        // Create 100 students (class assignment will happen after ClassSeeder)
        foreach (range(1, 100) as $i) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            $student = User::factory()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => "{$firstName} {$lastName}",
                'email' => "student{$i}@example.com",
                'date_of_birth' => now()->subYears(rand(4, 6))->subDays(rand(0, 364)),
                'password' => Hash::make('password123'),
                'class_id' => null,  // Will be assigned after ClassSeeder runs
            ]);
            $student->assignRole('student');
        }
    }
}
