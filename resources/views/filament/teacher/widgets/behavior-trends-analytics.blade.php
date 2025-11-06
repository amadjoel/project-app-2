<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-primary-500" />
                <span class="text-lg font-semibold">AI Behavior Analytics - Trends & Patterns</span>
            </div>
        </x-slot>
        
        @php
            $data = $this->getAnalytics();
        @endphp
        
        {{-- AI Insights Section --}}
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <x-heroicon-o-sparkles class="w-4 h-4" />
                AI-Generated Insights
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($data['insights'] as $insight)
                    <div class="rounded-lg border p-4 
                        @if($insight['type'] === 'success') bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800
                        @elseif($insight['type'] === 'warning') bg-yellow-50 dark:bg-yellow-950 border-yellow-200 dark:border-yellow-800
                        @elseif($insight['type'] === 'danger') bg-red-50 dark:bg-red-950 border-red-200 dark:border-red-800
                        @else bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800
                        @endif
                    ">
                        <div class="flex items-start gap-3">
                            <div class="
                                @if($insight['type'] === 'success') text-green-600 dark:text-green-400
                                @elseif($insight['type'] === 'warning') text-yellow-600 dark:text-yellow-400
                                @elseif($insight['type'] === 'danger') text-red-600 dark:text-red-400
                                @else text-blue-600 dark:text-blue-400
                                @endif
                            ">
                                @svg($insight['icon'], 'w-5 h-5')
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-sm mb-1
                                    @if($insight['type'] === 'success') text-green-900 dark:text-green-100
                                    @elseif($insight['type'] === 'warning') text-yellow-900 dark:text-yellow-100
                                    @elseif($insight['type'] === 'danger') text-red-900 dark:text-red-100
                                    @else text-blue-900 dark:text-blue-100
                                    @endif
                                ">{{ $insight['title'] }}</h4>
                                <p class="text-xs
                                    @if($insight['type'] === 'success') text-green-700 dark:text-green-200
                                    @elseif($insight['type'] === 'warning') text-yellow-700 dark:text-yellow-200
                                    @elseif($insight['type'] === 'danger') text-red-700 dark:text-red-200
                                    @else text-blue-700 dark:text-blue-200
                                    @endif
                                ">{{ $insight['message'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        {{-- Weekly Comparison --}}
        <div class="mb-6 border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <x-heroicon-o-calendar class="w-4 h-4" />
                Week-over-Week Comparison
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-950 dark:to-green-900 rounded-lg p-4 border border-green-200 dark:border-green-800">
                    <div class="text-xs text-green-700 dark:text-green-300 mb-1">This Week - Positive</div>
                    <div class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $data['week_comparison']['this_week']['positive'] }}</div>
                    @php
                        $change = $data['week_comparison']['this_week']['positive'] - $data['week_comparison']['last_week']['positive'];
                    @endphp
                    @if($change != 0)
                        <div class="text-xs {{ $change > 0 ? 'text-green-600' : 'text-red-600' }} flex items-center gap-1 mt-1">
                            @if($change > 0)
                                <x-heroicon-m-arrow-trending-up class="w-3 h-3" />
                                +{{ $change }}
                            @else
                                <x-heroicon-m-arrow-trending-down class="w-3 h-3" />
                                {{ $change }}
                            @endif
                            from last week
                        </div>
                    @endif
                </div>
                
                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-950 dark:to-red-900 rounded-lg p-4 border border-red-200 dark:border-red-800">
                    <div class="text-xs text-red-700 dark:text-red-300 mb-1">This Week - Negative</div>
                    <div class="text-2xl font-bold text-red-900 dark:text-red-100">{{ $data['week_comparison']['this_week']['negative'] }}</div>
                    @php
                        $change = $data['week_comparison']['this_week']['negative'] - $data['week_comparison']['last_week']['negative'];
                    @endphp
                    @if($change != 0)
                        <div class="text-xs {{ $change < 0 ? 'text-green-600' : 'text-red-600' }} flex items-center gap-1 mt-1">
                            @if($change < 0)
                                <x-heroicon-m-arrow-trending-down class="w-3 h-3" />
                                {{ $change }}
                            @else
                                <x-heroicon-m-arrow-trending-up class="w-3 h-3" />
                                +{{ $change }}
                            @endif
                            from last week
                        </div>
                    @endif
                </div>
                
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <div class="text-xs text-blue-700 dark:text-blue-300 mb-1">Total Points</div>
                    <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                        {{ $data['week_comparison']['this_week']['total_points'] > 0 ? '+' : '' }}{{ $data['week_comparison']['this_week']['total_points'] }}
                    </div>
                    @php
                        $change = $data['week_comparison']['this_week']['total_points'] - $data['week_comparison']['last_week']['total_points'];
                    @endphp
                    @if($change != 0)
                        <div class="text-xs {{ $change > 0 ? 'text-green-600' : 'text-red-600' }} flex items-center gap-1 mt-1">
                            @if($change > 0)
                                <x-heroicon-m-arrow-trending-up class="w-3 h-3" />
                                +{{ $change }}
                            @else
                                <x-heroicon-m-arrow-trending-down class="w-3 h-3" />
                                {{ $change }}
                            @endif
                            from last week
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Category Distribution and Time Patterns --}}
        <div class="grid md:grid-cols-2 gap-6 mb-6 border-t pt-6">
            {{-- Category Distribution --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <x-heroicon-o-tag class="w-4 h-4" />
                    Top Behavior Categories (Last 30 Days)
                </h3>
                <div class="space-y-2">
                    @foreach(array_slice($data['category_stats'], 0, 8) as $category)
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $category['category'] }}</span>
                                    <span class="text-xs font-bold {{ $category['type'] === 'positive' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $category['count'] }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $category['type'] === 'positive' ? 'bg-green-500' : 'bg-red-500' }}" 
                                         style="width: {{ ($category['count'] / max(array_column($data['category_stats'], 'count'))) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Time Patterns --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <x-heroicon-o-clock class="w-4 h-4" />
                    Behavior by Time of Day
                </h3>
                <div class="space-y-3">
                    @foreach($data['time_patterns'] as $pattern)
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ $pattern['period'] }}</div>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $pattern['positive'] }} positive</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $pattern['negative'] }} negative</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        {{-- Student Patterns --}}
        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <x-heroicon-o-users class="w-4 h-4" />
                Student Behavior Leaderboard (Last 30 Days)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-700 dark:text-gray-300 font-semibold">Student</th>
                            <th class="px-3 py-2 text-center text-gray-700 dark:text-gray-300 font-semibold">Positive</th>
                            <th class="px-3 py-2 text-center text-gray-700 dark:text-gray-300 font-semibold">Negative</th>
                            <th class="px-3 py-2 text-center text-gray-700 dark:text-gray-300 font-semibold">Points</th>
                            <th class="px-3 py-2 text-center text-gray-700 dark:text-gray-300 font-semibold">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($data['student_patterns'] as $student)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $student['name'] }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100">
                                        {{ $student['positive'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100">
                                        {{ $student['negative'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center font-bold {{ $student['points'] > 0 ? 'text-green-600' : ($student['points'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                    {{ $student['points'] > 0 ? '+' : '' }}{{ $student['points'] }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if($student['trend'] === 'improving')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100">
                                            <x-heroicon-m-arrow-trending-up class="w-3 h-3" />
                                            <span class="text-xs">Improving</span>
                                        </span>
                                    @elseif($student['trend'] === 'concern')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100">
                                            <x-heroicon-m-arrow-trending-down class="w-3 h-3" />
                                            <span class="text-xs">Concern</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                                            <x-heroicon-m-minus class="w-3 h-3" />
                                            <span class="text-xs">Stable</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if(empty($data['student_patterns']))
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No behavior records found for the last 30 days.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
