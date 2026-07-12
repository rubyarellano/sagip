<?php
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;
?>

<div>
    <div class="space-y-6 max-w-7xl mx-auto px-4 py-6">
        
        <!-- Header -->
        <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                Registered Citizens Directory
            </h1>
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mt-1">
                View medical risk summaries, pair IDs, and manage safety profiles of registered citizens
            </p>
        </div>

        @php
            // Query building for citizens
            $query = \App\Models\User::where('role', UserRole::Citizen->value);

            if (!empty($search)) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('device_id', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%')
                      ->orWhere('id', 'like', '%' . $this->search . '%');
                });
            }

            if (!empty($locationFilter)) {
                $query->where('address', 'like', '%' . $this->locationFilter . '%');
            }

            $totalRecords = $query->count();
            $users = $query->orderBy('created_at', 'desc')
                           ->skip(($page - 1) * $perPage)
                           ->take($perPage)
                           ->get();
            $totalPages = max(1, ceil($totalRecords / $perPage));
        @endphp

        <!-- Search / Filter Area -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="md:col-span-2">
                <flux:input 
                    wire:model.live="search" 
                    icon="magnifying-glass" 
                    placeholder="Search citizens by name, contact, device ID..." 
                />
            </div>
            
            <flux:select wire:model.live="locationFilter">
                <flux:select.option value="">All Locations</flux:select.option>
                <flux:select.option value="Brgy. 10">Brgy. 10</flux:select.option>
                <flux:select.option value="Goa">Goa</flux:select.option>
                <flux:select.option value="Pandan">Pandan</flux:select.option>
                <flux:select.option value="San Jose">San Jose</flux:select.option>
                <flux:select.option value="Tulatula">Tulatula</flux:select.option>
            </flux:select>
        </div>

        <!-- Citizens List Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800 text-xs font-bold text-zinc-400 uppercase">
                            <th class="py-3 px-4">User ID</th>
                            <th class="py-3 px-4">Full Name</th>
                            <th class="py-3 px-4">Watch ID</th>
                            <th class="py-3 px-4">Contact Number</th>
                            <th class="py-3 px-4">Address</th>
                            <th class="py-3 px-4">Emergency Contact</th>
                            <th class="py-3 px-4">Last Active</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-sm">
                        @forelse($users as $user)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-850/40 transition-colors duration-150">
                                <td class="py-3 px-4 font-bold text-zinc-500">
                                    #USR-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $user->name }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($user->device_id)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-green-150 text-green-700 dark:bg-green-950/40 dark:text-green-400 border border-green-200 dark:border-green-800">
                                            {{ $user->device_id }}
                                        </span>
                                    @else
                                        <span class="text-xs text-zinc-400">None paired</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-zinc-650 dark:text-zinc-400">
                                    {{ $user->phone ?: 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-zinc-650 dark:text-zinc-400 font-medium truncate max-w-[150px]" title="{{ $user->address }}">
                                    {{ $user->address ?: 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-zinc-650 dark:text-zinc-400 font-medium">
                                    @php
                                        $primaryContact = $user->emergencyContacts->first();
                                    @endphp
                                    @if($primaryContact)
                                        <div class="truncate max-w-[150px]" title="{{ $primaryContact->name }} ({{ $primaryContact->phone }})">
                                            {{ $primaryContact->name }}
                                            <span class="text-xs text-zinc-400 block">{{ $primaryContact->phone }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">None registered</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-zinc-550 dark:text-zinc-450 text-xs font-medium">
                                    {{ $user->updated_at->format('M d, Y H:i') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <flux:button 
                                            size="sm" 
                                            variant="ghost" 
                                            icon="eye" 
                                            wire:click="viewUser({{ $user->id }})" 
                                            title="View Medical File"
                                            class="hover:text-neutral-900 cursor-pointer"
                                        />
                                        <flux:button 
                                            size="sm" 
                                            variant="ghost" 
                                            icon="pencil" 
                                            wire:click="editUser({{ $user->id }})" 
                                            title="Edit Profile"
                                            class="hover:text-blue-600 cursor-pointer"
                                        />
                                        <flux:button 
                                            size="sm" 
                                            variant="ghost" 
                                            icon="trash" 
                                            wire:click="confirmDeleteUser({{ $user->id }})" 
                                            title="Delete Citizen Record"
                                            class="hover:text-red-600 cursor-pointer"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-zinc-500 font-medium">
                                    No registered citizens found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="flex items-center justify-between border-t border-zinc-150 dark:border-zinc-800 pt-6 mt-6">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    Showing Page <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $page }}</span> of <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $totalPages }}</span> ({{ $totalRecords }} total citizens)
                </span>
                
                <div class="flex gap-2">
                    <flux:button 
                        size="sm" 
                        wire:click="prevPage" 
                        :disabled="$page <= 1"
                        class="text-xs font-semibold cursor-pointer"
                    >
                        Prev
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

        <!-- High-Fidelity Custom Citizen Medical File Modal -->
        <flux:modal name="view-user-modal" class="md:w-[600px] p-6 rounded-3xl bg-neutral-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
            @if($selectedUser)
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-extrabold text-neutral-900 dark:text-white">{{ $selectedUser->name }}</span>
                            <span class="text-xs font-bold text-zinc-500">#USR-{{ str_pad($selectedUser->id, 3, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        @if($selectedUser->device_id)
                            <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-green-100 text-green-700 border border-green-200">Watch Active: {{ $selectedUser->device_id }}</span>
                        @endif
                    </div>

                    <!-- Medical File Content Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <!-- Personal Info -->
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Contact Details</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 space-y-1.5 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Phone:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $selectedUser->phone ?: 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Email:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $selectedUser->email }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Birthday:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $selectedUser->birthday ? \Carbon\Carbon::parse($selectedUser->birthday)->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500">Gender:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $selectedUser->gender ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Residence Address</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 text-sm text-zinc-800 dark:text-zinc-200 font-semibold">
                                    {{ $selectedUser->address ?: 'No address listed.' }}
                                </div>
                            </div>
                        </div>

                        <!-- Medical File -->
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Medical Indicators</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 grid grid-cols-3 gap-2 text-center text-sm">
                                    <div class="bg-red-50 dark:bg-red-950/20 p-2 rounded-lg">
                                        <span class="text-[10px] text-zinc-400 block font-semibold">BLOOD</span>
                                        <span class="font-extrabold text-[#801818] dark:text-red-400 text-base">{{ $selectedUser->blood_type ?: 'N/A' }}</span>
                                    </div>
                                    <div class="bg-zinc-50 dark:bg-zinc-800 p-2 rounded-lg">
                                        <span class="text-[10px] text-zinc-400 block font-semibold">HEIGHT</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200 text-xs">{{ $selectedUser->height ?: 'N/A' }}</span>
                                    </div>
                                    <div class="bg-zinc-50 dark:bg-zinc-800 p-2 rounded-lg">
                                        <span class="text-[10px] text-zinc-400 block font-semibold">WEIGHT</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200 text-xs">{{ $selectedUser->weight ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-1">Conditions & Allergies</span>
                                <div class="bg-white dark:bg-zinc-850 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 text-sm text-zinc-700 dark:text-zinc-300 min-h-[80px]">
                                    {{ $selectedUser->allergies ?: 'No medical conditions or allergies documented.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom emergency contacts list -->
                    <div>
                        <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider block mb-2">Registered Emergency Contacts</span>
                        <div class="space-y-2">
                            @forelse($selectedUser->emergencyContacts as $ec)
                                <div class="flex justify-between items-center bg-white dark:bg-zinc-850 px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-sm">
                                    <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $ec->name }} <span class="text-xs text-zinc-500 font-normal">({{ $ec->relation }})</span></span>
                                    <span class="text-zinc-650 dark:text-zinc-400 font-semibold">{{ $ec->phone }}</span>
                                </div>
                            @empty
                                <div class="p-3 text-center text-xs font-medium text-zinc-500 bg-white dark:bg-zinc-850 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-850">
                                    No custom contacts registered by user.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end pt-2 border-t border-zinc-200 dark:border-zinc-800 gap-3">
                        <flux:button variant="ghost" x-on:click="Flux.modal('view-user-modal').close()" class="rounded-xl px-6 cursor-pointer">
                            Close File
                        </flux:button>
                        <flux:button size="sm" wire:click="editUser({{ $selectedUser->id }})" class="!bg-[#801818] hover:!bg-[#601010] !text-white font-bold rounded-xl px-6">
                            Edit Profile
                        </flux:button>
                    </div>
                </div>
            @endif
        </flux:modal>

        <!-- High-Fidelity Custom Edit User Modal -->
        <flux:modal name="edit-user-modal" class="md:w-[600px] p-6 rounded-3xl bg-neutral-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
            @if($editingUser)
                <form wire:submit="updateUser" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                        <span class="text-lg font-extrabold text-neutral-900 dark:text-white">Edit Citizen Profile</span>
                        <span class="text-xs font-bold text-zinc-500">#USR-{{ str_pad($editingUser->id, 3, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                        <flux:input wire:model="editFirstName" :label="__('First Name')" required />
                        <flux:input wire:model="editLastName" :label="__('Last Name')" required />
                        <flux:input wire:model="editPhone" :label="__('Phone Number')" />
                        <flux:input wire:model="editAddress" :label="__('Address')" />
                        
                        <flux:select wire:model="editBloodType" :label="__('Blood Type')">
                            <flux:select.option value="">Select Blood Type</flux:select.option>
                            <flux:select.option value="A+">A+</flux:select.option>
                            <flux:select.option value="A-">A-</flux:select.option>
                            <flux:select.option value="B+">B+</flux:select.option>
                            <flux:select.option value="B-">B-</flux:select.option>
                            <flux:select.option value="AB+">AB+</flux:select.option>
                            <flux:select.option value="AB-">AB-</flux:select.option>
                            <flux:select.option value="O+">O+</flux:select.option>
                            <flux:select.option value="O-">O-</flux:select.option>
                        </flux:select>
                        
                        <div class="grid grid-cols-2 gap-2 items-start">
                            <flux:input wire:model="editHeight" :label="__('Height')" placeholder="5' 4&quot;" />
                            <flux:input wire:model="editWeight" :label="__('Weight')" placeholder="120 lbs" />
                        </div>
                    </div>

                    <flux:textarea wire:model="editAllergies" :label="__('Conditions & Allergies')" rows="3" />

                    <div class="flex justify-end pt-2 border-t border-zinc-200 dark:border-zinc-800 gap-3">
                        <flux:button variant="ghost" x-on:click="Flux.modal('edit-user-modal').close()" class="rounded-xl px-6 cursor-pointer">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" class="!bg-[#801818] hover:!bg-[#601010] !text-white font-bold rounded-xl px-6">
                            Save Changes
                        </flux:button>
                    </div>
                </form>
            @endif
        </flux:modal>

        <!-- High-Fidelity Custom Deletion Confirmation Modal -->
        <flux:modal name="delete-confirm-modal" class="md:w-[450px] p-6 rounded-3xl bg-neutral-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
            <div class="space-y-6 text-center flex flex-col items-center">
                <div class="h-14 w-14 rounded-full bg-red-100 dark:bg-red-950/40 text-[#801818] flex items-center justify-center">
                    <flux:icon.exclamation-triangle class="h-7 w-7" />
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-xl font-extrabold text-neutral-900 dark:text-white">Delete Citizen Record?</h3>
                    <p class="text-sm text-neutral-500 font-medium">This will permanently remove the citizen safety file and pair history from the system. This action is tracked by audit logs.</p>
                </div>

                <div class="flex w-full gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                    <flux:button variant="ghost" x-on:click="Flux.modal('delete-confirm-modal').close()" class="grow rounded-xl py-2.5 font-semibold text-neutral-550 border border-neutral-250 cursor-pointer">
                        Cancel
                    </flux:button>
                    <flux:button wire:click="deleteUser" class="grow !bg-[#801818] hover:!bg-[#601010] !text-white font-semibold rounded-xl py-2.5 shadow-md cursor-pointer">
                        Confirm Delete
                    </flux:button>
                </div>
            </div>
        </flux:modal>

    </div>
</div>