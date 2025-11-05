<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ParentStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parents = User::role('parent')->get();
        $students = User::role('student')->get();

        // Group students into chunks of 10
        $studentChunks = $students->chunk(10);

        // Assign each chunk to a parent
        foreach ($parents as $index => $parent) {
            if (isset($studentChunks[$index])) {
                $parent->students()->attach($studentChunks[$index]->pluck('id'));
            }
        }
    }
}
