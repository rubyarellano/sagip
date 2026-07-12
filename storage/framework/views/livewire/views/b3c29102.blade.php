<?php
use App\Models\User;
use App\Models\Incident;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use Carbon\Carbon;
use App\Enums\IncidentStatus;
use App\Enums\UserRole;
?>

<div>
    <div class="space-y-8 max-w-7xl mx-auto px-4 py-6">
        
        <!-- Header Panel with Notification Dropdown -->
        <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-5">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                    Command Center
                </h1>
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mt-1">
                    Real-time incident response and system status monitoring
                </p>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Active Alerts -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block">Active Alerts</span>
                    <h2 class="text-3xl font-extrabold text-neutral-900 dark:text-white mt-2">
                        {{ \App\Models\Incident::whereIn('status', ['Dispatched', 'Processing', 'In Progress'])->count() }}
                    </h2>
                </div>
                <div class="h-12 w-12 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-[#801818] relative">
                    <flux:icon.fire class="h-6 w-6" />
                    <span class="absolute top-0 right-0 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                </div>
            </div>

            <!-- Total Users -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block">Registered Citizens</span>
                    <h2 class="text-3xl font-extrabold text-neutral-900 dark:text-white mt-2">
                        {{ \App\Models\User::where('role', UserRole::Citizen->value)->count() }}
                    </h2>
                </div>
                <div class="h-12 w-12 rounded-xl bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-blue-600">
                    <flux:icon.users class="h-6 w-6" />
                </div>
            </div>

            <!-- Resolved Alerts -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block">Resolved Cases</span>
                    <h2 class="text-3xl font-extrabold text-neutral-900 dark:text-white mt-2">
                        {{ \App\Models\Incident::where('status', 'Resolved')->count() }}
                    </h2>
                </div>
                <div class="h-12 w-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600">
                    <flux:icon.check-circle class="h-6 w-6" />
                </div>
            </div>

            <!-- Dismissed/Total -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block">Dismissed Reports</span>
                    <h2 class="text-3xl font-extrabold text-neutral-900 dark:text-white mt-2">
                        {{ \App\Models\Incident::where('status', 'Dismissed')->count() }}
                    </h2>
                </div>
                <div class="h-12 w-12 rounded-xl bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center text-zinc-500">
                    <flux:icon.x-circle class="h-6 w-6" />
                </div>
            </div>
        </div>

        <!-- Analytics Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Left Column: Alert Trends Area Chart (2/3 width) -->
            <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 shadow-sm"
                 wire:ignore
                 x-data="{
                    chart: null,
                    init() {
                        let options = {
                            chart: {
                                type: 'area',
                                height: 280,
                                toolbar: { show: false },
                                fontFamily: 'Outfit, sans-serif'
                            },
                            series: [{
                                name: 'Distress Alerts',
                                data: @js($chartData)
                            }],
                            xaxis: {
                                categories: @js($chartCategories),
                                labels: { style: { colors: '#71717a', fontSize: '11px', fontWeight: 'bold' } }
                            },
                            yaxis: {
                                min: 0,
                                forceNiceScale: true,
                                labels: { style: { colors: '#71717a', fontSize: '11px', fontWeight: 'bold' } }
                            },
                            stroke: { curve: 'smooth', width: 3 },
                            colors: ['#801818'],
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.35,
                                    opacityTo: 0.02,
                                    stops: [40, 100, 100]
                                }
                            },
                            dataLabels: { enabled: false },
                            grid: { borderColor: '#f4f4f5' }
                        };
                        this.chart = new ApexCharts(document.querySelector('#alert-trends-chart'), options);
                        this.chart.render();

                        window.addEventListener('refresh-charts', (e) => {
                            this.chart.updateOptions({
                                xaxis: { categories: e.detail.chartCategories }
                            });
                            this.chart.updateSeries([{
                                data: e.detail.chartData
                            }]);
                        });
                    }
                 }"
            >
                <div class="flex items-center justify-between mb-4 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white">Distress Alert Trends</h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Volume of emergency alerts over the past week</p>
                    </div>
                </div>
                <div id="alert-trends-chart" class="w-full"></div>
            </div>

            <!-- Right Column: Category Breakdown Donut Chart (1/3 width) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 shadow-sm"
                 wire:ignore
                 x-data="{
                    chart: null,
                    init() {
                        let options = {
                            chart: {
                                type: 'donut',
                                height: 280,
                                fontFamily: 'Outfit, sans-serif'
                            },
                            series: @js($categoryChartData),
                            labels: @js($categoryChartLabels),
                            colors: ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#6b7280'],
                            dataLabels: { enabled: false },
                            legend: {
                                position: 'bottom',
                                labels: { colors: '#71717a', fontSize: '11px', fontWeight: 'bold' }
                            },
                            stroke: { width: 0 }
                        };
                        this.chart = new ApexCharts(document.querySelector('#category-breakdown-chart'), options);
                        this.chart.render();

                        window.addEventListener('refresh-charts', (e) => {
                            this.chart.updateOptions({
                                labels: e.detail.categoryChartLabels
                            });
                            this.chart.updateSeries(e.detail.categoryChartData);
                        });
                    }
                 }"
            >
                <div class="flex items-center justify-between mb-4 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white">Category Distribution</h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Share of reports by incident type</p>
                    </div>
                </div>
                <div id="category-breakdown-chart" class="w-full"></div>
            </div>
        </div>

        <!-- Alert Feed Section -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-zinc-150 dark:border-zinc-800">
                <div>
                    <h3 class="text-lg font-bold text-zinc-950 dark:text-zinc-50">Recent Alert Live Feeds</h3>
                    <p class="text-xs text-zinc-500 mt-1">Real-time incoming distress feeds from paired ResQbands</p>
                </div>
                
                <flux:button :href="route('admin.alert-history')" wire:navigate size="sm" class="text-xs font-semibold">
                    View Full History
                </flux:button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800 text-xs font-bold text-zinc-400 uppercase">
                            <th class="py-3 px-4">Timestamp</th>
                            <th class="py-3 px-4">Alert ID</th>
                            <th class="py-3 px-4">Alert Type</th>
                            <th class="py-3 px-4">Severity</th>
                            <th class="py-3 px-4">Location</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-sm">
                        @forelse(\App\Models\Incident::orderBy('created_at', 'desc')->take(10)->get() as $incident)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-850/40 transition-colors duration-150">
                                <td class="py-3 px-4 font-medium text-zinc-650 dark:text-zinc-450">
                                    {{ $incident->created_at->format('H:i') }}
                                    <span class="text-[10px] text-zinc-400 block">{{ $incident->created_at->format('M d') }}</span>
                                </td>
                                <td class="py-3 px-4 font-bold text-zinc-800 dark:text-zinc-200">
                                    {{ $incident->code }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $incident->category }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($incident->severity === 'High')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/60">
                                            High
                                        </span>
                                    @elseif($incident->severity === 'Medium')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60">
                                            Medium
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-900/60">
                                            Low
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-zinc-650 dark:text-zinc-400 font-medium">
                                    {{ $incident->address }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($incident->status === 'Dispatched')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-650 text-white shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                            Dispatched
                                        </span>
                                    @elseif($incident->status === 'Processing')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                            Processing
                                        </span>
                                    @elseif($incident->status === 'In Progress')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-600 text-white shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                            In Progress
                                        </span>
                                    @elseif($incident->status === 'Resolved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            Resolved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-neutral-200 text-neutral-600">
                                            Dismissed
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <flux:button size="sm" wire:click="viewIncident({{ $incident->id }})" class="text-xs font-bold hover:bg-[#801818] hover:text-white cursor-pointer">
                                        VIEW DETAILS
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-450 font-medium">
                                    No incidents reported yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- High-Fidelity Custom Incident Details Modal -->
        <flux:modal name="incident-details-modal" class="md:w-[650px] p-6 rounded-3xl bg-neutral-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 max-h-[90vh] overflow-y-auto">
            @if($selectedIncident)
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-extrabold text-neutral-900 dark:text-white">{{ $selectedIncident->code }}</span>
                            <span class="text-sm font-semibold text-zinc-550 dark:text-zinc-450">[{{ $selectedIncident->category }}]</span>
                        </div>
                        
                        <div>
                            @if($selectedIncident->severity === 'High')
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/60">High Severity</span>
                            @elseif($selectedIncident->severity === 'Medium')
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60">Medium Severity</span>
                            @else
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-900/60">Low Severity</span>
                            @endif
                        </div>
                    </div>

                    <!-- Dispatch Details Info Form Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <!-- Left Details -->
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Reporter Information</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 space-y-1.5">
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Name:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $selectedIncident->reporter_name ?: ($selectedIncident->user ? $selectedIncident->user->name : 'Anonymous') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Contact:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $selectedIncident->reporter_phone ?: ($selectedIncident->user ? $selectedIncident->user->phone : 'N/A') }}</span>
                                    </div>
                                    @if($selectedIncident->user && $selectedIncident->user->device_id)
                                        <div class="flex justify-between border-t border-zinc-100 dark:border-zinc-800 pt-1.5 mt-1.5">
                                            <span class="text-zinc-500">ResQband ID:</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $selectedIncident->user->device_id }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Situation Description</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 min-h-[60px] text-zinc-700 dark:text-zinc-300">
                                    {{ $selectedIncident->description ?: 'No description provided.' }}
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Reported Time</span>
                                <div class="text-zinc-600 dark:text-zinc-400 font-medium">
                                    {{ $selectedIncident->created_at->format('F d, Y \a\t H:i:s') }}
                                </div>
                            </div>
                        </div>

                        <!-- Right Location Map -->
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Geographic Location</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 text-sm space-y-1">
                                    <div class="font-bold text-zinc-850 dark:text-zinc-200">{{ $selectedIncident->address }}</div>
                                    <div class="text-xs text-zinc-500">Coordinates: {{ $selectedIncident->latitude }}, {{ $selectedIncident->longitude }}</div>
                                </div>
                            </div>

                            <!-- Mini OSM Leaflet Simulation or Static Map Graphic -->
                            <div class="h-40 rounded-xl overflow-hidden bg-neutral-200 dark:bg-zinc-800 border border-neutral-350 dark:border-zinc-750 relative flex items-center justify-center">
                                <!-- Leaflet static display mockup -->
                                <div class="absolute inset-0 bg-cover bg-center filter dark:brightness-75" style="background-image: url('https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/{{ $selectedIncident->longitude }},{{ $selectedIncident->latitude }},14,0/400x200?access_token=mock');">
                                    <!-- Fallback design mapping graphic -->
                                    <div class="w-full h-full bg-[#f8f9fa] dark:bg-zinc-800 flex flex-col items-center justify-center p-4 text-center">
                                        <flux:icon.map class="h-8 w-8 text-neutral-450 mb-1" />
                                        <span class="text-[11px] font-bold text-zinc-800 dark:text-zinc-200">Incident Location Pin</span>
                                        <span class="text-[10px] text-zinc-500">Lat: {{ $selectedIncident->latitude }} / Lng: {{ $selectedIncident->longitude }}</span>
                                    </div>
                                </div>
                                <div class="absolute z-10 p-2 bg-[#801818] rounded-full text-white shadow-md animate-bounce">
                                    <flux:icon.exclamation-triangle class="h-4 w-4" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dispatcher Workflow Status Control Panel -->
                    <div class="bg-zinc-100 dark:bg-zinc-850 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 space-y-3">
                        <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block">Update Emergency Status</span>
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" wire:click="updateStatus('Dispatched')" class="grow text-xs {{ $selectedStatus === 'Dispatched' ? '!bg-red-650 !text-white' : '' }}">
                                Dispatch Responders
                            </flux:button>
                            <flux:button size="sm" wire:click="updateStatus('Processing')" class="grow text-xs {{ $selectedStatus === 'Processing' ? '!bg-amber-500 !text-white' : '' }}">
                                Mark Processing
                            </flux:button>
                            <flux:button size="sm" wire:click="updateStatus('In Progress')" class="grow text-xs {{ $selectedStatus === 'In Progress' ? '!bg-blue-600 !text-white' : '' }}">
                                Set In Progress
                            </flux:button>
                            <flux:button size="sm" wire:click="updateStatus('Resolved')" class="grow text-xs {{ $selectedStatus === 'Resolved' ? '!bg-emerald-600 !text-white' : '' }}">
                                Resolve Alert
                            </flux:button>
                            <flux:button size="sm" wire:click="updateStatus('Dismissed')" class="grow text-xs {{ $selectedStatus === 'Dismissed' ? '!bg-zinc-650 !text-white' : '' }}">
                                Dismiss Case
                            </flux:button>
                        </div>
                    </div>

                    <!-- Close Modal Action -->
                    <div class="flex justify-end pt-2 border-t border-zinc-200 dark:border-zinc-800">
                        <flux:button variant="ghost" x-on:click="Flux.modal('incident-details-modal').close()" class="rounded-xl px-6 cursor-pointer">
                            Close details
                        </flux:button>
                    </div>
                </div>
            @endif
        </flux:modal>

    </div>
</div>