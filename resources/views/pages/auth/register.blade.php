<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#801818]">SAGIP Alert System</h1>
            <p class="text-sm text-neutral-500 font-medium">Watch Registration</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Form Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                
                <!-- First Name -->
                <div>
                    <flux:input
                        name="first_name"
                        :label="__('First Name')"
                        :value="old('first_name')"
                        type="text"
                        required
                        autofocus
                        placeholder="Aly"
                        class="bg-neutral-100 border-none rounded-xl"
                    />
                    @error('first_name')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <flux:input
                        name="last_name"
                        :label="__('Last Name')"
                        :value="old('last_name')"
                        type="text"
                        required
                        placeholder="Bautista"
                        class="bg-neutral-100 border-none rounded-xl"
                    />
                    @error('last_name')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <flux:input
                        name="address"
                        :label="__('Address')"
                        :value="old('address')"
                        type="text"
                        required
                        placeholder="Enter your full address"
                        class="bg-neutral-100 border-none rounded-xl"
                    />
                    @error('address')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>

                <!-- Gender -->
                <div>
                    <flux:label>{{ __('Gender') }}</flux:label>
                    <select name="gender" required class="w-full bg-neutral-100 text-neutral-800 rounded-xl px-3 py-2 border-none focus:ring-2 focus:ring-[#801818] outline-none text-sm h-10">
                        <option value="">Please select your gender</option>
                        <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                        <option value="Other" @selected(old('gender') === 'Other')>Other</option>
                    </select>
                    @error('gender')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>

                <!-- Birthday -->
                <div>
                    <flux:input
                        name="birthday"
                        :label="__('Birthday')"
                        :value="old('birthday')"
                        type="date"
                        required
                        class="bg-neutral-100 border-none rounded-xl"
                    />
                    @error('birthday')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <flux:input
                        name="email"
                        :label="__('Email')"
                        :value="old('email')"
                        type="email"
                        required
                        placeholder="example@gmail.com"
                        class="bg-neutral-100 border-none rounded-xl"
                    />
                    @error('email')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div>
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    placeholder="Min. 8 characters"
                    viewable
                    class="bg-neutral-100 border-none rounded-xl"
                />
                @error('password')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm Password')"
                    type="password"
                    required
                    placeholder="••••••••"
                    viewable
                    class="bg-neutral-100 border-none rounded-xl"
                />
                @error('password_confirmation')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
            </div>

            <div class="text-sm">
                @if (Route::has('password.request'))
                    <a class="text-neutral-500 hover:underline transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        Forgot your password? <span class="text-[#801818] font-bold">Reset here</span>
                    </a>
                @endif
            </div>

            <div class="mt-2">
                <flux:button type="submit" class="w-full !bg-[#801818] hover:!bg-[#601010] !text-white font-semibold rounded-xl py-3 shadow-md transition-all">
                    {{ __('Register') }}
                </flux:button>
            </div>
        </form>

        <div class="text-sm text-center text-neutral-600">
            <span>{{ __("Have an account?") }}</span>
            <flux:link :href="route('login')" class="text-[#801818] font-bold hover:underline" wire:navigate>{{ __('Login here') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
