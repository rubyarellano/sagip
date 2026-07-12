<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#eaeaea] dark:bg-zinc-950 flex flex-col md:flex-row">
        
        <!-- Left Maroon Sidebar -->
        <flux:sidebar sticky collapsible="mobile" class="!bg-[#5c1d1d] !text-white border-none shrink-0 py-6 px-4">
            <flux:sidebar.header class="mb-6">
                <x-app-logo :sidebar="true" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden text-white hover:text-white/80" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="space-y-2">
                @if(auth()->user()->isAdmin())
                    <flux:sidebar.group class="grid gap-1">
                        <flux:sidebar.item :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.live-map')" :current="request()->routeIs('admin.live-map')" wire:navigate>
                            {{ __('Live Map') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.alert-history')" :current="request()->routeIs('admin.alert-history')" wire:navigate>
                            {{ __('Alert History') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                            {{ __('Registered users') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @else
                    <flux:sidebar.group class="grid gap-1">
                        <flux:sidebar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('My Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('report-incident')" :current="request()->routeIs('report-incident')" wire:navigate>
                            {{ __('Report Incident') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('reports-history')" :current="request()->routeIs('reports-history')" wire:navigate>
                            {{ __('Reports History') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        <!-- Right Content Container -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#eaeaea] dark:bg-zinc-900">
            <!-- Top Header -->
            <header class="flex items-center justify-between px-6 py-6 bg-transparent shrink-0">
                <!-- Left: Breadcrumbs or Welcome Message -->
                <div>
                    @if(request()->routeIs('dashboard') || request()->routeIs('admin.dashboard'))
                        <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-white tracking-tight">
                            Welcome, {{ auth()->user()->first_name ?? auth()->user()->name }}
                        </h1>
                    @else
                        @php
                            $routeName = request()->route()->getName();
                            $breadcrumbTitle = match($routeName) {
                                'admin.profile' => 'Profile',
                                'admin.live-map' => 'Live Map',
                                'admin.alert-history' => 'Alert History',
                                'admin.users' => 'Registered users',
                                'report-incident' => 'Report Incident',
                                'reports-history' => 'Reports History',
                                'emergency-contacts' => 'Emergency Contacts',
                                'profile.edit' => 'Profile',
                                default => $title ?? 'Page',
                            };
                        @endphp
                        <div class="flex items-center gap-1.5 text-sm font-bold text-neutral-500 dark:text-neutral-400">
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="hover:underline text-neutral-500 dark:text-neutral-400" wire:navigate>Home</a>
                            <span>&gt;</span>
                            <span class="text-neutral-900 dark:text-white font-extrabold">{{ $breadcrumbTitle }}</span>
                        </div>
                    @endif
                </div>

                    <!-- Right: Profile & Avatar -->
                    <div class="flex items-center gap-4">
                        <!-- Toggle for Mobile Sidebar (show on mobile header) -->
                        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                        <!-- Alert Bell Icon -->
                        @php
                            $unreadCount = auth()->user()->appNotifications()->where('is_read', false)->count();
                        @endphp
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.notifications') : route('notifications') }}" wire:navigate class="relative flex items-center justify-center hover:opacity-85 mr-2" title="Notifications">
                            <flux:icon.bell class="h-6 w-6 {{ $unreadCount > 0 ? 'text-red-600 animate-pulse' : 'text-neutral-500 dark:text-neutral-400' }}" />
                            @if($unreadCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 block h-2.5 w-2.5 rounded-full bg-red-600 ring-2 ring-[#eaeaea] dark:ring-zinc-900 animate-pulse"></span>
                            @endif
                        </a>

                        <a href="{{ auth()->user()->isAdmin() ? route('admin.profile') : route('profile.edit') }}" class="flex items-center gap-3 text-sm font-bold text-neutral-800 dark:text-neutral-200 hover:opacity-80" wire:navigate>
                        <span>Profile</span>
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white dark:border-zinc-700 shadow-xs flex items-center justify-center bg-zinc-200 shrink-0">
                            <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://api.dicebear.com/7.x/lorelei/svg?seed=' . urlencode(auth()->user()->name) }}" class="w-full h-full object-cover" alt="{{ auth()->user()->name }}">
                        </div>
                    </a>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto px-6 pb-6">
                {{ $slot }}
            </main>
        </div>
            
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

