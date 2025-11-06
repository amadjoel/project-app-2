<?php

namespace Database\Seeders;

use App\Models\AuthorizedPickup;
use App\Models\RFIDCard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthorizedPickupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Generating authorized pickup records...');

        // For each parent-student pivot entry, create an authorized_pickup entry
        $pairs = DB::table('parent_student')->get();

        foreach ($pairs as $pair) {
            $parentId = $pair->parent_id;
            $studentId = $pair->student_id;

            // Find parent's active RFID card if any
            $rfid = RFIDCard::where('user_id', $parentId)->where('status', 'active')->first();

            AuthorizedPickup::firstOrCreate([
                'parent_id' => $parentId,
                'student_id' => $studentId,
            ], [
                'rfid_card_id' => $rfid ? $rfid->id : null,
                'allowed' => true,
                'notes' => null,
            ]);
        }

        // Get all parents and students
        $parents = User::role('parent')->get();
        $students = User::role('student')->get();

        if ($parents->isEmpty() || $students->isEmpty()) {
            $this->command->warn('Not enough parents or students to create 500+ records.');
            return;
        }

        // Create realistic pickup records simulating daily pickups
        // For the last 60 days, each parent picks up their assigned child(ren)
        $this->command->info('Simulating daily pickup events for the last 60 days...');

        // Get parent-student relationships
        $parentStudentPairs = DB::table('parent_student')->get();
        
        // Generate pickup history for the last 60 days
        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now();

        $created = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // For each parent-student pair, create pickup record
            foreach ($parentStudentPairs as $pair) {
                // 90% chance of pickup on any given day (sometimes parents miss pickup)
                if (rand(1, 100) > 90) {
                    continue;
                }

                $parent = $parents->firstWhere('id', $pair->parent_id);
                if (!$parent) continue;

                $rfid = RFIDCard::where('user_id', $parent->id)->where('status', 'active')->first();
                
                // Random pickup times (dismissal time varies between 3-4 PM)
                $baseHour = 15; // 3 PM
                $timeIn = $date->copy()->setTime($baseHour, rand(0, 59));
                
                // Most pickups are quick (5-30 minutes)
                $timeOut = $timeIn->copy()->addMinutes(rand(5, 30));
                
                // Determine status
                $rand = rand(1, 100);
                if ($rand <= 85) {
                    $status = 'present'; // On time
                } elseif ($rand <= 95) {
                    $status = 'late'; // Late pickup
                    $timeIn = $date->copy()->setTime($baseHour, rand(30, 59))->addHour();
                    $timeOut = $timeIn->copy()->addMinutes(rand(5, 20));
                } else {
                    $status = 'absent'; // No pickup
                    $timeIn = null;
                    $timeOut = null;
                }

                AuthorizedPickup::create([
                    'parent_id' => $pair->parent_id,
                    'student_id' => $pair->student_id,
                    'rfid_card_id' => $rfid ? $rfid->id : null,
                    'allowed' => true,
                    'time_in' => $timeIn ? $timeIn->format('H:i:s') : null,
                    'time_out' => $timeOut ? $timeOut->format('H:i:s') : null,
                    'notes' => $status === 'late' ? 'Late pickup - ' . $this->getRandomNote() : ($rand <= 5 ? $this->getRandomNote() : null),
                    'created_at' => $date->copy()->setTime(rand(15, 17), rand(0, 59)),
                    'updated_at' => $date->copy()->setTime(rand(15, 17), rand(0, 59)),
                ]);

                $created++;
            }
        }

        // If we still need more records to reach 500+, create additional ones with random combinations
        $currentCount = AuthorizedPickup::count();
        $targetCount = 500;
        $recordsToCreate = max(0, $targetCount - $currentCount);

        if ($recordsToCreate > 0) {
            $this->command->info("Creating {$recordsToCreate} additional records to reach target...");
            
            for ($i = 0; $i < $recordsToCreate; $i++) {
                $parent = $parents->random();
                $student = $students->random();
                
                $rfid = RFIDCard::where('user_id', $parent->id)->where('status', 'active')->first();
                
                // Random date within the last 60 days
                $randomDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );
                
                // Skip weekends
                if ($randomDate->isWeekend()) {
                    $i--;
                    continue;
                }

                // Random pickup times
                $timeIn = $randomDate->copy()->setTime(15, rand(0, 59));
                $timeOut = $timeIn->copy()->addMinutes(rand(5, 30));

                AuthorizedPickup::create([
                    'parent_id' => $parent->id,
                    'student_id' => $student->id,
                    'rfid_card_id' => $rfid ? $rfid->id : null,
                    'allowed' => rand(1, 100) <= 95,
                    'time_in' => $timeIn->format('H:i:s'),
                    'time_out' => rand(1, 100) <= 80 ? $timeOut->format('H:i:s') : null,
                    'notes' => rand(1, 100) <= 20 ? $this->getRandomNote() : null,
                    'created_at' => $randomDate->copy()->setTime(rand(15, 17), rand(0, 59)),
                    'updated_at' => $randomDate->copy()->setTime(rand(15, 17), rand(0, 59)),
                ]);

                $created++;
            }
        }

        $finalCount = AuthorizedPickup::count();
        $this->command->info("Created {$created} new records. Total: {$finalCount} authorized pickup records.");
    }

    private function getRandomNote(): string
    {
        $notes = [
            'Regular pickup',
            'Emergency contact pickup',
            'Grandparent pickup',
            'After school program',
            'Early dismissal',
            'Doctor appointment',
            'Family event',
            'Authorized family friend',
            'Carpooling parent',
            'Verified ID at gate',
        ];

        return $notes[array_rand($notes)];
    }
}
