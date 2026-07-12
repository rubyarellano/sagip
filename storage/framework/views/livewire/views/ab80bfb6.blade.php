<?php
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
?>

<div>
    <div class="max-w-7xl mx-auto px-4 py-4 space-y-8">
        
        <!-- Header Page Title -->
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                Citizen Profile: {{ auth()->user()->name }}
            </h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: Personal Details Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-6">Personal Details</h2>
                    
                    <div class="flex flex-col items-center text-center space-y-4">
                        <!-- Avatar with pinkish/red circular border -->
                        <div class="w-24 h-24 rounded-full border-4 border-rose-500/80 p-0.5 overflow-hidden flex items-center justify-center bg-rose-50 shrink-0">
                            <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://api.dicebear.com/7.x/lorelei/svg?seed=' . urlencode(auth()->user()->name) }}" class="w-full h-full object-cover" alt="Avatar">
                        </div>

                        <div class="space-y-1">
                            <h3 class="text-lg font-bold text-neutral-900 dark:text-white leading-tight">
                                {{ auth()->user()->name }}
                            </h3>
                        </div>

                        <!-- Left aligned profile info details -->
                        <div class="w-full border-t border-neutral-100 dark:border-zinc-800 pt-6 text-sm text-neutral-600 dark:text-neutral-400 space-y-3 text-left">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-neutral-800 dark:text-neutral-200">Role:</span>
                                <span class="text-neutral-600 dark:text-neutral-400">Citizen</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-neutral-800 dark:text-neutral-200">Email:</span>
                                <span class="text-neutral-600 dark:text-neutral-400 truncate max-w-[180px] block" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-neutral-800 dark:text-neutral-200">Phone:</span>
                                <span class="text-neutral-600 dark:text-neutral-400">{{ auth()->user()->phone ?: 'Not specified' }}</span>
                            </div>
                            @if(auth()->user()->device_id)
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-neutral-800 dark:text-neutral-200">Band ID:</span>
                                    <span class="text-green-655 dark:text-green-400 font-bold">{{ auth()->user()->device_id }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons aligned at the bottom of the column -->
                <div class="flex flex-col gap-3">
                    <a href="{{ route('dashboard') }}" wire:navigate class="w-full text-center bg-white dark:bg-zinc-800 hover:bg-neutral-50 dark:hover:bg-zinc-700 text-neutral-800 dark:text-neutral-200 font-bold px-6 py-2.5 rounded-xl border border-neutral-300 dark:border-zinc-700 shadow-xs cursor-pointer text-sm block">
                        Go to Dashboard
                    </a>

                    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="w-full">
                        @csrf
                        <button type="submit" class="w-full bg-[#5c1d1d] hover:bg-[#4a1616] text-white font-bold px-6 py-2.5 rounded-xl shadow-xs cursor-pointer text-sm">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Right Column: Personal details, medical, emergency contacts & security settings -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Profile Information Forms (Personal, Medical, Email) in a single form to submit them all -->
                <form wire:submit="updateProfileInformation" class="space-y-6">
                    
                    <!-- Personal Details Card -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                        <flux:heading size="lg">{{ __('Personal Details') }}</flux:heading>
                        <flux:subheading>{{ __('Update your basic details and profile avatar picture') }}</flux:subheading>
                        <flux:separator class="my-4" />

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                                <flux:input wire:model="first_name" :label="__('First Name')" type="text" required />
                                <flux:input wire:model="last_name" :label="__('Last Name')" type="text" required />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                                <flux:input wire:model="name" :label="__('Display Name')" type="text" required />
                                <flux:input wire:model="birthday" :label="__('Date of Birth')" type="date" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                                <flux:select wire:model="gender" :label="__('Gender')">
                                    <flux:select.option value="">Select Gender</flux:select.option>
                                    <flux:select.option value="Male">Male</flux:select.option>
                                    <flux:select.option value="Female">Female</flux:select.option>
                                    <flux:select.option value="Other">Other</flux:select.option>
                                </flux:select>
                                <flux:input wire:model="phone" :label="__('Phone Number')" type="text" />
                            </div>
                            <flux:input wire:model="address" :label="__('Address')" type="text" />

                            <div class="flex items-center gap-4 pt-2">
                                <div class="w-16 h-16 rounded-full border-2 border-rose-500/80 p-0.5 overflow-hidden flex items-center justify-center bg-rose-50 shrink-0">
                                    @if ($avatarFile)
                                        <img src="{{ $avatarFile->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @else
                                        <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://api.dicebear.com/7.x/lorelei/svg?seed=' . urlencode(auth()->user()->name) }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <flux:input type="file" wire:model="avatarFile" :label="__('Profile Avatar')" accept="image/*" />
                                    <flux:error name="avatarFile" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Medical Information Card -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                        <flux:heading size="lg">{{ __('Medical Information') }}</flux:heading>
                        <flux:subheading>{{ __('List medical conditions and alerts for dispatchers to access in emergency') }}</flux:subheading>
                        <flux:separator class="my-4" />

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                <flux:select wire:model="blood_type" :label="__('Blood Type')">
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
                                <flux:input wire:model="height" :label="__('Height')" type="text" placeholder="e.g. 5' 4&quot;" />
                                <flux:input wire:model="weight" :label="__('Weight')" type="text" placeholder="e.g. 120 lbs" />
                            </div>
                            <flux:textarea wire:model="allergies" :label="__('Medical Conditions & Allergies')" placeholder="List any medical conditions, allergies, or drug sensitivities..." rows="3" />
                        </div>
                    </div>
                    
                    <!-- Account Email Card -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                        <flux:heading size="lg">{{ __('Account Email') }}</flux:heading>
                        <flux:subheading>{{ __('Your account login email and verification status') }}</flux:subheading>
                        <flux:separator class="my-4" />

                        <div class="space-y-4">
                            <flux:input wire:model="email" :label="__('Email Address')" type="email" required />
                            @if ($this->hasUnverifiedEmail)
                                <div class="mt-2">
                                    <flux:text class="text-xs">
                                        {{ __('Your email address is unverified.') }}
                                        <flux:link class="cursor-pointer font-semibold text-[#801818] hover:text-[#992222]" wire:click.prevent="resendVerificationNotification">
                                            {{ __('Click here to re-send verification email.') }}
                                        </flux:link>
                                    </flux:text>
                                    @if (session('status') === 'verification-link-sent')
                                        <flux:text class="mt-1 text-xs font-medium text-green-600 dark:text-green-400">
                                            {{ __('A new verification link has been sent.') }}
                                        </flux:text>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end gap-4 border-t border-zinc-200 dark:border-zinc-700 pt-6 mt-6">
                            <flux:button variant="primary" type="submit" class="bg-[#801818] hover:bg-[#992222] text-white border-none px-6">
                                Save Changes and Update Profile
                            </flux:button>
                        </div>
                    </div>
                </form>

                <!-- Emergency Contacts Card -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                    <flux:heading size="lg">{{ __('Emergency Contacts') }}</flux:heading>
                    <flux:subheading>{{ __('Manage emergency contacts to alert in case of emergency dispatch') }}</flux:subheading>
                    <flux:separator class="my-4" />

                    <!-- List of current contacts -->
                    <div class="space-y-3 mb-6">
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
                                        wire:click="deleteEmergencyContact({{ $contact->id }})"
                                        class="text-red-500 hover:text-red-655 hover:bg-red-50 dark:hover:bg-red-955/50"
                                    />
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No custom emergency contacts added yet.</p>
                        @endforelse
                    </div>

                    <flux:separator class="my-4" />
                    <flux:heading size="sm">{{ __('Add New Contact') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start mt-4">
                        <flux:input wire:model="new_contact_name" :label="__('Name')" type="text" placeholder="Angela Bautista" />
                        <flux:input wire:model="new_contact_relation" :label="__('Relation')" type="text" placeholder="Sister" />
                        <flux:input wire:model="new_contact_phone" :label="__('Phone Number')" type="text" placeholder="+63 9xxx..." />
                    </div>

                    <div class="flex justify-end mt-4">
                        <flux:button type="button" wire:click="addEmergencyContact" variant="primary" class="bg-[#801818] hover:bg-[#992222] text-white border-none px-6">
                            {{ __('Add Contact') }}
                        </flux:button>
                    </div>
                </div>

                <!-- Update Password Card -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                    <flux:heading size="lg">{{ __('Update Password') }}</flux:heading>
                    <flux:subheading>{{ __('Ensure your account is using a long, random password to stay secure') }}</flux:subheading>
                    <flux:separator class="my-4" />

                    <form method="POST" wire:submit="updatePassword" class="space-y-4">
                        <flux:input
                            wire:model="current_password"
                            :label="__('Current password')"
                            type="password"
                            required
                            autocomplete="current-password"
                            viewable
                        />
                        <flux:input
                            wire:model="password"
                            :label="__('New password')"
                            type="password"
                            required
                            autocomplete="new-password"
                            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                            viewable
                        />
                        <flux:input
                            wire:model="password_confirmation"
                            :label="__('Confirm password')"
                            type="password"
                            required
                            autocomplete="new-password"
                            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                            viewable
                        />

                        <div class="flex justify-end gap-3 pt-4">
                            <flux:button variant="primary" type="submit" class="rounded-xl px-6" data-test="update-password-button">
                                {{ __('Update Password') }}
                            </flux:button>
                        </div>
                    </form>
                </div>

                <!-- Two Factor Card -->
                @if ($canManageTwoFactor)
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                        <flux:heading size="lg">{{ __('Two-Factor Authentication') }}</flux:heading>
                        <flux:subheading>{{ __('Manage your secondary verification settings for secure account access') }}</flux:subheading>
                        <flux:separator class="my-4" />

                        <div class="flex flex-col w-full mx-auto space-y-4 text-sm" wire:cloak>
                            @if ($twoFactorEnabled)
                                <div class="space-y-4">
                                    <flux:text>
                                        {{ __('Two-factor authentication is active. You will be prompted for a secure TOTP verification pin during logins.') }}
                                    </flux:text>

                                    <div class="flex justify-start">
                                        <flux:button
                                            variant="danger"
                                            wire:click="disable"
                                            class="rounded-xl"
                                        >
                                            {{ __('Disable 2FA') }}
                                        </flux:button>
                                    </div>

                                    <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                                </div>
                            @else
                                <div class="space-y-4">
                                    <flux:text variant="subtle">
                                        {{ __('Enable two-factor authentication to secure your account. You will need a standard TOTP app (like Google Authenticator or 1Password).') }}
                                    </flux:text>

                                    <flux:modal.trigger name="two-factor-setup-modal">
                                        <flux:button
                                            variant="primary"
                                            wire:click="$dispatch('start-two-factor-setup')"
                                            class="rounded-xl"
                                        >
                                            {{ __('Enable 2FA') }}
                                        </flux:button>
                                    </flux:modal.trigger>

                                    <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Passkeys Management Card -->
                @if ($canManagePasskeys)
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                        <flux:heading size="lg">{{ __('WebAuthn Passkeys') }}</flux:heading>
                        <flux:subheading>{{ __('Configure biometrics or security keys for passwordless account login') }}</flux:subheading>
                        <flux:separator class="my-4" />

                        <div class="flex flex-col w-full mx-auto space-y-4 text-sm" wire:cloak>
                            <div class="border rounded-xl border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                @forelse ($passkeys as $passkey)
                                    <div class="flex items-center justify-between p-4 {{ ! $loop->last ? 'border-b border-zinc-200 dark:border-zinc-700' : '' }}">
                                        <div class="flex items-center gap-4">
                                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                                <flux:icon.key class="size-5 text-zinc-500 dark:text-zinc-400" />
                                            </div>
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2.5">
                                                    <p class="font-medium tracking-tight text-neutral-900 dark:text-white">{{ $passkey['name'] }}</p>
                                                    @if ($passkey['authenticator'])
                                                        <flux:badge size="sm">{{ $passkey['authenticator'] }}</flux:badge>
                                                    @endif
                                                </div>
                                                <p class="text-zinc-500 dark:text-zinc-400 text-xs">
                                                    {{ __('Added :time', ['time' => $passkey['created_at_diff']]) }}
                                                    @if ($passkey['last_used_at_diff'])
                                                        <span class="opacity-50 mx-1">/</span>
                                                        {{ __('Last used :time', ['time' => $passkey['last_used_at_diff']]) }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="trash"
                                            icon:variant="outline"
                                            wire:click="confirmDelete({{ $passkey['id'] }})"
                                            class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50"
                                        />
                                    </div>
                                @empty
                                    <div class="p-8 text-center">
                                        <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon.key class="size-7 text-zinc-400 dark:text-zinc-500" />
                                        </div>
                                        <p class="font-medium text-neutral-900 dark:text-white">{{ __('No passkeys yet') }}</p>
                                        <flux:text class="mt-1">{{ __('Add a passkey to sign in securely without a password') }}</flux:text>
                                    </div>
                                @endforelse
                            </div>

                            <x-passkey-registration />
                        </div>
                    </div>
                @endif

                <!-- Delete Account Card -->
                @if ($this->showDeleteUser)
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                        <flux:heading size="lg">{{ __('Delete Account') }}</flux:heading>
                        <flux:subheading>{{ __('Permanently delete your account and all of its resources') }}</flux:subheading>
                        <flux:separator class="my-4" />

                        <flux:modal.trigger name="confirm-user-deletion">
                            <flux:button variant="danger" class="rounded-xl px-6" data-test="delete-user-button">
                                {{ __('Delete Account') }}
                            </flux:button>
                        </flux:modal.trigger>

                        <livewire:pages::settings.delete-user-modal />
                    </div>
                @endif
            </div>
        </div>

        <!-- Delete Passkey Confirmation Modal -->
        <flux:modal
            name="delete-passkey-modal"
            class="max-w-md md:min-w-md"
            @close="closeDeleteModal"
            wire:model="showDeleteModal"
        >
            <div class="space-y-6">
                <div class="space-y-2">
                    <flux:heading size="lg">{{ __('Remove passkey') }}</flux:heading>
                    <flux:text>
                        {{ __('Are you sure you want to remove the passkey ":name"? You will no longer be able to use it to sign in.', ['name' => $deletingPasskeyName]) }}
                    </flux:text>
                </div>

                <div class="flex gap-3 justify-end">
                    <flux:button
                        variant="outline"
                        wire:click="closeDeleteModal"
                    >
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button
                        variant="danger"
                        wire:click="deletePasskey"
                    >
                        {{ __('Remove passkey') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>