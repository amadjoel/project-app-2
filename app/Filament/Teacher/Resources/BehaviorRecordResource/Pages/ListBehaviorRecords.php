<?php

namespace App\Filament\Teacher\Resources\BehaviorRecordResource\Pages;

use App\Filament\Teacher\Resources\BehaviorRecordResource;
use App\Models\BehaviorRecord;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListBehaviorRecords extends ListRecords
{
    protected static string $resource = BehaviorRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Records'),
            
            'positive' => Tab::make('Positive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'positive'))
                ->badge(BehaviorRecord::query()
                    ->where('teacher_id', Auth::id())
                    ->where('type', 'positive')
                    ->count()),
            
            'negative' => Tab::make('Negative')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'negative'))
                ->badge(BehaviorRecord::query()
                    ->where('teacher_id', Auth::id())
                    ->where('type', 'negative')
                    ->count()),
            
            'needs_followup' => Tab::make('Needs Follow-up')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('requires_followup', true)->whereNull('followup_completed_at'))
                ->badge(BehaviorRecord::query()
                    ->where('teacher_id', Auth::id())
                    ->where('requires_followup', true)
                    ->whereNull('followup_completed_at')
                    ->count()),
            
            'parent_not_notified' => Tab::make('Parent Not Notified')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('parent_notified', false))
                ->badge(BehaviorRecord::query()
                    ->where('teacher_id', Auth::id())
                    ->where('parent_notified', false)
                    ->count()),
        ];
    }
}
