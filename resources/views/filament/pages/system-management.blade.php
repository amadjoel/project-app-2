<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $stats = $this->getAuditStats();
            @endphp
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary-600">{{ number_format($stats['total_users']) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Users</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">{{ number_format($stats['total_students']) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Students</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600">{{ number_format($stats['total_attendance']) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Attendance Records</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600">{{ number_format($stats['total_incidents']) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Incidents</div>
                </div>
            </x-filament::section>
        </div>

        <!-- Quick Stats -->
        <x-filament::section>
            <x-slot name="heading">
                System Statistics
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Teachers</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($stats['total_teachers']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Parents</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($stats['total_parents']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Classes</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($stats['total_classes']) }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">RFID Cards (Total)</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($stats['total_rfid_cards']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Active RFID Cards</span>
                        <span class="text-sm text-success-600">{{ number_format($stats['active_rfid_cards']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Authorized Pickups</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($stats['total_pickups']) }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Behavior Records</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($stats['total_behaviors']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Today's Attendance</span>
                        <span class="text-sm text-primary-600">{{ number_format($stats['today_attendance']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">This Week's Incidents</span>
                        <span class="text-sm text-warning-600">{{ number_format($stats['this_week_incidents']) }}</span>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Data Export Section -->
        <x-filament::section>
            <x-slot name="heading">
                Data Export
            </x-slot>
            <x-slot name="description">
                Export system data in various formats for backup or analysis
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Full System Backup -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-primary-100 dark:bg-primary-900 rounded-lg">
                            <x-heroicon-o-document-arrow-down class="w-6 h-6 text-primary-600" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Full System Backup</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Export all data as JSON</p>
                            <x-filament::button 
                                wire:click="exportAllData" 
                                size="sm" 
                                class="mt-3"
                                color="primary"
                            >
                                Export JSON
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                <!-- Database Backup -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-success-100 dark:bg-success-900 rounded-lg">
                            <x-heroicon-o-circle-stack class="w-6 h-6 text-success-600" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Database Backup</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Export SQL database dump</p>
                            <x-filament::button 
                                wire:click="exportDatabaseBackup" 
                                size="sm" 
                                class="mt-3"
                                color="success"
                            >
                                Export SQL
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                <!-- Users Export -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-info-100 dark:bg-info-900 rounded-lg">
                            <x-heroicon-o-users class="w-6 h-6 text-info-600" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Users Export</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Export all users to CSV</p>
                            <x-filament::button 
                                wire:click="exportUsers" 
                                size="sm" 
                                class="mt-3"
                                color="info"
                            >
                                Export CSV
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                <!-- Attendance Export -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-warning-100 dark:bg-warning-900 rounded-lg">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-warning-600" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Attendance Export</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Export attendance records</p>
                            <x-filament::button 
                                wire:click="exportAttendance" 
                                size="sm" 
                                class="mt-3"
                                color="warning"
                            >
                                Export CSV
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                <!-- Incidents Export -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-danger-100 dark:bg-danger-900 rounded-lg">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-danger-600" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Incidents Export</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Export incident logs</p>
                            <x-filament::button 
                                wire:click="exportIncidents" 
                                size="sm" 
                                class="mt-3"
                                color="danger"
                            >
                                Export CSV
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                <!-- Clear Cache -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                            <x-heroicon-o-arrow-path class="w-6 h-6 text-gray-600" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Clear Cache</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Clear application cache</p>
                            <x-filament::button 
                                wire:click="clearCache" 
                                size="sm" 
                                class="mt-3"
                                color="gray"
                            >
                                Clear Now
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Recent Backups -->
        <x-filament::section>
            <x-slot name="heading">
                Recent Backups
            </x-slot>
            <x-slot name="description">
                List of recently created backup files
            </x-slot>

            @php
                $backups = $this->getRecentBackups();
            @endphp

            @if(count($backups) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Filename</th>
                                <th class="px-4 py-3 text-left font-semibold">Size</th>
                                <th class="px-4 py-3 text-left font-semibold">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($backups as $backup)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $backup['name'] }}</td>
                                    <td class="px-4 py-3">{{ $backup['size'] }}</td>
                                    <td class="px-4 py-3">{{ $backup['modified'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-folder-open class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                    <p>No backups found. Create your first backup above.</p>
                </div>
            @endif
        </x-filament::section>

        <!-- Audit Information -->
        <x-filament::section>
            <x-slot name="heading">
                System Information
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold mb-3">Application Details</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Laravel Version</dt>
                            <dd class="font-medium">{{ app()->version() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">PHP Version</dt>
                            <dd class="font-medium">{{ phpversion() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Environment</dt>
                            <dd class="font-medium">{{ config('app.env') }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h4 class="font-semibold mb-3">Database Details</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Connection</dt>
                            <dd class="font-medium">{{ config('database.default') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Database</dt>
                            <dd class="font-medium">{{ config('database.connections.mysql.database') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Host</dt>
                            <dd class="font-medium">{{ config('database.connections.mysql.host') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
