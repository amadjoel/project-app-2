<x-filament-widgets::widget>
    <div class="fi-section rounded-xl border bg-white dark:bg-gray-900 p-4">
        <div class="mb-3">
            <h3 class="text-base font-semibold">Today's Activity</h3>
            <p class="text-xs text-gray-500">{{ now()->format('l, F j, Y') }}</p>
        </div>

        <div class="space-y-4">
            {{-- Attendance Summary --}}
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attendance</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-success-600 dark:text-success-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Present</div>
                            <div class="text-lg font-semibold">{{ $attendance['present'] }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-danger-100 dark:bg-danger-900/30 rounded-full flex items-center justify-center">
                            <x-heroicon-s-x-circle class="w-5 h-5 text-danger-600 dark:text-danger-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Absent</div>
                            <div class="text-lg font-semibold">{{ $attendance['absent'] }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-warning-100 dark:bg-warning-900/30 rounded-full flex items-center justify-center">
                            <x-heroicon-s-clock class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Late</div>
                            <div class="text-lg font-semibold">{{ $attendance['late'] }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-info-100 dark:bg-info-900/30 rounded-full flex items-center justify-center">
                            <x-heroicon-s-information-circle class="w-5 h-5 text-info-600 dark:text-info-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Excused</div>
                            <div class="text-lg font-semibold">{{ $attendance['excused'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Incidents Summary --}}
            <div class="border-t pt-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Incidents</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                            <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Total</div>
                            <div class="text-lg font-semibold">{{ $incidents['total'] }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-warning-100 dark:bg-warning-900/30 rounded-full flex items-center justify-center">
                            <x-heroicon-s-exclamation-circle class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Unresolved</div>
                            <div class="text-lg font-semibold">{{ $incidents['unresolved'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
