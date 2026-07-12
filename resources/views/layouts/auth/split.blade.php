<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
        </style>
    </head>
    <body class="min-h-screen bg-neutral-100 antialiased">
        <div class="relative grid min-h-screen flex-col items-center justify-center sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <!-- Left Brand Sidebar -->
            <div class="relative hidden h-full min-h-screen flex-col items-center justify-center p-10 text-white lg:flex" style="background-color: #551111;">
                <!-- Decorative subtle overlay pattern or gradient if needed, but solid dark red is premium -->
                <div class="flex flex-col items-center justify-center space-y-4">
                    <!-- Circular White Logo Container -->
                    <div class="flex h-64 w-64 items-center justify-center rounded-full bg-white shadow-xl">
                        <img src="{{ asset('images/logo.png') }}" class="h-40 w-40 object-contain" alt="SAGIP Logo">
                    </div>
                </div>
            </div>

            <!-- Right Form Section -->
            <div class="w-full lg:p-8 flex items-center justify-center min-h-screen">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 px-6 py-12 sm:max-w-[580px]">
                    <div class="lg:hidden flex justify-center mb-6">
                        <!-- Mobile logo -->
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-md border border-neutral-200">
                            <img src="{{ asset('images/logo.png') }}" class="h-10 w-10 object-contain" alt="SAGIP Logo">
                        </div>
                    </div>
                    
                    {{ $slot }}
                </div>
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
