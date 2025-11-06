<x-filament-widgets::widget>
    <div class="fi-section rounded-xl border bg-white dark:bg-gray-900 p-4">
        <div class="mb-3">
            <h3 class="text-base font-semibold">Today's Behavior</h3>
            <p class="text-xs text-gray-500">{{ now()->format('l, F j, Y') }}</p>
        </div>

        <div class="space-y-4">
            {{-- Behavior Counts --}}
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Records</h4>
                <div class="grid grid-cols-3 gap-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center">
                            <x-heroicon-s-hand-thumb-up class="w-5 h-5 text-success-600 dark:text-success-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Positive</div>
                            <div class="text-lg font-semibold">{{ $behavior['positive'] }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-danger-100 dark:bg-danger-900/30 rounded-full flex items-center justify-center">
                            <x-heroicon-s-hand-thumb-down class="w-5 h-5 text-danger-600 dark:text-danger-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Negative</div>
                            <div class="text-lg font-semibold">{{ $behavior['negative'] }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                            <x-heroicon-s-minus class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Neutral</div>
                            <div class="text-lg font-semibold">{{ $behavior['neutral'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Points --}}
            <div class="border-t pt-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Points</h4>
                <div class="flex items-center gap-3">
                    @php
                        $points = $behavior['totalPoints'];
                        $color = $points > 0 ? 'success' : ($points < 0 ? 'danger' : 'gray');
                    @endphp
                    <div class="flex-shrink-0 w-12 h-12 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 rounded-full flex items-center justify-center">
                        @if($points > 0)
                            <x-heroicon-s-arrow-trending-up class="w-6 h-6 text-{{ $color }}-600 dark:text-{{ $color }}-400" />
                        @elseif($points < 0)
                            <x-heroicon-s-arrow-trending-down class="w-6 h-6 text-{{ $color }}-600 dark:text-{{ $color }}-400" />
                        @else
                            <x-heroicon-s-minus class="w-6 h-6 text-{{ $color }}-600 dark:text-{{ $color }}-400" />
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Total Points Today</div>
                        <div class="text-2xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">
                            {{ $points > 0 ? '+' : '' }}{{ $points }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Categories --}}
            @if($topCategories->isNotEmpty())
                <div class="border-t pt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top Categories</h4>
                    <div class="space-y-2">
                        @foreach($topCategories as $category)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ ucfirst(str_replace('_', ' ', $category['category'])) }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500">{{ $category['count'] }}×</span>
                                    <span class="font-semibold {{ $category['points'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ $category['points'] > 0 ? '+' : '' }}{{ $category['points'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
