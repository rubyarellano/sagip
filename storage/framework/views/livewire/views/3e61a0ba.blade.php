<?php
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Incident;
use Flux\Flux;
use App\Enums\IncidentCategory;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Illuminate\Validation\Rules\Enum;
?>

<div>
    <!-- Leaflet mapping resources -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="space-y-8 max-w-7xl mx-auto px-4 py-6">
        
        <div class="flex items-center justify-between border-b border-neutral-200 dark:border-zinc-800 pb-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                New Incident Report
            </h1>
            <span class="text-sm font-semibold text-neutral-500">Emergency Dispatch Interface</span>
        </div>

        <form wire:submit="submitReport" class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            
            <!-- LEFT COLUMN: Categories and Map -->
            <div class="space-y-6">
                
                <!-- Step 1. Incident Category -->
                <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-neutral-200 dark:border-zinc-800 shadow-sm space-y-4">
                    <h3 class="text-md font-bold text-neutral-900 dark:text-white">Step 1. Incident Category</h3>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            'Fire' => 'flame',
                            'Flood' => 'waves',
                            'Medical' => 'heart',
                            'Landslide' => 'exclamation-circle',
                            'Earthquake' => 'bolt',
                            'Other' => 'ellipsis-horizontal'
                        ] as $cat => $icon)
                            <button
                                type="button"
                                wire:click="selectCategory('{{ $cat }}')"
                                class="flex flex-col items-center justify-center p-4 rounded-xl border text-sm font-bold transition-all cursor-pointer {{ $category === $cat ? 'bg-[#801818] border-[#801818] text-white shadow-sm' : 'bg-neutral-50 dark:bg-zinc-800 border-neutral-200 dark:border-zinc-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-zinc-750' }}"
                            >
                                <span class="text-lg mb-1">
                                    @if($cat === 'Fire')
                                        🔥
                                    @elseif($cat === 'Flood')
                                        🌊
                                    @elseif($cat === 'Medical')
                                        ❤️
                                    @elseif($cat === 'Landslide')
                                        🪨
                                    @elseif($cat === 'Earthquake')
                                        🌋
                                    @else
                                        💬
                                    @endif
                                </span>
                                {{ $cat }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Step 2. Location Details -->
                <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-neutral-200 dark:border-zinc-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-md font-bold text-neutral-900 dark:text-white">Step 2. Location Details</h3>
                    </div>

                    <!-- Leaflet Interactive Map Wrapper -->
                    <div
                        wire:ignore
                        x-data="{
                            map: null,
                            marker: null,
                            init() {
                                const startMap = () => {
                                    this.map = L.map('map', {zoomControl: false}).setView([{{ $latitude }}, {{ $longitude }}], 14);
                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                        maxZoom: 19,
                                        attribution: '© OSM'
                                    }).addTo(this.map);

                                    L.control.zoom({
                                        position: 'bottomright'
                                    }).addTo(this.map);

                                    this.marker = L.marker([{{ $latitude }}, {{ $longitude }}], {draggable: true}).addTo(this.map);

                                    this.marker.on('dragend', (e) => {
                                        let position = this.marker.getLatLng();
                                        @this.set('latitude', position.lat);
                                        @this.set('longitude', position.lng);
                                        this.updateAddress(position.lat, position.lng);
                                    });

                                    this.map.on('click', (e) => {
                                        this.marker.setLatLng(e.latlng);
                                        @this.set('latitude', e.latlng.lat);
                                        @this.set('longitude', e.latlng.lng);
                                        this.updateAddress(e.latlng.lat, e.latlng.lng);
                                    });
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
                            },
                            updateAddress(lat, lng) {
                                let addresses = [
                                    'Brgy. 10, Sorsogon City',
                                    'Zone 4, Goa Market Area',
                                    'Pandan Village, Bicol',
                                    'San Jose St., Sorsogon City',
                                    'Goa Town Plaza, Bicol',
                                    'Brgy. Bibincahan, Sorsogon City'
                                ];
                                let randomAddress = addresses[Math.floor(Math.random() * addresses.length)];
                                @this.set('address', randomAddress);
                            },
                            useCurrentLocation() {
                                if (navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition((position) => {
                                        let lat = position.coords.latitude;
                                        let lng = position.coords.longitude;
                                        if (this.map) {
                                            this.map.setView([lat, lng], 15);
                                        }
                                        if (this.marker) {
                                            this.marker.setLatLng([lat, lng]);
                                        }
                                        @this.set('latitude', lat);
                                        @this.set('longitude', lng);
                                        this.updateAddress(lat, lng);
                                    });
                                }
                            }
                        }"
                        class="space-y-4"
                    >
                        <div id="map" class="h-64 rounded-2xl border border-neutral-250 shadow-sm z-0"></div>
                        <div class="flex items-center justify-between">
                            <button type="button" x-on:click="useCurrentLocation" class="text-xs font-semibold bg-neutral-100 dark:bg-zinc-800 text-neutral-700 dark:text-neutral-200 px-3 py-1.5 rounded-lg border border-neutral-350 dark:border-zinc-700 hover:bg-neutral-200 dark:hover:bg-zinc-750 transition-colors cursor-pointer">
                                Use My Current Location
                            </button>
                        </div>
                    </div>

                    <!-- Verified Address -->
                    <div class="space-y-2">
                        <flux:label>{{ __('Verified Address') }}</flux:label>
                        <div class="bg-neutral-50 dark:bg-zinc-800 px-4 py-3 rounded-xl border border-neutral-200 dark:border-zinc-700 text-neutral-800 dark:text-neutral-200 text-sm font-semibold">
                            {{ $address }}
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Report Details and Contact Confirmations -->
            <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-neutral-200 dark:border-zinc-800 shadow-sm space-y-6">
                <!-- Progress Header -->
                <div class="text-xs font-bold text-neutral-450 uppercase tracking-widest border-b border-neutral-150 dark:border-zinc-800 pb-3">
                    3. Details
                </div>

                <!-- Step 3. Description & Status -->
                <div class="space-y-4">
                    <h3 class="text-md font-bold text-neutral-900 dark:text-white">Step 3. Description & Status</h3>
                    
                    <div class="space-y-2">
                        <flux:label>{{ __('My Status') }}</flux:label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 bg-neutral-50 dark:bg-zinc-850 px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-zinc-755 text-sm font-semibold cursor-pointer">
                                <input type="radio" wire:model="status" value="Safe" class="text-[#801818] focus:ring-[#801818]" />
                                <span class="text-emerald-600">Safe</span>
                            </label>
                            <label class="flex items-center gap-2 bg-neutral-50 dark:bg-zinc-850 px-4 py-2.5 rounded-xl border border-[#801818]/30 text-sm font-semibold cursor-pointer">
                                <input type="radio" wire:model="status" value="In Danger" class="text-[#801818] focus:ring-[#801818]" />
                                <span class="text-[#801818]">In Danger</span>
                            </label>
                        </div>
                    </div>

                    <flux:textarea
                        wire:model="description"
                        :label="__('Describe the Situation (e.g., rising water, fire size)')"
                        placeholder="Provide details to assist the dispatch team..."
                        rows="3"
                        required
                        class="bg-neutral-100 !border-none !shadow-none rounded-xl"
                    />
                </div>

                <!-- Step 4. Add Photos/Videos -->
                <div class="space-y-3">
                    <h3 class="text-md font-bold text-neutral-900 dark:text-white">Step 4. Add Photos/Videos</h3>
                    
                    <div class="flex items-center gap-4 bg-neutral-50 dark:bg-zinc-800 p-4 rounded-xl border border-dashed border-neutral-250 dark:border-zinc-700">
                        <input type="file" wire:model="photo" id="photo-upload" class="hidden" accept="image/*" />
                        <label for="photo-upload" class="bg-neutral-200 dark:bg-zinc-700 hover:bg-neutral-300 dark:hover:bg-zinc-600 text-neutral-800 dark:text-neutral-200 text-xs font-bold px-4 py-2 rounded-lg border border-neutral-300 dark:border-zinc-650 cursor-pointer transition-colors">
                            Upload File
                        </label>
                        <span class="text-xs text-neutral-500 font-medium">
                            @if($photo)
                                {{ $photo->getClientOriginalName() }} ({{ round($photo->getSize() / 1024) }} KB)
                            @else
                                Attach supporting visual evidence (Optional)
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Step 5. Confirm Contacts -->
                <div class="space-y-4">
                    <h3 class="text-md font-bold text-neutral-900 dark:text-white">Step 5. Confirm Contacts</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                        <flux:input
                            wire:model="reporterName"
                            :label="__('Reporter')"
                            required
                            class="bg-neutral-100 border-none rounded-xl"
                        />
                        <flux:input
                            wire:model="reporterPhone"
                            :label="__('Phone')"
                            required
                            class="bg-neutral-100 border-none rounded-xl"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-neutral-150 dark:border-zinc-800">
                    <flux:button type="submit" class="w-full !bg-[#801818] hover:!bg-[#601010] !text-white font-extrabold rounded-xl py-3.5 shadow-lg text-base">
                        Submit Report
                    </flux:button>
                </div>

            </div>

        </form>
    </div>
</div>