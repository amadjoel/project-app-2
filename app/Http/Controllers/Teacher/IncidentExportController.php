<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\IncidentLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class IncidentExportController extends Controller
{
    public function allCsv()
    {
        $records = IncidentLog::with('student')
            ->where('teacher_id', Auth::id())
            ->orderBy('incident_date', 'desc')
            ->get();

        $filename = 'incidents-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Time', 'Student', 'Type', 'Severity', 'Title', 'Parent Notified', 'Resolved']);
            foreach ($records as $r) {
                fputcsv($handle, [
                    optional($r->incident_date)->format('Y-m-d') ?? (string) $r->incident_date,
                    $r->incident_time,
                    optional($r->student)->name,
                    $r->type,
                    $r->severity,
                    $r->title,
                    $r->parent_notified ? 'Yes' : 'No',
                    $r->resolved ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function allPdf()
    {
        $records = IncidentLog::with('student')
            ->where('teacher_id', Auth::id())
            ->orderBy('incident_date', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.incidents', [
            'records' => $records,
        ])->setPaper('a4', 'portrait');

        $filename = 'incidents-' . now()->format('Ymd-His') . '.pdf';
        return response()->streamDownload(fn () => print($pdf->output()), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
