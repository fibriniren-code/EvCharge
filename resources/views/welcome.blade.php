<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050810">
    <title>{{ config('app.name', 'EVCharge') }} · Isi daya kendaraan listrik, tanpa ribet</title>
    <meta name="description" content="Temukan SPKLU terdekat, reservasi slot, scan QR, dan bayar otomatis dari saldo.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="aurora" aria-hidden="true">
        <i class="-left-32 -top-32 h-[32rem] w-[32rem] bg-emerald-500"></i>
        <i class="-right-40 top-1/4 h-[30rem] w-[30rem] bg-cyan-500" style="animation-delay:-6s"></i>
        <i class="-bottom-40 left-1/3 h-[26rem] w-[26rem] bg-violet-600 opacity-20" style="animation-delay:-10s"></i>
    </div>

    {{-- Navbar --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/[0.06] bg-ink-950/60 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 sm:px-8">
            <a href="/"><x-application-logo /></a>
            <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-400 md:flex">
                <a href="#fitur" class="transition hover:text-white">Fitur</a>
                <a href="#cara-kerja" class="transition hover:text-white">Cara kerja</a>
                <a href="#peran" class="transition hover:text-white">Untuk siapa</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary btn-sm">Buka dashboard <x-icon name="arrow-right" class="h-4 w-4" /></a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm">Daftar gratis</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-20 pt-36 sm:px-8 lg:grid-cols-2 lg:pt-44">
        <div>
            <span class="badge-emerald reveal"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-current"></span>Platform pengisian daya EV</span>
            <h1 class="reveal mt-6 text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-6xl" style="--i:1">Isi daya mobil listrik <span class="text-gradient">tanpa antre, tanpa ribet.</span></h1>
            <p class="reveal mt-6 max-w-xl text-lg leading-relaxed text-slate-400" style="--i:2">Cari SPKLU terdekat di peta, tahan slot lebih dulu, mulai charging lewat scan QR, dan bayar otomatis dari saldo. Semua dalam satu aplikasi.</p>
            <div class="reveal mt-9 flex flex-wrap gap-3" style="--i:3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary btn-lg">Buka dashboard <x-icon name="arrow-right" class="h-5 w-5" /></a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary btn-lg">Mulai sekarang <x-icon name="arrow-right" class="h-5 w-5" /></a>
                    <a href="{{ route('login') }}" class="btn-secondary btn-lg">Saya sudah punya akun</a>
                @endauth
            </div>
            <dl class="reveal mt-12 grid max-w-md grid-cols-3 gap-6" style="--i:4">
                @foreach ([['24/7', 'Peta SPKLU'], ['3 jam', 'Slot ditahan'], ['QR', 'Mulai instan']] as [$v, $l])
                    <div><dt class="text-2xl font-extrabold text-white">{{ $v }}</dt><dd class="text-xs text-slate-500">{{ $l }}</dd></div>
                @endforeach
            </dl>
        </div>

        {{-- Ilustrasi sesi charging --}}
        <div class="reveal relative mx-auto w-full max-w-md" style="--i:2">
            <div class="absolute -inset-6 -z-10 rounded-[3rem] bg-gradient-to-br from-emerald-400/20 via-cyan-400/10 to-transparent blur-2xl"></div>
            <div class="glass animate-float rounded-[2rem] p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <span class="badge-sky"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-current"></span>Mengisi daya</span>
                    <span class="text-xs text-slate-500">Charger #02 · 50 kW</span>
                </div>
                <div class="relative mx-auto my-8 flex h-44 w-44 items-center justify-center">
                    <svg viewBox="0 0 120 120" class="absolute inset-0 -rotate-90">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="8" />
                        <circle cx="60" cy="60" r="52" fill="none" stroke="url(#hg)" stroke-width="8" stroke-linecap="round" stroke-dasharray="327" stroke-dashoffset="90" class="animate-spin-slow" style="transform-origin:center" />
                        <defs><linearGradient id="hg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#34d399" /><stop offset="1" stop-color="#22d3ee" /></linearGradient></defs>
                    </svg>
                    <div class="text-center">
                        <x-icon name="bolt" class="mx-auto h-6 w-6 text-emerald-300" :stroke="2.4" />
                        <p class="mt-1 text-4xl font-extrabold text-white">24,8</p>
                        <p class="text-xs font-semibold text-slate-400">kWh terisi</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-white/[0.05] p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Durasi</p><p class="mt-1 font-mono text-xl font-bold text-white">00:29:41</p></div>
                    <div class="rounded-2xl bg-white/[0.05] p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Biaya</p><p class="mt-1 text-xl font-bold text-amber-300">Rp 61.157</p></div>
                </div>
                <div class="mt-5 h-2 overflow-hidden rounded-full bg-white/10"><div class="stripe-flow h-full w-2/3 rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400"></div></div>
            </div>
        </div>
    </section>

    {{-- Fitur --}}
    <section id="fitur" class="mx-auto max-w-7xl px-5 py-20 sm:px-8">
        <div class="sr max-w-2xl" data-reveal>
            <p class="eyebrow">Fitur</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Semua yang dibutuhkan pengemudi EV</h2>
        </div>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['pin', 'Peta & rute SPKLU', 'Lihat stasiun terdekat, ketersediaan charger, jarak, dan rute berkendara.'],
                ['calendar', 'Reservasi slot', 'Tahan charger sebelum berangkat agar tidak kehabisan slot.'],
                ['scan', 'Scan QR', 'Mulai dan hentikan pengisian cukup dengan memindai QR di charger.'],
                ['battery', 'Pantau real-time', 'Energi, durasi, dan biaya terupdate langsung selama charging.'],
                ['wallet', 'Bayar otomatis', 'Biaya dipotong dari saldo sesuai tarif stasiun, struk digital tersedia.'],
                ['star', 'Ulasan transparan', 'Beri rating alat dan tempat, operator menanggapi langsung.'],
            ] as $i => [$ic, $t, $d])
                <div class="sr card card-hover p-6" data-reveal style="--i: {{ $i }}">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-400/20 bg-emerald-400/10 text-emerald-300"><x-icon :name="$ic" class="h-6 w-6" /></span>
                    <h3 class="mt-5 text-lg font-bold">{{ $t }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Cara kerja --}}
    <section id="cara-kerja" class="mx-auto max-w-7xl px-5 py-20 sm:px-8">
        <div class="sr max-w-2xl" data-reveal>
            <p class="eyebrow">Cara kerja</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Empat langkah, selesai</h2>
        </div>
        <div class="mt-12 grid gap-5 md:grid-cols-4">
            @foreach ([['Cari', 'Temukan SPKLU di peta'], ['Pesan', 'Reservasi & konfirmasi slot'], ['Charge', 'Scan QR lalu pantau live'], ['Bayar', 'Saldo terpotong otomatis']] as $i => [$t, $d])
                <div class="sr relative" data-reveal style="--i: {{ $i }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-cyan-400 text-lg font-extrabold text-ink-950 shadow-lg shadow-emerald-500/30">{{ $i + 1 }}</div>
                    @if ($i < 3)<div class="absolute left-16 top-6 hidden h-px w-[calc(100%-4rem)] bg-gradient-to-r from-emerald-400/50 to-transparent md:block"></div>@endif
                    <h3 class="mt-5 text-lg font-bold">{{ $t }}</h3>
                    <p class="mt-1 text-sm text-slate-400">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Peran --}}
    <section id="peran" class="mx-auto max-w-7xl px-5 py-20 sm:px-8">
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ([['car', 'Pengemudi', 'Cari, reservasi, charging, riwayat, dan saldo.'], ['settings', 'Operator', 'Pantau port real-time, kendali stasiun, tangani gangguan dan keluhan.'], ['shield', 'Admin', 'Kelola stasiun, charger, tarif, dan moderasi ulasan.']] as $i => [$ic, $t, $d])
                <div class="sr glass rounded-3xl p-7" data-reveal style="--i: {{ $i }}">
                    <x-icon :name="$ic" class="h-8 w-8 text-emerald-300" />
                    <h3 class="mt-5 text-xl font-bold">{{ $t }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-5xl px-5 pb-24 sm:px-8">
        <div class="sr relative overflow-hidden rounded-[2rem] border border-emerald-400/20 bg-gradient-to-br from-emerald-400/15 via-cyan-400/10 to-transparent p-10 text-center sm:p-16" data-reveal>
            <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Siap mengisi daya?</h2>
            <p class="mx-auto mt-4 max-w-lg text-slate-400">Daftar gratis dan temukan SPKLU terdekat dalam hitungan detik.</p>
            <div class="mt-8 flex justify-center gap-3">
                <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="btn-primary btn-lg">{{ auth()->check() ? 'Buka dashboard' : 'Buat akun gratis' }}</a>
            </div>
        </div>
    </section>

    <footer class="border-t border-white/[0.06] py-8 text-center text-xs text-slate-600">&copy; {{ date('Y') }} EVCharge · Platform pengisian daya kendaraan listrik</footer>
</body>
</html>