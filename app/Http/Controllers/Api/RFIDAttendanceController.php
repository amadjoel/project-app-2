<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RFIDCard;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RFIDAttendanceController extends Controller
{
    /**
     * Process RFID card scan for attendance
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string',
            'reader_id' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the RFID card
        $rfidCard = RFIDCard::where('card_number', $request->card_number)
            ->where('status', 'active')
            ->with('user')
            ->first();

        if (!$rfidCard) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive RFID card',
                'card_number' => $request->card_number,
                'timestamp' => now()->toIso8601String(),
            ], 404);
        }

        $student = $rfidCard->user;

        // Check if student has the student role
        if (!$student->hasRole('student')) {
            return response()->json([
                'success' => false,
                'message' => 'Card is not assigned to a student',
                'card_number' => $request->card_number,
                'timestamp' => now()->toIso8601String(),
            ], 400);
        }

        $today = Carbon::today();
        $now = Carbon::now();

        // Find or create attendance record for today
        $attendance = Attendance::firstOrCreate(
            [
                'student_id' => $student->id,
                'date' => $today,
            ],
            [
                'teacher_id' => $student->class?->teacher_id ?? 1, // Use class teacher or default
                'status' => 'present',
            ]
        );

        $action = null;
        $message = null;
        $isNewRecord = $attendance->wasRecentlyCreated;

        // Determine action: check-in or check-out
        if (!$attendance->check_in_time) {
            // First scan of the day - CHECK IN
            $attendance->check_in_time = $now->format('H:i:s');
            $attendance->status = 'present';
            
            // Mark as late if after 8:30 AM
            if ($now->hour > 8 || ($now->hour === 8 && $now->minute > 30)) {
                $attendance->status = 'late';
            }
            
            $action = 'check-in';
            $message = "Welcome, {$student->name}! Checked in at " . $now->format('h:i A');
            
        } elseif (!$attendance->check_out_time) {
            // Second scan of the day - CHECK OUT
            $attendance->check_out_time = $now->format('H:i:s');
            $action = 'check-out';
            $message = "Goodbye, {$student->name}! Checked out at " . $now->format('h:i A');
            
        } else {
            // Already checked in and out - duplicate scan
            return response()->json([
                'success' => false,
                'message' => "{$student->name} has already checked in and out today",
                'action' => 'duplicate',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'class' => $student->class?->name,
                ],
                'attendance' => [
                    'date' => $attendance->date->format('Y-m-d'),
                    'status' => $attendance->status,
                    'check_in_time' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('h:i A') : null,
                    'check_out_time' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('h:i A') : null,
                ],
                'timestamp' => now()->toIso8601String(),
            ], 200);
        }

        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => $message,
            'action' => $action,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'class' => $student->class?->name,
                'date_of_birth' => $student->date_of_birth?->format('Y-m-d'),
            ],
            'attendance' => [
                'id' => $attendance->id,
                'date' => $attendance->date->format('Y-m-d'),
                'status' => $attendance->status,
                'check_in_time' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('h:i A') : null,
                'check_out_time' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('h:i A') : null,
            ],
            'rfid' => [
                'card_number' => $rfidCard->card_number,
                'reader_id' => $request->reader_id,
                'location' => $request->location,
            ],
            'timestamp' => now()->toIso8601String(),
        ], 201);
    }

    /**
     * Get attendance status for a student by card number
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $rfidCard = RFIDCard::where('card_number', $request->card_number)
            ->where('status', 'active')
            ->with(['user', 'user.class'])
            ->first();

        if (!$rfidCard) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive RFID card',
            ], 404);
        }

        $student = $rfidCard->user;
        $today = Carbon::today();

        $attendance = Attendance::where('student_id', $student->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'class' => $student->class?->name,
            ],
            'attendance' => $attendance ? [
                'date' => $attendance->date->format('Y-m-d'),
                'status' => $attendance->status,
                'check_in_time' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('h:i A') : null,
                'check_out_time' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('h:i A') : null,
            ] : null,
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Health check endpoint for RFID readers
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'message' => 'RFID Attendance API is operational',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
