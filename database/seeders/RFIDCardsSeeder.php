<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RFIDCard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RFIDCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students without an RFID card
        $students = User::role('student')
            ->whereDoesntHave('rfidCards', function($query) {
                $query->where('status', 'active');
            })
            ->get();

        // Generate 100 unique RFID cards
        $cards = collect();
        for ($i = 1; $i <= 100; $i++) {
            // Generate a unique 10-character hexadecimal card number
            do {
                $cardNumber = strtoupper(Str::random(10));
            } while ($cards->contains('card_number', $cardNumber) || RFIDCard::where('card_number', $cardNumber)->exists());

            // Create the card
            $card = RFIDCard::create([
                'card_number' => $cardNumber,
                'status' => 'active',
            ]);

            $cards->push($card);
        }

        // Assign cards to students (one per student)
        foreach ($students as $index => $student) {
            if ($index < $cards->count()) {
                $cards[$index]->update([
                    'user_id' => $student->id
                ]);
            }
        }
    }
}