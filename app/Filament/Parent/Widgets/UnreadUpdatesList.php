<?php

namespace App\Filament\Parent\Widgets;

use App\Models\BehaviorRecord;
use App\Models\IncidentLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UnreadUpdatesList extends Widget
{
    protected static ?string $heading = 'Unread Updates from Teachers';

    protected static string $view = 'filament.parent.widgets.unread-updates-list';

    // Ensure this widget renders after others on the dashboard
    protected static ?int $sort = 1000;

    // Span full width of the dashboard grid (2 columns by default)
    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 2,
        'lg' => 2,
        'xl' => 2,
        '2xl' => 2,
    ];

    // Ensure the widget starts at the first column across breakpoints
    protected int|string|array $columnStart = [
        'sm' => 1,
        'md' => 1,
        'lg' => 1,
        'xl' => 1,
        '2xl' => 1,
    ];

    public function markAsRead(string $model, int $id): void
    {
        $user = Auth::user();
        if (! $user) return;

        if ($model === 'incident') {
            $record = IncidentLog::where('id', $id)
                ->whereIn('student_id', $user->students->pluck('id'))
                ->first();
            if ($record) {
                $record->update([
                    'parent_notified' => true,
                    'parent_notified_at' => now(),
                ]);
            }
        } elseif ($model === 'behavior') {
            $record = BehaviorRecord::where('id', $id)
                ->whereIn('student_id', $user->students->pluck('id'))
                ->first();
            if ($record) {
                $record->update([
                    'parent_notified' => true,
                    'parent_notified_at' => now(),
                ]);
            }
        }
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        if (! $user) return;
        $ids = $user->students->pluck('id');

        IncidentLog::whereIn('student_id', $ids)
            ->where('parent_notified', false)
            ->update([
                'parent_notified' => true,
                'parent_notified_at' => now(),
            ]);

        BehaviorRecord::whereIn('student_id', $ids)
            ->where('parent_notified', false)
            ->whereIn('type', ['negative', 'positive'])
            ->update([
                'parent_notified' => true,
                'parent_notified_at' => now(),
            ]);
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $childrenIds = $user?->students?->pluck('id') ?? collect();

        if ($childrenIds->isEmpty()) {
            return ['items' => collect()];
        }

        $incidents = IncidentLog::with('student')
            ->whereIn('student_id', $childrenIds)
            ->where('parent_notified', false)
            ->latest('incident_date')
            ->get()
            ->map(function ($i) {
                // Normalize "when" to a Carbon instance
                $when = null;
                try {
                    if (! empty($i->incident_date)) {
                        $when = $i->incident_date instanceof Carbon ? $i->incident_date : Carbon::parse($i->incident_date);
                    } elseif (! empty($i->created_at)) {
                        $when = $i->created_at instanceof Carbon ? $i->created_at : Carbon::parse($i->created_at);
                    } else {
                        $when = Carbon::now();
                    }
                } catch (\Throwable $e) {
                    $when = Carbon::now();
                }

                return [
                    'id' => $i->id,
                    'model' => 'incident',
                    'title' => $i->title ?? 'Incident',
                    'summary' => $i->description ?? ($i->type ? ucfirst($i->type) : ''),
                    'student' => $i->student?->name,
                    'when' => $when,
                    'icon' => 'heroicon-m-exclamation-triangle',
                    'color' => 'warning',
                ];
            })
            ->values()
            ->toBase();

        $behaviors = BehaviorRecord::with('student')
            ->whereIn('student_id', $childrenIds)
            ->where('parent_notified', false)
            ->whereIn('type', ['negative', 'positive'])
            ->latest('date')
            ->get()
            ->map(function ($b) {
                $points = (int) $b->points;
                // Build a proper datetime for display without double date specification
                $when = null;
                try {
                    if (! empty($b->time)) {
                        $t = Carbon::parse($b->time);
                        if (! empty($b->date)) {
                            $d = Carbon::parse($b->date);
                            $when = Carbon::create(
                                $d->year,
                                $d->month,
                                $d->day,
                                $t->hour,
                                $t->minute,
                                $t->second
                            );
                        } else {
                            $when = $t; // time contains full datetime or just time (use today)
                        }
                    } elseif (! empty($b->date)) {
                        $when = Carbon::parse($b->date);
                    } else {
                        $when = $b->created_at instanceof Carbon ? $b->created_at : Carbon::parse($b->created_at);
                    }
                } catch (\Throwable $e) {
                    $when = Carbon::now();
                }
                return [
                    'id' => $b->id,
                    'model' => 'behavior',
                    'title' => $b->title ?? 'Behavior Update',
                    'summary' => trim(($b->category ? ucfirst(str_replace('_',' ',$b->category)).'. ' : '').($b->description ?? '')),
                    'student' => $b->student?->name,
                    'when' => $when,
                    'icon' => $points >= 0 ? 'heroicon-m-hand-thumb-up' : 'heroicon-m-hand-thumb-down',
                    'color' => $points >= 0 ? 'success' : 'danger',
                ];
            })
            ->values()
            ->toBase();

        $items = $incidents->merge($behaviors)
            ->sortByDesc('when')
            ->values();

        return [
            'items' => $items,
        ];
    }
}
