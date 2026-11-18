<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#050810">
    <title>{{ $attributes->get('title') ? $attributes->get('title') . ' · ' : '' }}{{ config('app.name', 'EVCharge') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="page-progress" class="page-progress"></div>
    <div class="aurora" aria-hidden="true">
        <i class="-left-32 -top-32 h-[30rem] w-[30rem] bg-emerald-500"></i>
        <i class="-right-40 top-1/2 h-[28rem] w-[28rem] bg-cyan-500" style="animation-delay:-6s"></i>
    </div>

    <div class="grid min-h-screen lg:grid-cols-[1.1fr_1fr]">
        {{-- Panel brand --}}
        <section class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between lg:p-14">
            <a href="/" class="relative z-10"><x-application-logo /></a>

            <div class="relative z-10 max-w-lg">
                <p class="eyebrow reveal">Platform pengisian daya EV</p>
                <h2 class="reveal mt-4 text-5xl font-extrabold leading-[1.1] tracking-tight" style="--i:1">Isi daya jadi <span class="text-gradient">semudah satu ketukan.</span></h2>
                <p class="reveal mt-5 text-base leading-relaxed text-slate-400" style="--i:2">Temukan SPKLU terdekat, reservasi slot, mulai charging dengan scan QR, dan bayar otomatis dari saldo.</p>

                <div class="reveal mt-10 grid grid-cols-3 gap-4" style="--i:3">
                    @foreach ([['pin', 'SPKLU', 'Peta & rute'], ['clock', 'Reservasi', 'Slot ditahan'], ['wallet', 'Auto-debit', 'Tanpa antre']] as [$ic, $t, $s])
                        <div class="glass rounded-2xl p-4 transition duration-300 hover:-translate-y-1 hover:border-emerald-400/30">
                            <x-icon :name="$ic" class="h-6 w-6 text-emerald-300" />
                            <p class="mt-3 text-sm font-bold text-white">{{ $t }}</p>
                            <p class="text-xs text-slate-500">{{ $s }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Ilustrasi charger --}}
            <div class="pointer-events-none absolute -right-10 bottom-10 w-[22rem] opacity-90" aria-hidden="true">
                <div class="relative mx-auto h-72 w-44 rounded-[2rem] border border-white/10 bg-gradient-to-b from-ink-700 to-ink-900 p-4 shadow-2xl animate-float">
                    <div class="flex h-full flex-col items-center justify-between rounded-[1.4rem] border border-white/5 bg-ink-950/80 p-4">
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-emerald-400/10 text-emerald-300 ring-pulse">
                            <x-icon name="bolt" class="h-9 w-9" :stroke="2" />
                        </div>
                        <div class="w-full space-y-2">
                            <div class="h-2 overflow-hidden rounded-full bg-white/10"><div class="stripe-flow h-full w-3/4 rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400"></div></div>
                            <div class="h-1.5 w-1/2 rounded-full bg-white/10"></div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="relative z-10 text-xs text-slate-600">&copy; {{ date('Y') }} EVCharge</p>
        </section>

        {{-- Panel form --}}
        <section class="flex items-center justify-center px-5 py-10 sm:px-10">
            <div class="w-full max-w-md">
                <a href="/" class="mb-8 inline-block lg:hidden"><x-application-logo /></a>
                <div class="animate-fade-up">{{ $slot }}</div>
            </div>
        </section>
    </div>
</body>
</html>