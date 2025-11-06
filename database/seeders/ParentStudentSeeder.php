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

        if ($parents->isEmpty() || $students->isEmpty()) {
            $this->command->warn('No parents or students found. Skipping parent-student relationships.');
            return;
        }

        // Each student gets 1-2 parents
        foreach ($students as $student) {
            // Randomly select 1 or 2 parents for this student
            $numParents = rand(1, 2);
            $selectedParents = $parents->random(min($numParents, $parents->count()));
            
            foreach ($selectedParents as $parent) {
                // Avoid duplicate relationships
                if (!$student->parents()->where('parent_id', $parent->id)->exists()) {
                    $student->parents()->attach($parent->id);
                }
            }
        }

        $count = \DB::table('parent_student')->count();
        $this->command->info("Created {$count} parent-student relationships.");
    }
}
