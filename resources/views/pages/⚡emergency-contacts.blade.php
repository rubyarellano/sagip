<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\EmergencyContact;
use Flux\Flux;

new #[Title('Emergency Contacts')] class extends Component {
    public string $name = '';
    public string $relation = '';
    public string $phone = '';

    public function mount(): void
    {
        if (Auth::user()->isAdmin()) {
            $this->redirect(route('admin.dashboard'), navigate: true);
            return;
        }
    }

    public function saveContact()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'relation' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
        ]);

        EmergencyContact::create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'relation' => $this->relation,
            'phone' => $this->phone,
        ]);

        $this->name = '';
        $this->relation = '';
        $this->phone = '';

        Flux::toast(variant: 'success', text: __('Emergency contact added successfully!'));
    }

    public function deleteContact(int $id): void
    {
        $contact = EmergencyContact::where('user_id', Auth::id())->findOrFail($id);
        $contact->delete();

        Flux::toast(variant: 'success', text: __('Emergency contact removed successfully!'));
    }
}; ?>

<div>
    <div class="space-y-8 max-w-7xl mx-auto px-4 py-6">
        
        <div class="flex items-center justify-between border-b border-neutral-200 dark:border-zinc-800 pb-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                Update Emergency Contacts
            </h1>
            <span class="text-sm font-semibold text-neutral-500">SAGIP Dispatch Directory</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            
            <!-- LEFT COLUMN: Responders & Custom Contacts -->
            <div class="space-y-6">
                <!-- Core Responders -->
                <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-neutral-200 dark:border-zinc-800 shadow-sm space-y-4">
                    <div class="border-b border-neutral-150 dark:border-zinc-850 pb-3">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Core Responders</h3>
                        <p class="text-xs text-neutral-500 font-medium mt-1">Local Authorities (Pre-filled)</p>
                    </div>

                    <div class="space-y-3">
                        <!-- Responder Items -->
                        @foreach([
                            ['Ligao City CDRRMO', '+63 919 078 0730', 'phone'],
                            ['Police (911)', '911', 'map-pin'],
                            ['Bureau of Fire Protection (BFP)', '+63 936 547 4962', 'fire'],
                            ['Philippine National Police (PNP)', '+63 998 598 5928', 'shield-check']
                        ] as $responder)
                            <div class="flex items-center justify-between bg-neutral-50 dark:bg-zinc-800 px-4 py-3 rounded-xl border border-neutral-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">
                                        @if($responder[2] === 'phone')
                                            📞
                                        @elseif($responder[2] === 'map-pin')
                                            📍
                                        @elseif($responder[2] === 'fire')
                                            🚒
                                        @else
                                            👮
                                        @endif
                                    </span>
                                    <span class="font-bold text-sm text-neutral-800 dark:text-neutral-200">{{ $responder[0] }}</span>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-neutral-500">{{ $responder[1] }}</span>
                                    <div class="text-neutral-400">
                                        <flux:icon.lock-closed class="h-4 w-4" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Current Emergency Contacts Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-neutral-200 dark:border-zinc-800 shadow-sm space-y-4">
                    <div class="border-b border-neutral-150 dark:border-zinc-850 pb-3">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Current Emergency Contacts</h3>
                        <p class="text-xs text-neutral-500 font-medium mt-1">Your registered emergency contacts</p>
                    </div>

                    <div class="space-y-3">
                        @forelse (auth()->user()->emergencyContacts as $contact)
                            <div class="flex items-center justify-between bg-neutral-50 dark:bg-zinc-800 px-4 py-3 rounded-xl border border-neutral-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">👤</span>
                                    <div>
                                        <div class="font-bold text-sm text-neutral-800 dark:text-neutral-200">
                                            {{ $contact->name }}
                                        </div>
                                        <div class="text-xs text-neutral-500 font-medium">
                                            {{ $contact->relation }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <span class="text-xs font-semibold text-neutral-500">{{ $contact->phone }}</span>
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        icon:variant="outline"
                                        wire:click="deleteContact({{ $contact->id }})"
                                        class="text-red-500 hover:text-red-655 hover:bg-red-50 dark:hover:bg-red-955/50"
                                    />
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No custom emergency contacts added yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Add Emergency Contact Form -->
            <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-neutral-200 dark:border-zinc-800 shadow-sm space-y-4">
                <div class="border-b border-neutral-150 dark:border-zinc-850 pb-3">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Add Emergency Contact</h3>
                    <p class="text-xs text-neutral-500 font-medium mt-1">Include family, friends, or caregivers</p>
                </div>

                <form wire:submit="saveContact" class="space-y-4">
                    <flux:input
                        wire:model="name"
                        :label="__('Name')"
                        placeholder="Angela Bautista"
                        required
                        class="bg-neutral-100 border-none rounded-xl"
                    />

                    <flux:input
                        wire:model="relation"
                        :label="__('Relation Input')"
                        placeholder="Sister"
                        required
                        class="bg-neutral-100 border-none rounded-xl"
                    />

                    <flux:input
                        wire:model="phone"
                        :label="__('Phone Input')"
                        placeholder="+63 9xxx..."
                        required
                        class="bg-neutral-100 border-none rounded-xl"
                    />

                    <div class="flex items-center justify-between gap-4 pt-4 border-t border-neutral-150 dark:border-zinc-800">
                        <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-semibold text-red-700 hover:text-red-900 cursor-pointer">
                            Cancel
                        </a>
                        <flux:button type="submit" class="!bg-[#801818] hover:!bg-[#601010] !text-white font-semibold rounded-xl px-6 py-2.5 shadow-md">
                            Save Contact
                        </flux:button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
