<x-filament-widgets::widget>
    <div class="fi-section rounded-xl border bg-white dark:bg-gray-900 p-6">
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <x-heroicon-s-sparkles class="w-5 h-5 text-primary-600" />
                    AI-Generated Weekly Report
                </h3>
                <div class="text-xs text-gray-500">
                    {{ $weekStart->format('M j') }} - {{ $weekEnd->format('M j, Y') }}
                </div>
            </div>
            <div class="h-1 w-full bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500 rounded-full"></div>
        </div>

        @if($report)
            {{-- Overall Summary --}}
            <div class="mb-6 p-4 bg-gradient-to-br from-primary-50 to-purple-50 dark:from-primary-900/20 dark:to-purple-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center">
                        <x-heroicon-s-light-bulb class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h4 class="font-semibold text-primary-900 dark:text-primary-100 mb-1">Weekly Summary</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $report['summary'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Key Insights --}}
            <div class="space-y-4 mb-6">
                <h4 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-m-chart-bar class="w-5 h-5 text-gray-500" />
                    Key Insights
                </h4>
                
                <div class="grid gap-3">
                    {{-- Attendance Insight --}}
                    <div class="flex items-start gap-3 p-3 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-800">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-success-600 dark:text-success-400 mt-0.5 flex-shrink-0" />
                        <div>
                            <div class="text-xs font-medium text-success-900 dark:text-success-100 mb-0.5">Attendance</div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $report['insights']['attendance'] }}</div>
                        </div>
                    </div>

                    {{-- Behavior Insight --}}
                    @php
                        $behaviorColor = $metrics['behavior']['totalPoints'] > 0 ? 'success' : ($metrics['behavior']['totalPoints'] < 0 ? 'danger' : 'gray');
                    @endphp
                    <div class="flex items-start gap-3 p-3 bg-{{ $behaviorColor }}-50 dark:bg-{{ $behaviorColor }}-900/20 rounded-lg border border-{{ $behaviorColor }}-200 dark:border-{{ $behaviorColor }}-800">
                        @if($metrics['behavior']['totalPoints'] > 0)
                            <x-heroicon-s-hand-thumb-up class="w-5 h-5 text-{{ $behaviorColor }}-600 dark:text-{{ $behaviorColor }}-400 mt-0.5 flex-shrink-0" />
                        @elseif($metrics['behavior']['totalPoints'] < 0)
                            <x-heroicon-s-hand-thumb-down class="w-5 h-5 text-{{ $behaviorColor }}-600 dark:text-{{ $behaviorColor }}-400 mt-0.5 flex-shrink-0" />
                        @else
                            <x-heroicon-s-minus class="w-5 h-5 text-{{ $behaviorColor }}-600 dark:text-{{ $behaviorColor }}-400 mt-0.5 flex-shrink-0" />
                        @endif
                        <div>
                            <div class="text-xs font-medium text-{{ $behaviorColor }}-900 dark:text-{{ $behaviorColor }}-100 mb-0.5">Behavior</div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $report['insights']['behavior'] }}</div>
                        </div>
                    </div>

                    {{-- Incidents Insight --}}
                    @php
                        $incidentColor = $metrics['incidents']['total'] == 0 ? 'success' : ($metrics['incidents']['unresolved'] > 0 ? 'warning' : 'info');
                    @endphp
                    <div class="flex items-start gap-3 p-3 bg-{{ $incidentColor }}-50 dark:bg-{{ $incidentColor }}-900/20 rounded-lg border border-{{ $incidentColor }}-200 dark:border-{{ $incidentColor }}-800">
                        @if($metrics['incidents']['total'] == 0)
                            <x-heroicon-s-shield-check class="w-5 h-5 text-{{ $incidentColor }}-600 dark:text-{{ $incidentColor }}-400 mt-0.5 flex-shrink-0" />
                        @else
                            <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-{{ $incidentColor }}-600 dark:text-{{ $incidentColor }}-400 mt-0.5 flex-shrink-0" />
                        @endif
                        <div>
                            <div class="text-xs font-medium text-{{ $incidentColor }}-900 dark:text-{{ $incidentColor }}-100 mb-0.5">Incidents</div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $report['insights']['incidents'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recommendations --}}
            @if(!empty($report['recommendations']))
                <div class="pt-4 border-t">
                    <h4 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                        <x-heroicon-m-clipboard-document-check class="w-5 h-5 text-gray-500" />
                        Recommended Actions
                    </h4>
                    <ul class="space-y-2">
                        @foreach($report['recommendations'] as $recommendation)
                            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <x-heroicon-m-arrow-right class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" />
                                <span>{{ $recommendation }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @else
            <div class="text-center py-8 text-gray-500">
                <x-heroicon-o-document-text class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                <p class="text-sm">No children linked to your account.</p>
                <p class="text-xs mt-1">Weekly reports will appear here once children are added.</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
