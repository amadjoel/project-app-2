<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all teachers and students
        $teachers = User::role('teacher')->get();
        $students = User::role('student')->get();

        if ($teachers->isEmpty() || $students->isEmpty()) {
            $this->command->warn('No teachers or students found. Skipping attendance seeding.');
            return;
        }

        // Generate attendance for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $this->command->info('Generating attendance records...');

        // For each teacher
        foreach ($teachers as $teacher) {
            // Randomly assign 10-15 students to this teacher
            $teacherStudents = $students->random(min(rand(10, 15), $students->count()));

            // For each day in the last 30 days
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }

                // For each student assigned to this teacher
                foreach ($teacherStudents as $student) {
                    // Random status (90% present, 5% late, 3% absent, 2% excused)
                    $rand = rand(1, 100);
                    if ($rand <= 90) {
                        $status = 'present';
                        $checkIn = $date->copy()->setTime(8, rand(0, 15));
                        $checkOut = $date->copy()->setTime(15, rand(0, 30));
                    } elseif ($rand <= 95) {
                        $status = 'late';
                        $checkIn = $date->copy()->setTime(8, rand(20, 45));
                        $checkOut = $date->copy()->setTime(15, rand(0, 30));
                    } elseif ($rand <= 98) {
                        $status = 'absent';
                        $checkIn = null;
                        $checkOut = null;
                    } else {
                        $status = 'excused';
                        $checkIn = null;
                        $checkOut = null;
                    }

                    Attendance::firstOrCreate(
                        [
                            'student_id' => $student->id,
                            'date' => $date->format('Y-m-d'),
                        ],
                        [
                            'teacher_id' => $teacher->id,
                            'status' => $status,
                            'check_in_time' => $checkIn,
                            'check_out_time' => $checkOut,
                            'notes' => $status === 'late' ? 'Arrived late' : null,
                        ]
                    );
                }
            }
        }

        $count = Attendance::count();
        $this->command->info("Created {$count} attendance records.");
    }
}
