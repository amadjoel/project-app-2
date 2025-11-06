<?php

namespace App\Http\Controllers;

use App\Models\RFIDCard;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RFIDSimulatorController extends Controller
{
    public function index()
    {
        $rfidCards = RFIDCard::with(['user', 'user.class'])
            ->where('status', 'active')
            ->orderBy('card_number')
            ->get();
        
        $recentScans = Attendance::with(['student', 'student.class'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('rfid-simulator', compact('rfidCards', 'recentScans'));
    }

    public function scan(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string',
        ]);

        $rfidCard = RFIDCard::where('card_number', $request->card_number)
            ->where('status', 'active')
            ->with('user')
            ->first();

        if (!$rfidCard) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive RFID card',
                'sound' => 'error'
            ], 404);
        }

        $student = $rfidCard->user;
        $today = Carbon::today();

        // Find or create attendance record for today
        $attendance = Attendance::firstOrCreate(
            [
                'student_id' => $student->id,
                'date' => $today,
            ],
            [
                'teacher_id' => 1, // Default to first teacher, you can change this
                'status' => 'present',
            ]
        );

        $action = null;
        $message = null;

        // If no check-in time, this is a check-in
        if (!$attendance->check_in_time) {
            $attendance->check_in_time = Carbon::now()->format('H:i:s');
            $attendance->status = 'present';
            $action = 'check-in';
            $message = "Welcome, {$student->name}! Checked in at " . Carbon::now()->format('h:i A');
        }
        // If check-in exists but no check-out, this is a check-out
        elseif (!$attendance->check_out_time) {
            $attendance->check_out_time = Carbon::now()->format('H:i:s');
            $action = 'check-out';
            $message = "Goodbye, {$student->name}! Checked out at " . Carbon::now()->format('h:i A');
        }
        // If both exist, this is a duplicate scan
        else {
            return response()->json([
                'success' => false,
                'message' => "{$student->name} has already checked in and out today",
                'sound' => 'warning',
                'student' => [
                    'name' => $student->name,
                    'class' => $student->class->name ?? 'No Class',
                    'check_in' => Carbon::parse($attendance->check_in_time)->format('h:i A'),
                    'check_out' => Carbon::parse($attendance->check_out_time)->format('h:i A'),
                ]
            ]);
        }

        $attendance->save();

        return response()->json([
            'success' => true,
            'action' => $action,
            'message' => $message,
            'sound' => 'success',
            'student' => [
                'name' => $student->name,
                'class' => $student->class->name ?? 'No Class',
                'photo' => null, // You can add student photo here if you have it
                'check_in' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('h:i A') : null,
                'check_out' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('h:i A') : null,
            ]
        ]);
    }
}
