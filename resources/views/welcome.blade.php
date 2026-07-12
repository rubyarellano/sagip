<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResQband - Sync Band: Unlock Your Potential</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .text-brand {
            color: #801818;
        }
        .bg-brand {
            background-color: #801818;
        }
        .bg-brand-hover:hover {
            background-color: #601010;
        }
        .border-brand {
            border-color: #801818;
        }
    </style>
</head>
<body class="min-h-screen bg-neutral-50 text-neutral-800 antialiased selection:bg-red-200 selection:text-red-950">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-50 w-full border-b border-neutral-200 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <!-- Brand Logo -->
            <a href="#" class="flex items-center">
                <img src="{{ asset('images/logo-word.png') }}" class="h-9 object-contain" alt="ResQband Logo">
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-600">
                <a href="#" class="text-brand hover:text-neutral-900 transition-colors">Home</a>
                <a href="#getting-started" class="hover:text-neutral-900 transition-colors">Setup Guide</a>
                <a href="#features" class="hover:text-neutral-900 transition-colors">Features</a>
                <a href="#about" class="hover:text-neutral-900 transition-colors">About Us</a>
            </nav>

            <!-- CTA -->
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        {{ __('Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg border border-neutral-300 bg-white px-5 py-2.5 text-sm font-semibold text-neutral-700 shadow-sm transition-all hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        LOGIN
                    </a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        REGISTER
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-white py-20 lg:py-32">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <!-- Left Content -->
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-brand">
                        <span class="flex h-2 w-2 rounded-full bg-brand animate-ping"></span>
                        SAGIP Companion System
                    </div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-neutral-900 sm:text-5xl md:text-6xl leading-[1.1]">
                        SYNC BAND: UNLOCK YOUR POTENTIAL.
                    </h1>
                    <p class="max-w-xl text-lg text-neutral-600 leading-relaxed">
                        Track Health, Stay Connected, and Master Your Time. Your smartwatch's perfect companion.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-xl bg-brand px-6 py-3.5 text-base font-semibold text-white shadow-lg transition-all hover:bg-brand-hover hover:translate-y-[-1px]">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-xl bg-brand px-6 py-3.5 text-base font-semibold text-white shadow-lg transition-all hover:bg-brand-hover hover:translate-y-[-1px]">
                                REGISTER
                            </a>
                            <a href="{{ route('login') }}" class="rounded-xl border border-neutral-300 bg-white px-6 py-3.5 text-base font-semibold text-neutral-700 shadow-sm transition-all hover:bg-neutral-50 hover:translate-y-[-1px]">
                                LOGIN
                            </a>
                            <a href="#features" class="rounded-xl border border-neutral-300 bg-white px-6 py-3.5 text-base font-semibold text-neutral-700 shadow-sm transition-all hover:bg-neutral-50 hover:translate-y-[-1px]">
                                Explore Band
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Right Visual -->
                <div class="relative flex justify-center lg:justify-end">
                    <div class="relative w-full max-w-[480px]">
                        <!-- Decorative background glow -->
                        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-red-200 to-red-100 opacity-70 blur-2xl"></div>
                        
                        <!-- Watch Illustration Frame -->
                        <div class="relative overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-900 shadow-2xl">
                            <img src="https://altimg.xyz/480x600.png/1e1e1e/ffffff?text=Smartwatch+Mockup" class="w-full h-auto object-cover aspect-[4/5] block" alt="Smartwatch Mockup">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Getting Started Section -->
    <section id="getting-started" class="bg-neutral-100 py-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="text-center space-y-4">
                <h2 class="text-3xl font-extrabold tracking-tight text-neutral-900 sm:text-4xl">
                    GETTING STARTED: 3 SIMPLE TO START TO SYNC
                </h2>
                <p class="mx-auto max-w-2xl text-neutral-600">
                    Connect and map your safety wristband to the SAGIP alert network.
                </p>
            </div>

            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Step 1 -->
                <div class="relative overflow-hidden rounded-2xl bg-white p-8 shadow-sm border border-neutral-200">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-neutral-900">1. POWER UP</h3>
                    <p class="mt-3 text-neutral-600 leading-relaxed text-sm">
                        Turning on your SAGIP Band by holding the side button until the startup logo flashes.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="relative overflow-hidden rounded-2xl bg-white p-8 shadow-sm border border-neutral-200">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-neutral-900">2. CONNECT BLUETOOTH</h3>
                    <p class="mt-3 text-neutral-600 leading-relaxed text-sm">
                        Open this website on your devices & ensure reverse Bluetooth pairing mode is active.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="relative overflow-hidden rounded-2xl bg-white p-8 shadow-sm border border-neutral-200">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-neutral-900">3. REGISTER & LINK</h3>
                    <p class="mt-3 text-neutral-600 leading-relaxed text-sm">
                        Click the button below to register & pair your unique identification credentials.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Step-by-Step Instructions -->
    <section class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="text-center space-y-4">
                <h2 class="text-3xl font-extrabold tracking-tight text-neutral-900 sm:text-4xl">
                    DETAILED INSTRUCTIONS: STEP-BY-STEP
                </h2>
                <p class="mx-auto max-w-2xl text-neutral-600">
                    Follow these guidelines to complete the setup process.
                </p>
            </div>

            <div class="mt-16 grid gap-8 sm:grid-cols-2 md:grid-cols-4">
                <div class="space-y-3">
                    <div class="text-4xl font-extrabold text-brand/20">01</div>
                    <h4 class="text-lg font-bold text-neutral-900">Browse the Web</h4>
                    <p class="text-sm text-neutral-600 leading-relaxed">
                        Open this website portal on your active device (PC, tablet, or smartphone).
                    </p>
                </div>
                
                <div class="space-y-3">
                    <div class="text-4xl font-extrabold text-brand/20">02</div>
                    <h4 class="text-lg font-bold text-neutral-900">Create Account</h4>
                    <p class="text-sm text-neutral-600 leading-relaxed">
                        Register a profile on the system with your name, contact information, and medical details.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="text-4xl font-extrabold text-brand/20">03</div>
                    <h4 class="text-lg font-bold text-neutral-900">Register the Watch</h4>
                    <p class="text-sm text-neutral-600 leading-relaxed">
                        Scan the QR code printed on the back of your watch casing to initiate instant pairing.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="text-4xl font-extrabold text-brand/20">04</div>
                    <h4 class="text-lg font-bold text-neutral-900">Go to your Account</h4>
                    <p class="text-sm text-neutral-600 leading-relaxed">
                        Edit your personal information, update your medical history, or change your profile picture.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Features Section -->
    <section id="features" class="bg-neutral-100 py-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <!-- Smartwatch mock image -->
                <div class="flex justify-center space-x-6">
                    <!-- Dynamic Watch SVGs -->
                    <div class="relative h-72 w-52 rounded-3xl bg-neutral-950 overflow-hidden shadow-xl">
                        <img src="https://altimg.xyz/300x400.png/0f0f0f/ffffff?text=Health+Metrics" class="w-full h-full object-cover" alt="Health Metrics Mockup">
                    </div>
                    
                    <div class="relative h-72 w-52 rounded-3xl bg-neutral-950 overflow-hidden shadow-xl translate-y-6">
                        <img src="https://altimg.xyz/300x400.png/0f0f0f/ffffff?text=SOS+Active" class="w-full h-full object-cover" alt="SOS Active Mockup">
                    </div>
                </div>

                <!-- Text specs -->
                <div id="about" class="space-y-8">
                    <h2 class="text-3xl font-extrabold tracking-tight text-neutral-900 sm:text-4xl">
                        ABOUT THE SAGIP BAND
                    </h2>
                    <p class="text-neutral-600 leading-relaxed">
                        A robust, responsive device engineered to sync vital metrics and alerts straight to disaster dispatchers.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-brand/10 text-brand">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-neutral-900">All-Day Battery</h5>
                                <p class="text-sm text-neutral-600">Built to withstand the toughest conditions with up to 7 days of continuous power.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-brand/10 text-brand">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-neutral-900">Comprehensive Health Monitoring</h5>
                                <p class="text-sm text-neutral-600">Track heart rate, blood oxygen levels, body temperature, and physical exertion metrics.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-brand/10 text-brand">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-neutral-900">Swim-Proof (5ATM)</h5>
                                <p class="text-sm text-neutral-600">Fully water-resistant up to 50 meters, perfect for floods, rains, and water rescue environments.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-brand/10 text-brand">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-neutral-900">Smart Notifications</h5>
                                <p class="text-sm text-neutral-600">Receive warning broadcasts, weather alerts, and instructions directly on your wrist.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-neutral-200 bg-white py-12">
        <div class="mx-auto max-w-7xl px-6 text-center text-sm text-neutral-500">
            <p>2024 Sagip Band, Inc. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
