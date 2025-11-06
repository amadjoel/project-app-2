<?php

namespace Database\Seeders;

use App\Models\AuthorizedPickup;
use App\Models\RFIDCard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthorizedPickupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        // Additionally, create a few sample entries where a parent is allowed to pick up a student not yet linked
        $parent = User::role('parent')->first();
        $student = User::role('student')->skip(0)->first();
        if ($parent && $student) {
            AuthorizedPickup::firstOrCreate([
                'parent_id' => $parent->id,
                'student_id' => $student->id,
            ], [
                'rfid_card_id' => RFIDCard::where('user_id', $parent->id)->where('status','active')->value('id'),
                'allowed' => true,
                'notes' => 'Seeded sample entry',
            ]);
        }
    }
}
