@php
    $user = auth()->user();
    $role = $user->role ?? 'driver';
    $pageTitle = $attributes->get('title');

    $roleLabel = ['admin' => 'Administrator', 'operator' => 'Operator SPKLU', 'driver' => 'Pengemudi'][$role] ?? 'Pengguna';

    $activeSession = null;
    $navBadges = [];

    if ($role === 'driver') {
        $activeSession = \App\Models\ChargingSession::where('user_id', $user->id)->where('status', 'running')->latest('id_session')->first();
    }

    if ($role === 'operator') {
        $navBadges['operator.stations.index'] = \App\Models\Charger::where('status', 'faulted')->count();
        $navBadges['operator.reviews.index'] = \App\Models\Review::whereNull('balasan_operator')->count();
    }

    $nav = match ($role) {
        'admin' => [
            ['admin.dashboard', 'Dashboard', 'grid', 'admin.dashboard'],
            ['admin.stations.index', 'Stasiun & Charger', 'building', 'admin.stations.*'],
            ['admin.tariffs.index', 'Tarif & Biaya', 'tag', 'admin.tariffs.*'],
            ['admin.reviews.index', 'Moderasi Ulasan', 'star', 'admin.reviews.*'],
            ['profile.edit', 'Profil Saya', 'user', 'profile.*'],
        ],
        'operator' => [
            ['operator.dashboard', 'Monitoring Live', 'grid', 'operator.dashboard'],
            ['operator.stations.index', 'Stasiun & Port', 'plug', 'operator.stations.*'],
            ['operator.sessions.index', 'Sesi Charging', 'bolt', 'operator.sessions.*'],
            ['operator.reviews.index', 'Ulasan & Keluhan', 'chat', 'operator.reviews.*'],
            ['profile.edit', 'Profil Saya', 'user', 'profile.*'],
        ],
        default => [
            ['dashboard', 'Beranda', 'home', 'dashboard'],
            ['spklu.index', 'Cari SPKLU', 'pin', 'spklu.*'],
            ['reservations.index', 'Reservasi', 'calendar', 'reservations.*'],
            ['driver.scan', 'Scan & Charge', 'scan', 'driver.scan'],
            ['transactions.index', 'Riwayat', 'receipt', ['transactions.*', 'driver.charging.receipt']],
            ['cars.index', 'Mobil Saya', 'car', 'cars.*'],
            ['topup.index', 'Isi Saldo', 'wallet', 'topup.*'],
            ['profile.edit', 'Profil Saya', 'user', 'profile.*'],
        ],
    };
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#050810">
    <title>{{ $pageTitle ? $pageTitle . ' · ' : '' }}{{ config('app.name', 'EVCharge') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body x-data="{ drawer: false }" x-effect="document.body.style.overflow = drawer ? 'hidden' : ''" @keydown.escape.window="drawer = false">
    <div id="page-progress" class="page-progress no-print"></div>

    <div class="aurora no-print" aria-hidden="true">
        <i class="-left-32 -top-32 h-[28rem] w-[28rem] bg-emerald-500"></i>
        <i class="-right-40 top-1/3 h-[26rem] w-[26rem] bg-cyan-500" style="animation-delay:-5s"></i>
        <i class="-bottom-40 left-1/4 h-[24rem] w-[24rem] bg-violet-600 opacity-20" style="animation-delay:-9s"></i>
    </div>

    {{-- Sidebar desktop --}}
    <aside class="no-print fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-white/[0.06] bg-ink-900/70 backdrop-blur-2xl lg:block">
        @include('layouts.partials.sidebar')
    </aside>

    {{-- Drawer mobile --}}
    <div class="no-print lg:hidden" x-show="drawer" x-cloak>
        <div x-show="drawer" x-transition.opacity class="fixed inset-0 z-[60] bg-ink-950/80 backdrop-blur-sm" @click="drawer = false"></div>
        <aside x-show="drawer"
               x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-[70] w-72 max-w-[85vw] border-r border-white/10 bg-ink-900 shadow-2xl">
            <button type="button" class="absolute right-3 top-5 rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white" @click="drawer = false" aria-label="Tutup menu"><x-icon name="x" /></button>
            @include('layouts.partials.sidebar')
        </aside>
    </div>

    <div class="flex min-h-screen flex-col lg:pl-72">
        {{-- Top bar --}}
        <header class="no-print sticky top-0 z-30 border-b border-white/[0.06] bg-ink-950/70 backdrop-blur-xl" style="padding-top: env(safe-area-inset-top)">
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-10">
                <div class="flex items-center gap-3">
                    <button type="button" class="rounded-xl border border-white/10 bg-white/[0.04] p-2.5 text-slate-300 transition hover:bg-white/10 lg:hidden" @click="drawer = true" aria-label="Buka menu"><x-icon name="menu" class="h-5 w-5" /></button>
                    <a href="{{ route('dashboard') }}" class="lg:hidden"><x-application-logo size="h-9 w-9" :text="false" /></a>
                    <div class="hidden lg:block">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">{{ now()->locale('id')->translatedFormat('l, j F Y') }}</p>
                        <p class="-mt-0.5 text-sm font-bold text-white">{{ $pageTitle ?? $roleLabel }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @if ($activeSession)
                        <a href="{{ route('driver.charging.monitor', $activeSession->id_session) }}" class="relative inline-flex items-center gap-2 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-3 py-2 text-xs font-bold text-emerald-300 transition hover:bg-emerald-400/20">
                            <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span></span>
                            <span class="hidden sm:inline">Sedang charging</span><span class="sm:hidden">Live</span>
                        </a>
                    @endif

                    @if ($role === 'driver')
                        <a href="{{ route('topup.index') }}" class="group flex items-center gap-2.5 rounded-xl border border-white/10 bg-white/[0.04] py-1.5 pl-3 pr-1.5 transition hover:border-emerald-400/30">
                            <span class="text-right leading-none"><span class="block text-[9px] font-bold uppercase tracking-widest text-slate-500">Saldo</span><span class="text-sm font-extrabold text-emerald-300">Rp {{ number_format((float) $user->balance, 0, ',', '.') }}</span></span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-cyan-400 text-ink-950 transition group-hover:scale-105"><x-icon name="plus" class="h-4 w-4" :stroke="2.6" /></span>
                        </a>
                    @else
                        <span class="badge-emerald hidden sm:inline-flex"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-current"></span>Sistem online</span>
                    @endif
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 pb-28 pt-6 sm:px-6 lg:px-10 lg:pb-12 lg:pt-8">
            <div class="mx-auto w-full max-w-7xl animate-fade-up">
                @isset($header)
                    <div class="mb-6">{{ $header }}</div>
                @endisset
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>

        <footer class="no-print hidden px-10 pb-6 text-xs text-slate-600 lg:block">
            &copy; {{ date('Y') }} EVCharge &middot; Platform pengisian daya kendaraan listrik
        </footer>
    </div>

    {{-- Bottom nav mobile (driver) --}}
    @if ($role === 'driver')
        <nav class="no-print fixed inset-x-0 bottom-0 z-40 border-t border-white/10 bg-ink-900/90 backdrop-blur-2xl lg:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
            <div class="mx-auto grid max-w-md grid-cols-5 items-end px-2">
                @foreach ([['dashboard', 'Beranda', 'home', 'dashboard'], ['spklu.index', 'SPKLU', 'pin', 'spklu.*'], ['driver.scan', 'Scan', 'scan', 'driver.scan'], ['reservations.index', 'Reservasi', 'calendar', 'reservations.*'], ['profile.edit', 'Profil', 'user', 'profile.*']] as [$r, $label, $ic, $pat])
                    @php $on = request()->routeIs($pat); @endphp
                    @if ($r === 'driver.scan')
                        <a href="{{ route($r) }}" class="-mt-5 flex flex-col items-center gap-1 pb-2">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-cyan-400 text-ink-950 shadow-lg shadow-emerald-500/40 ring-4 ring-ink-900 transition active:scale-90"><x-icon :name="$ic" class="h-6 w-6" :stroke="2.2" /></span>
                            <span class="text-[10px] font-bold {{ $on ? 'text-emerald-300' : 'text-slate-400' }}">{{ $label }}</span>
                        </a>
                    @else
                        <a href="{{ route($r) }}" class="flex flex-col items-center gap-1 py-2.5 transition active:scale-90 {{ $on ? 'text-emerald-300' : 'text-slate-500' }}">
                            <x-icon :name="$ic" class="h-[22px] w-[22px]" />
                            <span class="text-[10px] font-bold">{{ $label }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>
    @endif

    <x-flash />
    @stack('scripts')
</body>
</html>