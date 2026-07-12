<?php

use App\Models\Incident;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use App\Enums\IncidentStatus;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentCategory;

new #[Title('Live Map')] class extends Component {
    public ?Incident $selectedIncident = null;
    public string $selectedStatus = '';

    public function mount(): void
    {
        if (!auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'), navigate: true);
            return;
        }
    }

    public function viewIncident(int $id): void
    {
        $this->selectedIncident = Incident::with('user')->find($id);
        if ($this->selectedIncident) {
            $this->selectedStatus = $this->selectedIncident->status;
            Flux::modal('live-map-details-modal')->show();
        }
    }

    public function updateStatus(string $status): void
    {
        if ($this->selectedIncident) {
            $this->selectedIncident->status = $status;
            $this->selectedIncident->save();
            
            $this->selectedStatus = $status;
            Flux::toast(variant: 'success', text: __('Incident status updated to :status.', ['status' => $status]));
            
            $activeIncidents = Incident::whereIn('status', [
                IncidentStatus::Dispatched->value,
                IncidentStatus::Processing->value,
                IncidentStatus::InProgress->value
            ])->get();
            
            $this->dispatch('refresh-incidents', incidents: $activeIncidents->toArray());
        }
    }
}; ?>

<div>
    <!-- Leaflet mapping resources -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="space-y-6 max-w-7xl mx-auto px-4 py-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-4 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                    Live Operations Map
                </h1>
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mt-1">
                    Real-time geographic visualization of active distress alerts
                </p>
            </div>
            
            <!-- Map Pin Legend -->
            <div class="flex items-center gap-4 bg-white dark:bg-zinc-900 px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold text-zinc-700 dark:text-zinc-300">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span> High Severity
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Medium Severity
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Low Severity
                </span>
            </div>
        </div>

        @php
            $activeIncidents = \App\Models\Incident::whereIn('status', [
                IncidentStatus::Dispatched->value,
                IncidentStatus::Processing->value,
                IncidentStatus::InProgress->value
            ])->get();
        @endphp

        <!-- Main Map and List Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            <!-- Sidebar: Active Incidents List -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4 max-h-[600px] overflow-y-auto">
                <div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">Active Incidents List</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">{{ $activeIncidents->count() }} active reports found</p>
                </div>
                
                <flux:separator />

                <div class="space-y-3" id="incident-cards-container">
                    @forelse($activeIncidents as $inc)
                        <div 
                            onclick="window.dispatchEvent(new CustomEvent('pan-to-incident', {detail: {lat: {{ $inc->latitude }}, lng: {{ $inc->longitude }}}}));"
                            class="p-3 bg-neutral-50 dark:bg-zinc-850 hover:bg-neutral-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-750 rounded-xl cursor-pointer transition-all duration-150 text-left space-y-2 group"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-sm text-zinc-800 dark:text-zinc-200">{{ $inc->code }}</span>
                                @if($inc->severity === 'High')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/60">High</span>
                                @elseif($inc->severity === 'Medium')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60">Medium</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-900/60">Low</span>
                                @endif
                            </div>
                            
                            <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $inc->category }} Incident</div>
                            <div class="text-[11px] text-zinc-550 dark:text-zinc-400 truncate">{{ $inc->address }}</div>
                            
                            <div class="flex justify-between items-center border-t border-zinc-100 dark:border-zinc-800 pt-2 mt-2">
                                <span class="text-[10px] text-zinc-450">{{ $inc->created_at->diffForHumans() }}</span>
                                <button 
                                    type="button" 
                                    wire:click.stop="viewIncident({{ $inc->id }})" 
                                    class="text-[10px] font-extrabold text-[#801818] hover:underline"
                                >
                                    MANAGE
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-xs font-medium text-zinc-500">
                            No active emergency incidents currently reported.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Interactive Leaflet Map -->
            <div class="lg:col-span-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-4 shadow-sm z-0 relative">
                <div
                    wire:ignore
                    x-data="liveMapComponent({{ $activeIncidents->toJson() }}, '{{ $this->getId() }}')"
                    class="w-full"
                >
                    <div id="live-map-canvas" class="h-[568px] w-full rounded-2xl border border-zinc-200 dark:border-zinc-800 z-0"></div>
                </div>
            </div>

        </div>

        <!-- High-Fidelity Custom Incident Details Modal -->
        <flux:modal name="live-map-details-modal" class="md:w-[650px] p-6 rounded-3xl bg-neutral-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 max-h-[90vh] overflow-y-auto">
            @if($selectedIncident)
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-extrabold text-neutral-900 dark:text-white">{{ $selectedIncident->code }}</span>
                            <span class="text-sm font-semibold text-zinc-555 dark:text-zinc-455">[{{ $selectedIncident->category }}]</span>
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

                    <!-- Details Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <!-- Left Details -->
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Reporter Details</span>
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
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 min-h-[60px] text-zinc-700 dark:text-zinc-300 font-medium">
                                    {{ $selectedIncident->description ?: 'No description provided.' }}
                                </div>
                            </div>
                        </div>

                        <!-- Right Location Map details -->
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Geographic Location</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 text-sm space-y-1">
                                    <div class="font-bold text-zinc-850 dark:text-zinc-200">{{ $selectedIncident->address }}</div>
                                    <div class="text-xs text-zinc-500">Coordinates: {{ $selectedIncident->latitude }}, {{ $selectedIncident->longitude }}</div>
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
                        <flux:button variant="ghost" x-on:click="Flux.modal('live-map-details-modal').close()" class="rounded-xl px-6 cursor-pointer">
                            Close details
                        </flux:button>
                    </div>
                </div>
            @endif
        </flux:modal>

    </div>

    <script>
    (function() {
        const registerComponent = () => {
            Alpine.data('liveMapComponent', (initialIncidents, componentId) => ({
                map: null,
                markers: [],
                incidents: initialIncidents,
                init() {
                    const startMap = () => {
                        this.map = L.map('live-map-canvas', {zoomControl: false}).setView([12.9644, 124.0044], 13);
                        
                        // Load custom style layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap'
                        }).addTo(this.map);

                        L.control.zoom({
                            position: 'bottomright'
                        }).addTo(this.map);

                        this.loadPins(componentId);
                    };

                    if (typeof L !== 'undefined') {
                        startMap();
                    } else {
                        const interval = setInterval(() => {
                            if (typeof L !== 'undefined') {
                                clearInterval(interval);
                                startMap();
                            }
                        }, 50);
                    }

                    window.addEventListener('pan-to-incident', (e) => {
                        if (this.map) {
                            this.map.setView([e.detail.lat, e.detail.lng], 16, { animate: true });
                        }
                    });

                    window.addEventListener('refresh-incidents', (e) => {
                        this.incidents = e.detail.incidents;
                        if (this.map) {
                            this.loadPins(componentId);
                        }
                    });
                },
                loadPins(componentId) {
                    if (!this.map) return;

                    // Clear existing
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.markers = [];

                    this.incidents.forEach(inc => {
                        let color = 'emerald';
                        if (inc.severity === 'High') color = 'red';
                        else if (inc.severity === 'Medium') color = 'amber';

                        let pinHtml = `
                            <div class='relative'>
                                <div class='absolute -top-3 -left-3 h-6 w-6 rounded-full bg-${color}-500 border-2 border-white dark:border-zinc-900 shadow-md flex items-center justify-center text-white'>
                                    <span class='text-[9px] font-extrabold'>${inc.category.charAt(0)}</span>
                                </div>
                                <div class='absolute -top-3 -left-3 h-6 w-6 rounded-full bg-${color}-500 animate-ping opacity-45'></div>
                            </div>
                        `;

                        let customIcon = L.divIcon({
                            html: pinHtml,
                            className: `custom-marker-${inc.id}`,
                            iconSize: [24, 24]
                        });

                        let marker = L.marker([inc.latitude, inc.longitude], {icon: customIcon})
                            .addTo(this.map)
                            .bindPopup(`
                                <div class='p-2 space-y-1.5 text-xs text-left'>
                                    <div class='flex justify-between items-center gap-4 font-bold border-b border-zinc-100 pb-1'>
                                        <span>${inc.code}</span>
                                        <span class='uppercase tracking-wide text-[9px]'>${inc.severity}</span>
                                    </div>
                                    <div><strong>Type:</strong> ${inc.category}</div>
                                    <div><strong>Address:</strong> ${inc.address}</div>
                                    <div><strong>Status:</strong> ${inc.status}</div>
                                    <div class='pt-1.5 border-t border-zinc-100 mt-1 flex justify-end'>
                                        <button onclick="window.Livewire.find('${componentId}').viewIncident(${inc.id})" class='bg-[#801818] hover:bg-[#601010] text-white text-[9px] font-bold px-2 py-1 rounded transition-colors cursor-pointer'>
                                            Manage Alert
                                        </button>
                                    </div>
                                </div>
                            `);
                        
                        this.markers.push(marker);
                    });
                }
            }));
        };

        if (window.Alpine) {
            registerComponent();
        } else {
            document.addEventListener('alpine:init', registerComponent);
        }
    })();
    </script>
</div>
