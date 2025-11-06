<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-wrap items-center gap-2">
            <x-filament::button tag="a" href="{{ route('teacher.exports.attendance.csv') }}" target="_blank" icon="heroicon-o-arrow-down-tray">
                Export Attendance CSV
            </x-filament::button>
            <x-filament::button tag="a" href="{{ route('teacher.exports.attendance.pdf') }}" target="_blank" color="success" icon="heroicon-o-document-arrow-down">
                Export Attendance PDF
            </x-filament::button>
            <x-filament::button tag="a" href="{{ route('teacher.exports.incidents.csv') }}" target="_blank" color="warning" icon="heroicon-o-arrow-down-tray">
                Export Incidents CSV
            </x-filament::button>
            <x-filament::button tag="a" href="{{ route('teacher.exports.incidents.pdf') }}" target="_blank" color="danger" icon="heroicon-o-document-arrow-down">
                Export Incidents PDF
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
