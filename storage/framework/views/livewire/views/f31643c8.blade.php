<?php
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Concerns\PasswordValidationRules;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
?>

<div>
    <div class="max-w-7xl mx-auto px-4 py-4 space-y-8">
        
        <!-- Header Page Title -->
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                Admin Profile: Administrator {{ auth()->user()->name }}
            </h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: Personal Details Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-6">Personal Details</h2>
                    
                    <div class="flex flex-col items-center text-center space-y-4">
                        <!-- Female Avatar with pinkish/red circular border -->
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
                                <span class="font-bold text-neutral-800 dark:text-neutral-200">Username:</span>
                                <span class="text-neutral-600 dark:text-neutral-400">admin.ruby</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-neutral-800 dark:text-neutral-200">Role:</span>
                                <span class="text-neutral-600 dark:text-neutral-400">Senior Dispatcher</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-neutral-800 dark:text-neutral-200">Email:</span>
                                <span class="text-neutral-600 dark:text-neutral-400">{{ auth()->user()->email }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-neutral-800 dark:text-neutral-200">Phone:</span>
                                <span class="text-neutral-600 dark:text-neutral-400">{{ auth()->user()->phone ?: '+63 917 123 4567' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-neutral-800 dark:text-neutral-200">Last login:</span>
                                <span class="text-neutral-600 dark:text-neutral-400">2 mins ago (Sorsogon PST)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons aligned at the bottom of the column -->
                <div class="flex flex-col gap-3">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="w-full text-center bg-white dark:bg-zinc-800 hover:bg-neutral-50 dark:hover:bg-zinc-700 text-neutral-800 dark:text-neutral-200 font-bold px-6 py-2.5 rounded-xl border border-neutral-300 dark:border-zinc-700 shadow-xs cursor-pointer text-sm block">
                        (System Settings [Redirect])
                    </a>

                    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="w-full">
                        @csrf
                        <button type="submit" class="w-full bg-[#5c1d1d] hover:bg-[#4a1616] text-white font-bold px-6 py-2.5 rounded-xl shadow-xs cursor-pointer text-sm">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Security Controls -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Edit Personal Details Card -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                    <flux:heading size="lg">{{ __('Personal Details') }}</flux:heading>
                    <flux:subheading>{{ __('Update your dispatcher contact information and profile picture') }}</flux:subheading>
                    <flux:separator class="my-4" />

                    <form method="POST" wire:submit="updateProfile" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                            <flux:input
                                wire:model="name"
                                :label="__('Full Name')"
                                required
                            />
                            <flux:input
                                wire:model="email"
                                :label="__('Email Address')"
                                type="email"
                                required
                            />
                        </div>

                        <flux:input
                            wire:model="phone"
                            :label="__('Phone Number')"
                        />

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

                        <div class="flex justify-end gap-3 pt-4">
                            <flux:button variant="primary" type="submit" class="rounded-xl px-6">
                                {{ __('Save Details') }}
                            </flux:button>
                        </div>
                    </form>
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
                            <flux:button variant="primary" type="submit" class="rounded-xl px-6">
                                {{ __('Update Password') }}
                            </flux:button>
                        </div>
                    </form>
                </div>

                <!-- Two-Factor Authentication Card -->
                @if ($canManageTwoFactor)
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
                        <flux:heading size="lg">{{ __('Two-Factor Authentication') }}</flux:heading>
                        <flux:subheading>{{ __('Manage your secondary verification settings for secure admin access') }}</flux:subheading>
                        <flux:separator class="my-4" />

                        <div class="flex flex-col w-full mx-auto space-y-4 text-sm" wire:cloak>
                            @if ($twoFactorEnabled)
                                <div class="space-y-4">
                                    <flux:text>
                                        {{ __('Two-factor authentication is active. You will be prompted for a secure TOTP verification pin during administrative logins.') }}
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
                                        {{ __('Enable two-factor authentication to secure your dispatcher account. You will need a standard TOTP app (like Google Authenticator or 1Password).') }}
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
                        <flux:subheading>{{ __('Configure biometrics or security keys for passwordless dispatcher login') }}</flux:subheading>
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