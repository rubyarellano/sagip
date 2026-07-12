<?php
use App\Models\Incident;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use Carbon\Carbon;
use App\Enums\IncidentStatus;
?>

<div>
    <div class="space-y-6 max-w-7xl mx-auto px-4 py-6">
        
        <!-- Header -->
        <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                My Reports History
            </h1>
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mt-1">
                View and track all incident reports you have submitted to dispatch
            </p>
        </div>

        @php
            $query = \App\Models\Incident::where('user_id', auth()->id());

            if (!empty($search)) {
                $query->where(function($q) {
                    $q->where('address', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            }

            if (!empty($category)) {
                $query->where('category', $category);
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            $totalRecords = $query->count();
            $incidents = $query->orderBy('created_at', 'desc')
                               ->skip(($page - 1) * $perPage)
                               ->take($perPage)
                               ->get();
            $totalPages = max(1, ceil($totalRecords / $perPage));
        @endphp

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <flux:input 
                wire:model.live="search" 
                icon="magnifying-glass" 
                placeholder="Search my reports (location, details...)" 
            />

            <flux:select wire:model.live="category">
                <flux:select.option value="">All Categories</flux:select.option>
                <flux:select.option value="Fire">Fire</flux:select.option>
                <flux:select.option value="Flood">Flood</flux:select.option>
                <flux:select.option value="Medical">Medical</flux:select.option>
                <flux:select.option value="Landslide">Landslide</flux:select.option>
                <flux:select.option value="Earthquake">Earthquake</flux:select.option>
                <flux:select.option value="Other">Other</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="status">
                <flux:select.option value="">All Statuses</flux:select.option>
                <flux:select.option value="Dispatched">Dispatched</flux:select.option>
                <flux:select.option value="Processing">Processing</flux:select.option>
                <flux:select.option value="In Progress">In Progress</flux:select.option>
                <flux:select.option value="Resolved">Resolved</flux:select.option>
                <flux:select.option value="Dismissed">Dismissed</flux:select.option>
            </flux:select>
        </div>

        <!-- Alert History Records Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800 text-xs font-bold text-zinc-400 uppercase">
                            <th class="py-3 px-4">Report ID</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Time & Date</th>
                            <th class="py-3 px-4">Location</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-sm">
                        @forelse($incidents as $incident)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-850/40 transition-colors duration-150">
                                <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-150">
                                    {{ $incident->code }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        @if($incident->severity === 'High')
                                            <span class="w-2 h-2 rounded-full bg-red-600" title="High Severity"></span>
                                        @elseif($incident->severity === 'Medium')
                                            <span class="w-2 h-2 rounded-full bg-amber-500" title="Medium Severity"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-emerald-500" title="Low Severity"></span>
                                        @endif
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $incident->category }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400 font-medium">
                                    {{ $incident->created_at->format('M d, Y - H:i') }}
                                </td>
                                <td class="py-3 px-4 text-zinc-700 dark:text-zinc-300 font-medium truncate max-w-[280px]" title="{{ $incident->address }}">
                                    {{ $incident->address }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($incident->status === 'Dispatched')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-650 text-white shadow-sm">
                                            Dispatched
                                        </span>
                                    @elseif($incident->status === 'Processing')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white shadow-sm">
                                            Processing
                                        </span>
                                    @elseif($incident->status === 'In Progress')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-600 text-white shadow-sm">
                                            In Progress
                                        </span>
                                    @elseif($incident->status === 'Resolved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-850 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            Resolved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-neutral-250 text-neutral-600">
                                            Dismissed
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <flux:button size="sm" wire:click="viewIncident({{ $incident->id }})" class="text-xs font-bold hover:!bg-[#801818] hover:!text-white cursor-pointer">
                                        View Details
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-zinc-500 font-medium">
                                    No reports found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination Footer -->
            <div class="flex items-center justify-between border-t border-zinc-150 dark:border-zinc-800 pt-6 mt-6">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    Showing Page <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $page }}</span> of <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $totalPages }}</span> ({{ $totalRecords }} total reports)
                </span>
                
                <div class="flex gap-2">
                    <flux:button 
                        size="sm" 
                        wire:click="prevPage" 
                        :disabled="$page <= 1"
                        class="text-xs font-semibold cursor-pointer"
                    >
                        Previous
                    </flux:button>
                    <flux:button 
                        size="sm" 
                        wire:click="nextPage({{ $totalRecords }})" 
                        :disabled="$page >= $totalPages"
                        class="text-xs font-semibold cursor-pointer"
                    >
                        Next
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Custom Details Modal -->
        <flux:modal name="citizen-history-details-modal" class="md:w-[650px] p-6 rounded-3xl bg-neutral-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 max-h-[90vh] overflow-y-auto">
            @if($selectedIncident)
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-extrabold text-neutral-900 dark:text-white">{{ $selectedIncident->code }}</span>
                            <span class="text-sm font-semibold text-zinc-500 dark:text-zinc-450">[{{ $selectedIncident->category }}]</span>
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

                    <!-- Details Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block mb-1">Reporter Details</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 space-y-1.5">
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Name:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $selectedIncident->reporter_name ?: 'Anonymous' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Contact:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $selectedIncident->reporter_phone ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block mb-1">Situation Description</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 min-h-[60px] text-zinc-700 dark:text-zinc-300 font-medium">
                                    {{ $selectedIncident->description ?: 'No description provided.' }}
                                </div>
                            </div>
                        </div>

                        <!-- Right Location and Media details -->
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block mb-1">Geographic Location</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 text-sm space-y-1">
                                    <div class="font-bold text-zinc-850 dark:text-zinc-200">{{ $selectedIncident->address }}</div>
                                    <div class="text-xs text-zinc-500">Coordinates: {{ $selectedIncident->latitude }}, {{ $selectedIncident->longitude }}</div>
                                </div>
                            </div>

                            @if($selectedIncident->photo_path)
                                <div>
                                    <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block mb-1">Attached Photo</span>
                                    <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
                                        <img src="{{ asset('storage/' . $selectedIncident->photo_path) }}" class="w-full max-h-48 object-cover" alt="Incident Photo">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Dispatcher/Response Status -->
                    <div class="bg-zinc-100 dark:bg-zinc-850 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 space-y-2">
                        <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Current Dispatch Status</span>
                        <div class="text-sm font-bold">
                            @if($selectedIncident->status === 'Dispatched')
                                <span class="text-red-650">Responders Dispatched</span>
                            @elseif($selectedIncident->status === 'Processing')
                                <span class="text-amber-500">Processing Alert</span>
                            @elseif($selectedIncident->status === 'In Progress')
                                <span class="text-blue-600">Action In Progress</span>
                            @elseif($selectedIncident->status === 'Resolved')
                                <span class="text-emerald-600">Resolved / Safe</span>
                            @else
                                <span class="text-zinc-500">Dismissed</span>
                            @endif
                        </div>
                    </div>

                    <!-- Close Action -->
                    <div class="flex justify-end pt-2 border-t border-zinc-200 dark:border-zinc-800">
                        <flux:button variant="ghost" x-on:click="Flux.modal('citizen-history-details-modal').close()" class="rounded-xl px-6 cursor-pointer">
                            Close details
                        </flux:button>
                    </div>
                </div>
            @endif
        </flux:modal>

    </div>
</div>