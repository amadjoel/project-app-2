<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\User;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get teachers who don't already have a class assigned
        $teachers = User::role('teacher')
            ->whereDoesntHave('teacherClass')
            ->limit(10)
            ->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No available teachers found. Please ensure you have teachers in the database.');
            return;
        }

        $classes = [
            [
                'name' => 'Mathematics 101',
                'grade_level' => 'Grade 10',
                'room_number' => 'Room 201',
                'capacity' => 30,
                'description' => 'Introduction to Algebra and Geometry',
                'is_active' => true,
            ],
            [
                'name' => 'English Literature',
                'grade_level' => 'Grade 11',
                'room_number' => 'Room 105',
                'capacity' => 25,
                'description' => 'Classic and contemporary literature studies',
                'is_active' => true,
            ],
            [
                'name' => 'Science Laboratory',
                'grade_level' => 'Grade 9',
                'room_number' => 'Lab 3',
                'capacity' => 20,
                'description' => 'Hands-on experiments in physics, chemistry, and biology',
                'is_active' => true,
            ],
            [
                'name' => 'World History',
                'grade_level' => 'Grade 12',
                'room_number' => 'Room 302',
                'capacity' => 28,
                'description' => 'Ancient civilizations to modern era',
                'is_active' => true,
            ],
            [
                'name' => 'Physical Education',
                'grade_level' => 'All Grades',
                'room_number' => 'Gymnasium',
                'capacity' => 40,
                'description' => 'Sports, fitness, and health education',
                'is_active' => true,
            ],
            [
                'name' => 'Art & Design',
                'grade_level' => 'Grade 10',
                'room_number' => 'Art Studio',
                'capacity' => 22,
                'description' => 'Creative expression through various mediums',
                'is_active' => true,
            ],
            [
                'name' => 'Computer Science',
                'grade_level' => 'Grade 11',
                'room_number' => 'Computer Lab 1',
                'capacity' => 24,
                'description' => 'Programming fundamentals and web development',
                'is_active' => true,
            ],
            [
                'name' => 'Spanish Language',
                'grade_level' => 'Grade 9',
                'room_number' => 'Room 108',
                'capacity' => 26,
                'description' => 'Conversational Spanish and cultural studies',
                'is_active' => true,
            ],
            [
                'name' => 'Music Theory',
                'grade_level' => 'Grade 10',
                'room_number' => 'Music Room',
                'capacity' => 20,
                'description' => 'Understanding musical notation and composition',
                'is_active' => true,
            ],
            [
                'name' => 'Chemistry Advanced',
                'grade_level' => 'Grade 12',
                'room_number' => 'Lab 2',
                'capacity' => 18,
                'description' => 'Advanced concepts in organic and inorganic chemistry',
                'is_active' => true,
            ],
        ];

        foreach ($classes as $index => $classData) {
            // Assign a teacher if available
            if (isset($teachers[$index])) {
                $classData['teacher_id'] = $teachers[$index]->id;
            }

            ClassModel::create($classData);
        }

        $this->command->info('Successfully created ' . count($classes) . ' classes with assigned teachers.');
        
        // Now assign students to classes evenly
        $createdClasses = ClassModel::all();
        $students = User::role('student')->whereNull('class_id')->get();
        
        if ($students->isNotEmpty() && $createdClasses->isNotEmpty()) {
            $classIndex = 0;
            foreach ($students as $student) {
                $assignedClass = $createdClasses[$classIndex % $createdClasses->count()];
                $student->update(['class_id' => $assignedClass->id]);
                $classIndex++;
            }
            $this->command->info('Successfully assigned ' . $students->count() . ' students to classes.');
        }
    }
}

