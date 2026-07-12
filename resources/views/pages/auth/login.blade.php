<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#801818]">SAGIP Alert System</h1>
            <p class="text-sm text-neutral-500 font-medium">Let's Create Our Own Account</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
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
                    autocomplete="email"
                    placeholder="example@gmail.com"
                    class="bg-neutral-100 border-none rounded-xl"
                />
                @error('email')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
            </div>

            <!-- Password -->
            <div class="flex flex-col">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Min. 8 characters"
                    viewable
                    class="bg-neutral-100 border-none rounded-xl"
                />
                @error('password')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <!-- Remember Me -->
                <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

                @if (Route::has('password.request'))
                    <a class="text-neutral-500 hover:underline transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        Forgot your password? <span class="text-[#801818] font-bold">Reset here</span>
                    </a>
                @endif
            </div>

            <div class="mt-2">
                <flux:button type="submit" class="w-full !bg-[#801818] hover:!bg-[#601010] !text-white font-semibold rounded-xl py-3 shadow-md transition-all" data-test="login-button">
                    {{ __('Login') }}
                </flux:button>
            </div>
        </form>

        <div class="text-sm text-center text-neutral-600">
            <span>{{ __("Don't have an account?") }}</span>
            <flux:link :href="route('register')" class="text-[#801818] font-bold hover:underline" wire:navigate>{{ __('Register here') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
