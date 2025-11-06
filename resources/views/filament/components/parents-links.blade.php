<div class="flex flex-wrap gap-2">
    @foreach($parents as $parent)
        <a href="{{ route('filament.admin.resources.users.edit', ['record' => $parent->id]) }}" 
           class="text-primary-600 hover:text-primary-500 hover:underline font-medium">
            {{ $parent->name }}
        </a>
        @if(!$loop->last)
            <span class="text-gray-400">•</span>
        @endif
    @endforeach
</div>
