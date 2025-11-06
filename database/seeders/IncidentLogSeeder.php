<?php

namespace Database\Seeders;

use App\Models\IncidentLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class IncidentLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::role('teacher')->get();
        $students = User::role('student')->get();

        if ($teachers->isEmpty() || $students->isEmpty()) {
            $this->command->warn('No teachers or students found. Please run SampleUsersSeeder first.');
            return;
        }

        $incidents = [
            [
                'type' => 'behavioral',
                'severity' => 'minor',
                'title' => 'Minor disruption in class',
                'description' => 'Student was talking during quiet reading time after one warning.',
                'action_taken' => 'Verbal warning given. Student complied immediately.',
            ],
            [
                'type' => 'behavioral',
                'severity' => 'moderate',
                'title' => 'Refusing to follow instructions',
                'description' => 'Student refused to put away toys during transition time despite multiple requests.',
                'action_taken' => 'Timeout given. Met with student after class to discuss expectations.',
                'parent_notified' => true,
            ],
            [
                'type' => 'behavioral',
                'severity' => 'serious',
                'title' => 'Physical altercation with peer',
                'description' => 'Student pushed another student during playground time, resulting in minor injury.',
                'action_taken' => 'Both students separated. Parents contacted. Behavior contract initiated.',
                'parent_notified' => true,
                'resolved' => false,
            ],
            [
                'type' => 'health',
                'severity' => 'minor',
                'title' => 'Minor scrape on playground',
                'description' => 'Student fell while running and scraped knee.',
                'action_taken' => 'First aid applied. Band-aid and ice pack provided.',
                'parent_notified' => true,
            ],
            [
                'type' => 'health',
                'severity' => 'moderate',
                'title' => 'Bumped head on table',
                'description' => 'Student bumped head on corner of table. Small bump visible.',
                'action_taken' => 'Ice pack applied. Monitored for 30 minutes. No signs of concussion.',
                'parent_notified' => true,
            ],
            [
                'type' => 'safety',
                'severity' => 'moderate',
                'title' => 'Left classroom without permission',
                'description' => 'Student left classroom to use restroom without asking teacher.',
                'action_taken' => 'Reviewed safety rules with student. Reminder about asking permission.',
                'parent_notified' => true,
            ],
            [
                'type' => 'other',
                'severity' => 'minor',
                'title' => 'Accidentally broke crayon',
                'description' => 'Student pressed too hard on crayon and it broke.',
                'action_taken' => 'Reminded student to use gentle pressure. No further action needed.',
            ],
            [
                'type' => 'other',
                'severity' => 'minor',
                'title' => 'Spilled juice on carpet',
                'description' => 'Student knocked over juice container during snack time.',
                'action_taken' => 'Student helped clean up. Carpet cleaned immediately.',
                'parent_notified' => false,
            ],
            [
                'type' => 'behavioral',
                'severity' => 'minor',
                'title' => 'Upset during parent drop-off',
                'description' => 'Student cried for 5 minutes after parent left.',
                'action_taken' => 'Comforted student. Engaged in favorite activity. Settled quickly.',
            ],
            [
                'type' => 'behavioral',
                'severity' => 'moderate',
                'title' => 'Anxiety about upcoming event',
                'description' => 'Student expressed worry about school performance.',
                'action_taken' => 'Provided reassurance. Discussed feelings with student.',
                'parent_notified' => true,
                'resolved' => false,
            ],
            [
                'type' => 'other',
                'severity' => 'minor',
                'title' => 'Forgot lunch',
                'description' => 'Student came to school without lunch.',
                'action_taken' => 'Contacted parent. Backup lunch provided.',
                'parent_notified' => true,
            ],
        ];

        // Create incidents for the last 30 days
        $startDate = now()->subDays(30);

        for ($day = 0; $day < 30; $day++) {
            $date = $startDate->copy()->addDays($day);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // 2-6 incidents per day (kindergarten typically has few serious incidents)
            $numIncidents = rand(2, 6);

            for ($i = 0; $i < $numIncidents; $i++) {
                $incident = $incidents[array_rand($incidents)];
                $student = $students->random();
                $teacher = $teachers->random();

                $record = [
                    'student_id' => $student->id,
                    'teacher_id' => $teacher->id,
                    'incident_date' => $date->format('Y-m-d'),
                    'incident_time' => $date->setTime(rand(8, 15), rand(0, 59))->format('H:i:s'),
                    'type' => $incident['type'],
                    'severity' => $incident['severity'],
                    'title' => $incident['title'],
                    'description' => $incident['description'],
                    'action_taken' => $incident['action_taken'],
                    'parent_notified' => $incident['parent_notified'] ?? false,
                    'resolved' => $incident['resolved'] ?? true,
                ];

                // If parent was notified, set timestamp
                if ($record['parent_notified']) {
                    $record['parent_notified_at'] = $date->copy()->addHours(rand(1, 4));
                    
                    // 70% chance parent responded
                    if (rand(1, 100) <= 70) {
                        $record['parent_response'] = collect([
                            'Thank you for letting me know. We will discuss this at home.',
                            'We appreciate you keeping us informed.',
                            'I will talk to them about this tonight.',
                            'Thanks for the update. Please keep me posted.',
                            'We will work on this behavior at home.',
                        ])->random();
                    }
                }

                // Some incidents are resolved
                if ($record['resolved'] && rand(0, 100) > 20) {
                    $record['resolved_at'] = $date->copy()->addDays(rand(1, 5));
                }

                IncidentLog::create($record);
            }
        }

        $count = IncidentLog::count();
        $this->command->info("Created {$count} incident log records.");
    }
}
