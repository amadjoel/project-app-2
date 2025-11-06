<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class SystemManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.system-management';
    
    protected static ?string $navigationLabel = 'System Management';
    
    protected static ?string $title = 'System Management';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 99;

    public function getHeading(): string
    {
        return 'System Management';
    }

    public function getSubheading(): string
    {
        return 'Audit logs, data backup, and export tools';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->dispatch('$refresh');
                    Notification::make()
                        ->title('Page refreshed')
                        ->success()
                        ->send();
                }),
        ];
    }

    // Get audit log statistics
    public function getAuditStats(): array
    {
        return [
            'total_users' => \App\Models\User::count(),
            'total_students' => \App\Models\User::role('student')->count(),
            'total_teachers' => \App\Models\User::role('teacher')->count(),
            'total_parents' => \App\Models\User::role('parent')->count(),
            'total_classes' => \App\Models\ClassModel::count(),
            'total_attendance' => \App\Models\Attendance::count(),
            'total_behaviors' => \App\Models\BehaviorRecord::count(),
            'total_incidents' => \App\Models\IncidentLog::count(),
            'total_rfid_cards' => \App\Models\RFIDCard::count(),
            'active_rfid_cards' => \App\Models\RFIDCard::where('status', 'active')->count(),
            'total_pickups' => DB::table('authorized_pickups')->count(),
            'today_attendance' => \App\Models\Attendance::whereDate('date', today())->count(),
            'this_week_incidents' => \App\Models\IncidentLog::whereBetween('incident_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    // Export all data as JSON
    public function exportAllData()
    {
        try {
            $data = [
                'exported_at' => now()->toIso8601String(),
                'users' => \App\Models\User::with(['roles', 'permissions'])->get(),
                'classes' => \App\Models\ClassModel::with(['teacher', 'students'])->get(),
                'attendances' => \App\Models\Attendance::with(['student', 'teacher'])->get(),
                'behavior_records' => \App\Models\BehaviorRecord::with(['student', 'teacher'])->get(),
                'incident_logs' => \App\Models\IncidentLog::with(['student', 'teacher'])->get(),
                'rfid_cards' => \App\Models\RFIDCard::with('user')->get(),
                'authorized_pickups' => DB::table('authorized_pickups')->get(),
                'parent_student' => DB::table('parent_student')->get(),
            ];

            $filename = 'system_backup_' . now()->format('Y-m-d_His') . '.json';
            $path = 'backups/' . $filename;

            Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT));

            Notification::make()
                ->title('Export completed successfully')
                ->body("File saved: {$filename}")
                ->success()
                ->send();

            return response()->download(storage_path('app/' . $path), $filename);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Export failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Export database backup (SQL)
    public function exportDatabaseBackup()
    {
        try {
            $filename = 'database_backup_' . now()->format('Y-m-d_His') . '.sql';
            $path = storage_path('app/backups/' . $filename);

            // Ensure backups directory exists
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($path)
            );

            exec($command, $output, $result);

            if ($result === 0 && file_exists($path)) {
                Notification::make()
                    ->title('Database backup completed')
                    ->body("File saved: {$filename}")
                    ->success()
                    ->send();

                return response()->download($path, $filename);
            } else {
                throw new \Exception('Database backup failed. Make sure mysqldump is available.');
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Database backup failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Export users to CSV
    public function exportUsers()
    {
        try {
            $users = \App\Models\User::with(['roles', 'class'])->get();
            
            $csv = "ID,Name,Email,Role,Class,Date of Birth,Created At\n";
            
            foreach ($users as $user) {
                $csv .= sprintf(
                    "%d,%s,%s,%s,%s,%s,%s\n",
                    $user->id,
                    '"' . str_replace('"', '""', $user->name) . '"',
                    $user->email,
                    $user->roles->pluck('name')->implode(','),
                    $user->class?->name ?? 'N/A',
                    $user->date_of_birth?->format('Y-m-d') ?? 'N/A',
                    $user->created_at->format('Y-m-d H:i:s')
                );
            }

            $filename = 'users_export_' . now()->format('Y-m-d_His') . '.csv';
            
            return response()->streamDownload(function () use ($csv) {
                echo $csv;
            }, $filename);

        } catch (\Exception $e) {
            Notification::make()
                ->title('User export failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Export attendance to CSV
    public function exportAttendance()
    {
        try {
            $attendances = \App\Models\Attendance::with(['student', 'teacher'])->get();
            
            $csv = "ID,Student,Teacher,Date,Status,Check In,Check Out,Notes\n";
            
            foreach ($attendances as $attendance) {
                $csv .= sprintf(
                    "%d,%s,%s,%s,%s,%s,%s,%s\n",
                    $attendance->id,
                    '"' . str_replace('"', '""', $attendance->student->name) . '"',
                    '"' . str_replace('"', '""', $attendance->teacher->name) . '"',
                    $attendance->date,
                    $attendance->status,
                    $attendance->check_in_time ?? 'N/A',
                    $attendance->check_out_time ?? 'N/A',
                    '"' . str_replace('"', '""', $attendance->notes ?? '') . '"'
                );
            }

            $filename = 'attendance_export_' . now()->format('Y-m-d_His') . '.csv';
            
            return response()->streamDownload(function () use ($csv) {
                echo $csv;
            }, $filename);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Attendance export failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Export incidents to CSV
    public function exportIncidents()
    {
        try {
            $incidents = \App\Models\IncidentLog::with(['student', 'teacher'])->get();
            
            $csv = "ID,Student,Teacher,Date,Time,Type,Severity,Title,Description,Action Taken,Parent Notified\n";
            
            foreach ($incidents as $incident) {
                $csv .= sprintf(
                    "%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                    $incident->id,
                    '"' . str_replace('"', '""', $incident->student->name) . '"',
                    '"' . str_replace('"', '""', $incident->teacher->name) . '"',
                    $incident->incident_date,
                    $incident->incident_time,
                    $incident->type,
                    $incident->severity,
                    '"' . str_replace('"', '""', $incident->title) . '"',
                    '"' . str_replace('"', '""', $incident->description) . '"',
                    '"' . str_replace('"', '""', $incident->action_taken ?? '') . '"',
                    $incident->parent_notified ? 'Yes' : 'No'
                );
            }

            $filename = 'incidents_export_' . now()->format('Y-m-d_His') . '.csv';
            
            return response()->streamDownload(function () use ($csv) {
                echo $csv;
            }, $filename);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Incidents export failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Clear cache
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            Notification::make()
                ->title('Cache cleared successfully')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Cache clear failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Get recent backups
    public function getRecentBackups(): array
    {
        $backups = [];
        
        if (Storage::disk('local')->exists('backups')) {
            $files = Storage::disk('local')->files('backups');
            
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'size' => $this->formatBytes(Storage::disk('local')->size($file)),
                    'modified' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file))->diffForHumans(),
                ];
            }
        }

        return array_reverse($backups);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

