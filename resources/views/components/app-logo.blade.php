@props([
    'sidebar' => false,
])

@php
    $href = $attributes->get('href', auth()->user() && auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard'));
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center gap-3 shrink-0']) }} wire:navigate>
    @if($sidebar)
        <div class="flex items-center justify-center w-11 h-11 bg-white rounded-full p-2 shadow-xs">
            <img src="{{ asset('images/logo.png') }}" class="w-full h-full object-contain" alt="SAGIP Icon">
        </div>
        <span class="text-white font-extrabold text-base tracking-wide whitespace-nowrap">SAGIP Alert System</span>
    @else
        <img src="{{ asset('images/logo-word.png') }}" class="h-12 object-contain" alt="SAGIP Logo">
    @endif
</a>

