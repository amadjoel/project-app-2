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
        // Get all students with their class teachers
        $students = User::role('student')->with('class.teacher')->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Skipping attendance seeding.');
            return;
        }

        // Generate attendance for the last 60 days
        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now();

        $this->command->info('Generating realistic kindergarten attendance records...');

        // Define student punctuality patterns (realistic for 4-6 year olds)
        $punctualityProfiles = [
            'early_bird' => ['weight' => 20, 'attendance_rate' => 98],      // Always early, rarely absent
            'on_time' => ['weight' => 50, 'attendance_rate' => 96],         // Usually on time, very reliable
            'sometimes_late' => ['weight' => 25, 'attendance_rate' => 94],  // Occasionally late, still good
            'frequently_late' => ['weight' => 5, 'attendance_rate' => 90],  // Often late, more absences
        ];

        // Assign each student a punctuality profile
        $studentProfiles = [];
        foreach ($students as $student) {
            $rand = rand(1, 100);
            if ($rand <= 20) {
                $profile = 'early_bird';
            } elseif ($rand <= 70) {
                $profile = 'on_time';
            } elseif ($rand <= 95) {
                $profile = 'sometimes_late';
            } else {
                $profile = 'frequently_late';
            }
            $studentProfiles[$student->id] = $profile;
        }

        // For each day in the last 60 days
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // For each student
            foreach ($students as $student) {
                // Skip if student has no class assigned
                if (!$student->class || !$student->class->teacher) {
                    continue;
                }

                $teacher = $student->class->teacher;
                $profile = $studentProfiles[$student->id];
                $attendanceRate = $punctualityProfiles[$profile]['attendance_rate'];

                // Determine if student attends today based on their attendance rate
                $attendsToday = rand(1, 100) <= $attendanceRate;

                if (!$attendsToday) {
                    // Randomly decide between absent and excused (80% absent, 20% excused)
                    $status = rand(1, 100) <= 80 ? 'absent' : 'excused';
                    $checkIn = null;
                    $checkOut = null;
                    $notes = $status === 'excused' ? collect(['Doctor appointment', 'Family emergency', 'Sick', 'Dentist visit'])->random() : null;
                } else {
                    // Student attends - determine timing based on profile
                    switch ($profile) {
                        case 'early_bird':
                            // Arrives 7:30-7:50 (before 8:00)
                            $checkIn = $date->copy()->setTime(7, rand(30, 50));
                            $status = 'present';
                            break;

                        case 'on_time':
                            // Arrives 7:45-8:15 (mostly on time, rarely late)
                            $arrivalMinute = rand(45, 75); // 45-75 minutes after 7:00
                            $hour = 7 + intdiv($arrivalMinute, 60);
                            $minute = $arrivalMinute % 60;
                            $checkIn = $date->copy()->setTime($hour, $minute);
                            $status = $checkIn->format('H:i') > '08:30' ? 'late' : 'present';
                            break;

                        case 'sometimes_late':
                            // Arrives 7:50-8:40 (sometimes late)
                            $arrivalMinute = rand(50, 100); // 50-100 minutes after 7:00
                            $hour = 7 + intdiv($arrivalMinute, 60);
                            $minute = $arrivalMinute % 60;
                            $checkIn = $date->copy()->setTime($hour, $minute);
                            $status = $checkIn->format('H:i') > '08:30' ? 'late' : 'present';
                            break;

                        case 'frequently_late':
                            // Arrives 8:00-9:00 (often late)
                            $checkIn = $date->copy()->setTime(8, rand(0, 60));
                            $status = $checkIn->format('H:i') > '08:30' ? 'late' : 'present';
                            break;
                    }

                    // Checkout time: 3:00-4:00 PM (kindergarten pickup time)
                    $checkOut = $date->copy()->setTime(15, rand(0, 60));
                    $notes = $status === 'late' ? 'Arrived late' : null;
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
                        'notes' => $notes,
                    ]
                );
            }
        }

        $count = Attendance::count();
        $this->command->info("Created {$count} realistic attendance records.");
    }
}
