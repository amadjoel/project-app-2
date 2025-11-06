<x-filament-widgets::widget>
    <div class="fi-section rounded-xl border bg-white dark:bg-gray-900 p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-semibold">Unread Updates from Teachers</h3>
            <button wire:click="markAllAsRead" class="fi-btn fi-btn-size-sm fi-btn-color-primary">
                <x-heroicon-m-check class="w-4 h-4 mr-1" /> Mark all as read
            </button>
        </div>

        @if($items->isEmpty())
            <div class="text-sm text-gray-500">All caught up! No new updates.</div>
        @else
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($items as $item)
                    <li class="py-3 flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            @if($item['icon'] ?? false)
                                <x-dynamic-component :component="$item['icon']" class="w-5 h-5 text-{{$item['color'] ?? 'gray'}}-600 mt-0.5" />
                            @endif
                            <div>
                                <div class="text-sm font-medium">
                                    {{ $item['title'] }}
                                    <span class="text-gray-500">— {{ $item['student'] }}</span>
                                </div>
                                @if(!empty($item['summary']))
                                    <div class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">{{ $item['summary'] }}</div>
                                @endif
                                <div class="text-xs text-gray-500 mt-1">
                                    @php($when = $item['when'] instanceof \Carbon\Carbon ? $item['when'] : \Carbon\Carbon::parse($item['when']))
                                    {{ $when->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        <button wire:click="markAsRead('{{ $item['model'] }}', {{ $item['id'] }})" class="fi-btn fi-btn-size-sm fi-btn-color-gray">
                            Mark read
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-filament-widgets::widget>
