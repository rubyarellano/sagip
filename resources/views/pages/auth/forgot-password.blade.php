<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#801818]">SAGIP Alert System</h1>
            <p class="text-sm text-neutral-500 font-medium">Let's Create Our Own Account</p>
        </div>

        <div class="space-y-1">
            <h3 class="text-lg font-bold text-[#801818]">{{ __('Forgot Password') }}</h3>
            <p class="text-sm text-neutral-500">{{ __('Enter your email to reset your password') }}</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <div>
                <flux:input
                    name="email"
                    :label="__('Email')"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    placeholder="Enter your email"
                    class="bg-neutral-100 border-none rounded-xl"
                />
                @error('email')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
            </div>

            <div class="mt-2">
                <flux:button type="submit" class="w-full !bg-[#801818] hover:!bg-[#601010] !text-white font-semibold rounded-xl py-3 shadow-md transition-all">
                    {{ __('Send Reset Link') }}
                </flux:button>
            </div>
        </form>

        <div class="text-center">
            <flux:link :href="route('login')" class="text-blue-600 hover:text-blue-800 underline text-sm" wire:navigate>{{ __('Back to Login') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
