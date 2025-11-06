<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <x-heroicon-o-sparkles class="w-6 h-6 text-primary-500" />
                <h2 class="text-xl font-bold">AI-Driven Insights</h2>
            </div>
            
            @php
                $data = $this->getSummaryData();
                $insights = $data['insights'];
            @endphp
            
            <div class="space-y-3">
                @foreach($insights as $insight)
                    <div class="p-4 rounded-lg border @if($insight['type'] === 'success') bg-green-50 border-green-200 dark:bg-green-950 dark:border-green-800 @elseif($insight['type'] === 'warning') bg-yellow-50 border-yellow-200 dark:bg-yellow-950 dark:border-yellow-800 @elseif($insight['type'] === 'danger') bg-red-50 border-red-200 dark:bg-red-950 dark:border-red-800 @else bg-blue-50 border-blue-200 dark:bg-blue-950 dark:border-blue-800 @endif">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-0.5">
                                @if($insight['type'] === 'success')
                                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                                @elseif($insight['type'] === 'warning')
                                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                @elseif($insight['type'] === 'danger')
                                    <x-heroicon-o-shield-exclamation class="w-5 h-5 text-red-600 dark:text-red-400" />
                                @else
                                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold @if($insight['type'] === 'success') text-green-800 dark:text-green-200 @elseif($insight['type'] === 'warning') text-yellow-800 dark:text-yellow-200 @elseif($insight['type'] === 'danger') text-red-800 dark:text-red-200 @else text-blue-800 dark:text-blue-200 @endif">
                                    {{ $insight['title'] }}
                                </h3>
                                <p class="mt-1 text-sm @if($insight['type'] === 'success') text-green-700 dark:text-green-300 @elseif($insight['type'] === 'warning') text-yellow-700 dark:text-yellow-300 @elseif($insight['type'] === 'danger') text-red-700 dark:text-red-300 @else text-blue-700 dark:text-blue-300 @endif">
                                    {{ $insight['message'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                    <x-heroicon-o-clock class="w-4 h-4" />
                    Last updated: {{ now()->format('M d, Y H:i') }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
