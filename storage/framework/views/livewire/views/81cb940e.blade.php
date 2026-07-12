<?php
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
?>

<div>
    <div class="space-y-8 max-w-7xl mx-auto px-4 py-2">

        <!-- Quick Action Cards Section -->
        <div class="space-y-4">
            <h3 class="text-xl font-bold text-neutral-800 dark:text-neutral-200">Quick Action</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Report Incident (Red) -->
                <a href="{{ route('report-incident') }}" wire:navigate class="group relative overflow-hidden rounded-[1.25rem] bg-[#c00000] p-6 text-white shadow-md transition-[transform,box-shadow] duration-200 hover:scale-[1.02] hover:shadow-lg active:scale-[0.98]">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white text-[#c00000] shadow-xs">
                            <flux:icon.exclamation-triangle class="h-6 w-6" />
                        </div>
                        <div>
                            <h4 class="text-lg font-extrabold leading-tight">Report an Incident</h4>
                        </div>
                    </div>
                </a>

                <!-- Register Watch (Blue) -->
                <button type="button" wire:click="openRegisterWatch" class="group text-left relative overflow-hidden rounded-[1.25rem] bg-[#4f86d6] p-6 text-white shadow-md transition-[transform,box-shadow] duration-200 hover:scale-[1.02] hover:shadow-lg active:scale-[0.98] cursor-pointer w-full">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white text-[#4f86d6] shadow-xs">
                            <flux:icon.device-phone-mobile class="h-6 w-6" />
                        </div>
                        <div>
                            <h4 class="text-lg font-extrabold leading-tight">Register your Watch</h4>
                        </div>
                    </div>
                </button>

                <!-- Update Contacts (Yellow) -->
                <a href="{{ route('emergency-contacts') }}" wire:navigate class="group relative overflow-hidden rounded-[1.25rem] bg-[#ffd200] p-6 text-zinc-900 shadow-md transition-[transform,box-shadow] duration-200 hover:scale-[1.02] hover:shadow-lg active:scale-[0.98]">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white text-[#ffd200] shadow-xs">
                            <flux:icon.users class="h-6 w-6" />
                        </div>
                        <div>
                            <h4 class="text-lg font-extrabold leading-tight">Update Emergency Contacts</h4>
                        </div>
                    </div>
                </a>

            </div>
        </div>

        <!-- My Safety Plan Section -->
        <div class="bg-white dark:bg-zinc-900 rounded-[1.5rem] p-8 border border-neutral-200 dark:border-neutral-800 shadow-sm">
            <h3 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6 border-b border-neutral-150 dark:border-neutral-800 pb-4">
                My Safety Plan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                
                <!-- Left: My Profile Summary -->
                <div class="space-y-6 pr-0 md:pr-8 md:border-r border-neutral-200 dark:border-neutral-800">
                    <h4 class="text-base font-bold text-neutral-800 dark:text-neutral-200">My Profile</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm font-semibold text-neutral-600 dark:text-neutral-400 block mb-1">Primary Residence:</span>
                            <div class="bg-zinc-200/60 dark:bg-zinc-800 px-4 py-2.5 rounded-xl text-neutral-850 dark:text-neutral-200 text-sm min-h-[40px] flex items-center">
                                {{ auth()->user()->address ?: 'No address specified.' }}
                            </div>
                        </div>

                        <div>
                            <span class="text-sm font-semibold text-neutral-600 dark:text-neutral-400 block mb-1">Personal Risks:</span>
                            <div class="bg-zinc-200/60 dark:bg-zinc-800 px-4 py-2.5 rounded-xl text-neutral-850 dark:text-neutral-200 text-sm min-h-[40px] flex items-center">
                                {{ auth()->user()->allergies ?: 'No medical risks listed.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Emergency Contacts -->
                <div class="space-y-6">
                    <h4 class="text-base font-bold text-neutral-800 dark:text-neutral-200">Emergency Contacts</h4>
                    
                    <div class="space-y-3">
                        @forelse(auth()->user()->emergencyContacts as $contact)
                            <div class="bg-zinc-200/60 dark:bg-zinc-800 px-4 py-2.5 rounded-xl text-neutral-850 dark:text-neutral-200 text-sm">
                                {{ $contact->relation }}: {{ $contact->name }} [{{ $contact->phone }}]
                            </div>
                        @empty
                            <div class="text-sm text-neutral-500 bg-zinc-200/40 dark:bg-zinc-800 p-4 rounded-xl text-center border border-dashed border-neutral-200 dark:border-neutral-800">
                                No custom emergency contacts added.
                            </div>
                        @endforelse

                        <!-- Pre-filled default local responder -->
                        <div class="bg-zinc-200/60 dark:bg-zinc-800 px-4 py-2.5 rounded-xl text-neutral-850 dark:text-neutral-200 text-sm">
                            Local Authorities: Bicol CDRRMO [+63 919 078 0730]
                        </div>
                        
                        <div class="bg-zinc-200/60 dark:bg-zinc-800 px-4 py-2.5 rounded-xl text-neutral-850 dark:text-neutral-200 text-sm">
                            Police (911)
                        </div>

                        <div class="bg-zinc-200/60 dark:bg-zinc-800 px-4 py-2.5 rounded-xl text-neutral-850 dark:text-neutral-200 text-sm">
                            Police (911)
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- High-Fidelity Custom Watch Registration Modal -->
    <flux:modal name="register-watch-modal" class="md:w-[650px] bg-neutral-50 p-6 rounded-3xl border border-neutral-200">
        <div class="space-y-6">
            
            <!-- Modal Header -->
            <div class="flex flex-col items-center text-center space-y-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#801818]/15 text-[#801818]">
                    <flux:icon.bell class="h-7 w-7" />
                </div>
                <div class="space-y-1">
                    <h2 class="text-2xl font-extrabold text-neutral-900">Register Your Smart ID Watch</h2>
                    <p class="text-sm text-neutral-500 font-medium">Claim your watch in 2 easy steps:</p>
                </div>
            </div>

            @if($step === 1)
                <!-- STEP 1: ID MAPPING -->
                <div class="bg-white p-6 rounded-2xl border border-neutral-200 shadow-sm space-y-4">
                    <h3 class="text-lg font-bold text-neutral-900 border-b border-neutral-150 pb-2">Step 1: ID Mapping</h3>
                    
                    <div class="space-y-2">
                        <flux:label>{{ __('Unique Device ID') }}</flux:label>
                        <div class="relative flex items-center">
                            <input type="text" wire:model="deviceId" placeholder="#01004" class="w-full bg-neutral-100 rounded-xl px-4 py-3 text-neutral-800 font-semibold border-none focus:ring-2 focus:ring-[#801818] outline-none text-sm" />
                            @if(!empty($deviceId))
                                <div class="absolute right-3 text-emerald-500">
                                    <flux:icon.check-circle class="h-6 w-6" />
                                </div>
                            @endif
                        </div>
                        @error('deviceId')
                            <flux:error class="mt-1">{{ $message }}</flux:error>
                        @enderror
                        <p class="text-xs text-neutral-450 font-medium">(based on the device's MAC)</p>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-between gap-4 mt-6">
                    <flux:button variant="ghost" type="button" x-on:click="Flux.modal('register-watch-modal').close()" class="rounded-xl px-6 py-2.5 font-semibold text-neutral-550 border border-neutral-250 cursor-pointer">
                        Cancel
                    </flux:button>
                    <flux:button type="button" wire:click="verifyDevice" class="!bg-[#801818] hover:!bg-[#601010] !text-white font-semibold rounded-xl px-6 py-2.5 shadow-md cursor-pointer">
                        Next
                    </flux:button>
                </div>
            @else
                <!-- STEP 2: SCAN & PAIR -->
                <div class="bg-white p-6 rounded-2xl border border-neutral-200 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    
                    <!-- Left: Mapping ID display -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-neutral-900 border-b border-neutral-150 pb-2">Step 1: ID Mapping</h3>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-neutral-450 uppercase block">Unique Device ID</label>
                            <div class="flex items-center gap-2 bg-neutral-50 px-4 py-2.5 rounded-xl border border-neutral-150">
                                <span class="font-bold text-neutral-800">{{ $deviceId }}</span>
                                <div class="text-emerald-500">
                                    <flux:icon.check-circle class="h-5 w-5" />
                                </div>
                            </div>
                            <p class="text-xs text-neutral-450 font-medium">(based on the device's MAC)</p>
                        </div>
                    </div>

                    <!-- Right: Camera QR scanner simulation -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-neutral-900 border-b border-neutral-150 pb-2">Step 2: Scan & Pair</h3>
                        
                        <div class="relative overflow-hidden rounded-xl border border-neutral-250 bg-neutral-900 p-2 text-white">
                            <!-- Mock QR Scanning Casing -->
                            <div class="relative flex flex-col items-center justify-center py-6 bg-neutral-850 rounded-lg">
                                <div class="relative flex h-32 w-32 items-center justify-center border-2 border-emerald-500 rounded-lg p-2 bg-white">
                                    <!-- QR Code SVG representation -->
                                    <svg class="h-full w-full text-neutral-800" viewBox="0 0 100 100" fill="currentColor">
                                        <path d="M5 5h30v30H5V5zm5 5v20h20V10H10zM5 65h30v30H5V65zm5 5v20h20V70H10zM65 5h30v30H65V5zm5 5v20h20V10H70zM65 65h10v10H65V65zm10 10h10v10H75V75zm10-10h10v10H85V65zm-10 20h10v10H75V85zm10 0h10v10H85V85zm-20 10h10v10H65V95zm10 0h10v10H75V95zm10 0h10v10H85V95zm0-20h10v10H85V75z" />
                                    </svg>
                                    <!-- Green scanner border laser -->
                                    <div class="absolute inset-0 border-2 border-emerald-500 animate-pulse"></div>
                                </div>
                                <div class="absolute bottom-2 bg-emerald-500/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                    WATCH ID VERIFIED
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-neutral-500 font-medium text-center">Center the QR Code from the back of your watch.</p>
                    </div>

                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-between gap-4 mt-6">
                    <flux:button variant="ghost" type="button" wire:click="$set('step', 1)" class="rounded-xl px-6 py-2.5 font-semibold text-neutral-550 border border-neutral-250 cursor-pointer">
                        Back
                    </flux:button>
                    
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-250 animate-pulse">
                            {{ $statusText }}
                        </span>
                        <flux:button type="button" wire:click="pairDevice" class="!bg-[#801818] hover:!bg-[#601010] !text-white font-semibold rounded-xl px-6 py-2.5 shadow-md cursor-pointer">
                            CONFIRM AND PAIR DEVICE
                        </flux:button>
                    </div>
                </div>
            @endif

        </div>
    </flux:modal>
</div>