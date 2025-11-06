<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class AttendanceExportController extends Controller
{
    public function allCsv()
    {
        $records = Attendance::with('student')
            ->where('teacher_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        $filename = 'attendance-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Student', 'Status', 'Check In', 'Check Out', 'Notes']);
            foreach ($records as $r) {
                fputcsv($handle, [
                    optional($r->date)->format('Y-m-d') ?? (string) $r->date,
                    optional($r->student)->name,
                    $r->status,
                    $r->check_in_time,
                    $r->check_out_time,
                    $r->notes,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function allPdf()
    {
        $records = Attendance::with('student')
            ->where('teacher_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.attendance', [
            'records' => $records,
        ])->setPaper('a4', 'portrait');

        $filename = 'attendance-' . now()->format('Ymd-His') . '.pdf';
        return response()->streamDownload(fn () => print($pdf->output()), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
