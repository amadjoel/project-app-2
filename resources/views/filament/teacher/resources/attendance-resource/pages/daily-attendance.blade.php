<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">
                {{ \Carbon\Carbon::parse($this->selectedDate)->format('l, F j, Y') }}
            </h2>
            
            <div class="text-sm text-gray-500">
                Total Students: {{ $this->getTable()->getRecords()->count() }}
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
