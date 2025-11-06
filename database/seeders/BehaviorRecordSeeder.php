<?php

namespace Database\Seeders;

use App\Models\BehaviorRecord;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BehaviorRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get teacher1
        $teacher = User::where('email', 'teacher1@example.com')->first();
        
        if (!$teacher) {
            $this->command->warn('Teacher1 not found. Please run UserSeeder first.');
            return;
        }
        
        // Get students assigned to this teacher
        $studentIds = \DB::table('attendances')
            ->where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('student_id')
            ->toArray();
        
        if (empty($studentIds)) {
            $this->command->warn('No students found for teacher1. Please run AttendanceSeeder first.');
            return;
        }
        
        $behaviors = [
            // Positive behaviors
            [
                'type' => 'positive',
                'category' => 'participation',
                'title' => 'Excellent class participation',
                'description' => 'Asked thoughtful questions and contributed to class discussion',
                'points' => 2,
            ],
            [
                'type' => 'positive',
                'category' => 'cooperation',
                'title' => 'Great teamwork during group activity',
                'description' => 'Worked well with peers and helped others understand the material',
                'points' => 2,
            ],
            [
                'type' => 'positive',
                'category' => 'leadership',
                'title' => 'Showed leadership in group project',
                'description' => 'Took initiative to organize team and delegate tasks effectively',
                'points' => 3,
            ],
            [
                'type' => 'positive',
                'category' => 'responsibility',
                'title' => 'Completed all homework on time',
                'description' => 'Consistently submits quality work before deadlines',
                'points' => 1,
            ],
            [
                'type' => 'positive',
                'category' => 'respect',
                'title' => 'Respectful behavior',
                'description' => 'Showed courtesy and respect to peers and teacher',
                'points' => 1,
            ],
            
            // Negative behaviors
            [
                'type' => 'negative',
                'category' => 'disruption',
                'title' => 'Talking during lesson',
                'description' => 'Repeatedly talked to neighbor during instruction time after warnings',
                'points' => -2,
                'requires_followup' => true,
            ],
            [
                'type' => 'negative',
                'category' => 'rule_violation',
                'title' => 'Used phone during class',
                'description' => 'Was using phone for non-educational purposes during lesson',
                'points' => -1,
            ],
            [
                'type' => 'negative',
                'category' => 'conflict',
                'title' => 'Argument with classmate',
                'description' => 'Had a verbal disagreement with another student during group work',
                'points' => -2,
                'requires_followup' => true,
                'parent_notified' => true,
            ],
            [
                'type' => 'negative',
                'category' => 'responsibility',
                'title' => 'Forgot homework again',
                'description' => 'Third time this week student forgot to bring homework',
                'points' => -1,
                'parent_notified' => true,
            ],
            
            // Neutral observations
            [
                'type' => 'neutral',
                'category' => 'other',
                'title' => 'Seemed tired today',
                'description' => 'Student appeared sleepy and less engaged than usual',
                'points' => 0,
            ],
        ];
        
        // Create behavior records for the last 14 days
        $startDate = now()->subDays(14);
        
        for ($day = 0; $day < 14; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            // Randomly select 2-5 students per day
            $numRecords = rand(2, 5);
            $selectedStudents = collect($studentIds)->random(min($numRecords, count($studentIds)));
            
            foreach ($selectedStudents as $studentId) {
                $behavior = $behaviors[array_rand($behaviors)];
                
                $record = [
                    'student_id' => $studentId,
                    'teacher_id' => $teacher->id,
                    'date' => $date->format('Y-m-d'),
                    'time' => $date->setTime(rand(8, 15), rand(0, 59))->format('H:i:s'),
                    'type' => $behavior['type'],
                    'category' => $behavior['category'],
                    'title' => $behavior['title'],
                    'description' => $behavior['description'],
                    'points' => $behavior['points'],
                    'parent_notified' => $behavior['parent_notified'] ?? false,
                    'requires_followup' => $behavior['requires_followup'] ?? false,
                ];
                
                // If parent was notified, set the timestamp
                if ($record['parent_notified']) {
                    $record['parent_notified_at'] = $date->copy()->addHours(rand(1, 4));
                }
                
                // Some follow-ups are completed
                if ($record['requires_followup'] && rand(0, 100) > 40) {
                    $record['followup_completed_at'] = $date->copy()->addDays(rand(1, 3));
                    $record['followup_notes'] = 'Met with student. Discussed behavior expectations. Student agreed to improve.';
                }
                
                BehaviorRecord::create($record);
            }
        }
        
        $this->command->info('Behavior records seeded successfully!');
    }
}
