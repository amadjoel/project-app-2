<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SampleUsersSeeder::class,  // Creates admins, teachers, parents, and students (with classes)
            ClassSeeder::class,  // Creates classes and assigns teachers
            ParentStudentSeeder::class,
            RFIDCardsSeeder::class,
            AttendanceSeeder::class,
            BehaviorRecordSeeder::class,
            IncidentLogSeeder::class,
            AuthorizedPickupsSeeder::class,
        ]);
    }
}