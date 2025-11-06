<?php

namespace App\Filament\Parent\Widgets;

use App\Models\BehaviorRecord;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyBehaviorSummary extends Widget
{
    protected static ?string $heading = 'Daily Behavior Summary';

    protected static string $view = 'filament.parent.widgets.daily-behavior-summary';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        $user = Auth::user();
        $childrenIds = $user?->students?->pluck('id') ?? collect();

        if ($childrenIds->isEmpty()) {
            return [
                'children' => collect(),
                'behavior' => [
                    'positive' => 0,
                    'negative' => 0,
                    'neutral' => 0,
                    'totalPoints' => 0,
                ],
                'topCategories' => collect(),
            ];
        }

        $today = Carbon::today();

        // Today's behavior records
        $todayBehavior = BehaviorRecord::with('student')
            ->whereIn('student_id', $childrenIds)
            ->where('date', $today)
            ->get();

        $behavior = [
            'positive' => $todayBehavior->where('type', 'positive')->count(),
            'negative' => $todayBehavior->where('type', 'negative')->count(),
            'neutral' => $todayBehavior->where('type', 'neutral')->count(),
            'totalPoints' => $todayBehavior->sum('points'),
        ];

        // Top categories today
        $topCategories = $todayBehavior
            ->filter(fn($b) => !empty($b->category))
            ->groupBy('category')
            ->map(fn($group) => [
                'category' => $group->first()->category,
                'count' => $group->count(),
                'points' => $group->sum('points'),
            ])
            ->sortByDesc('count')
            ->take(3)
            ->values();

        return [
            'children' => $user->students,
            'behavior' => $behavior,
            'topCategories' => $topCategories,
        ];
    }
}
