<x-app-layout title="Beranda">
    @php
        $user = auth()->user();
        $hour = now()->hour;
        $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
        $steps = [
            ['Tambah mobil listrik', $vehicleCount > 0, route('cars.create'), 'car'],
            ['Isi saldo', (float) $user->balance >= 20000, route('topup.index'), 'wallet'],
            ['Cari SPKLU & pesan slot', $recent->isNotEmpty() || $activeReservation || $activeSession, route('spklu.index'), 'pin'],
        ];
        $done = collect($steps)->where(1, true)->count();
    @endphp

    {{-- Hero --}}
    <section class="reveal relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-emerald-500/20 via-ink-800 to-ink-900 p-6 sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-6">
            <div>
                <p class="eyebrow">{{ $greet }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Halo, <span class="text-gradient">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->first() }}</span> 👋</h1>
                <p class="mt-2 max-w-md text-sm text-slate-400">Siap mengisi daya hari ini? Temukan SPKLU terdekat atau lanjutkan reservasi Anda.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('spklu.index') }}" class="btn-primary"><x-icon name="pin" class="h-4 w-4" /> Cari SPKLU</a>
                    <a href="{{ route('driver.scan') }}" class="btn-secondary"><x-icon name="scan" class="h-4 w-4" /> Scan &amp; Charge</a>
                </div>
            </div>
            <div class="glass w-full rounded-2xl p-5 sm:w-72">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Saldo Anda</p>
                <p class="mt-1 text-3xl font-extrabold text-white"><span data-count="{{ (float) $user->balance }}" data-prefix="Rp ">Rp {{ number_format((float) $user->balance, 0, ',', '.') }}</span></p>
                <a href="{{ route('topup.index') }}" class="btn-soft-emerald btn-sm mt-4 w-full"><x-icon name="plus" class="h-4 w-4" /> Isi saldo</a>
            </div>
        </div>
    </section>

    @if ($outstanding > 0)
        <div class="reveal mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-400/30 bg-amber-400/10 p-4" style="--i:1">
            <div class="flex items-center gap-3 text-amber-200"><x-icon name="alert" class="h-5 w-5 shrink-0" /><p class="text-sm font-semibold">Tagihan tertunggak Rp {{ number_format($outstanding, 0, ',', '.') }}. Top up untuk melunasi dan bisa memesan lagi.</p></div>
            <a href="{{ route('topup.index') }}" class="btn-warn btn-sm">Top up sekarang</a>
        </div>
    @endif

    {{-- Sesi / reservasi aktif --}}
    @if ($activeSession || $activeReservation)
        <section class="mt-5 grid gap-5 lg:grid-cols-2">
            @if ($activeSession)
                <a href="{{ route('driver.charging.monitor', $activeSession->id_session) }}" class="card card-hover reveal group relative overflow-hidden border-emerald-400/30 p-6" style="--i:1">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/10 to-transparent"></div>
                    <div class="relative flex items-center justify-between"><x-status-badge status="running" type="session" /><x-icon name="arrow-up-right" class="h-5 w-5 text-slate-500 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-emerald-300" /></div>
                    <h3 class="relative mt-4 text-lg font-bold">Sedang mengisi daya</h3>
                    <p class="relative mt-1 text-sm text-slate-400">{{ $activeSession->charger?->location?->nama_lokasi }} · Charger #{{ $activeSession->id_charger }}</p>
                    <p class="relative mt-4 text-sm font-semibold text-emerald-300">Buka monitoring live →</p>
                </a>
            @endif

            @if ($activeReservation)
                <a href="{{ route('reservations.show', $activeReservation->id_reservation) }}" class="card card-hover reveal group p-6" style="--i:2">
                    <div class="flex items-center justify-between"><x-status-badge :status="$activeReservation->status" type="reservation" /><x-icon name="arrow-up-right" class="h-5 w-5 text-slate-500 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-emerald-300" /></div>
                    <h3 class="mt-4 text-lg font-bold">Reservasi #{{ $activeReservation->id_reservation }}</h3>
                    <p class="mt-1 text-sm text-slate-400">{{ $activeReservation->charger?->location?->nama_lokasi }} · {{ $activeReservation->waktu_mulai->format('d M, H:i') }}</p>
                    <p class="mt-4 text-sm font-semibold text-emerald-300">{{ $activeReservation->status === 'pending' ? 'Konfirmasi sebelum ' . $activeReservation->expires_at->format('H:i') : 'Scan QR untuk memulai' }} →</p>
                </a>
            @endif
        </section>
    @endif

    {{-- Statistik bulan ini --}}
    <h2 class="reveal mb-4 mt-9 text-lg font-bold" style="--i:2">Ringkasan bulan ini</h2>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat label="Sesi charging" :value="$stats['sessions']" icon="bolt" :i="3" />
        <x-stat label="Energi terisi" :value="$stats['kwh']" icon="battery" tone="sky" suffix=" kWh" :decimals="1" :i="4" />
        <x-stat label="Total biaya" :value="$stats['spend']" icon="wallet" tone="amber" prefix="Rp " :i="5" />
    </div>

    <div class="mt-8 grid gap-5 lg:grid-cols-3">
        {{-- Transaksi terakhir --}}
        <section class="card reveal lg:col-span-2" style="--i:4">
            <div class="card-head"><h2 class="font-bold">Transaksi terakhir</h2><a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">Lihat semua →</a></div>
            @forelse ($recent as $s)
                <a href="{{ route('driver.charging.receipt', $s->id_session) }}" class="group flex items-center gap-4 border-b border-white/5 px-5 py-4 transition last:border-0 hover:bg-white/[0.03] sm:px-6">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-400/10 text-emerald-300"><x-icon name="bolt" class="h-5 w-5" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-white">{{ $s->charger?->location?->nama_lokasi ?? 'SPKLU' }}</p>
                        <p class="text-xs text-slate-500">{{ $s->waktu_mulai->format('d M Y, H:i') }} · {{ number_format((float) $s->energi_kwh, 2, ',', '.') }} kWh</p>
                    </div>
                    <p class="text-sm font-extrabold text-white">Rp {{ number_format((float) $s->estimasi_biaya, 0, ',', '.') }}</p>
                    <x-icon name="chevron-right" class="h-4 w-4 text-slate-600 transition group-hover:translate-x-0.5 group-hover:text-slate-300" />
                </a>
            @empty
                <x-empty icon="receipt" title="Belum ada transaksi" text="Riwayat pengisian daya Anda akan muncul di sini.">
                    <a href="{{ route('spklu.index') }}" class="btn-primary btn-sm">Cari SPKLU</a>
                </x-empty>
            @endforelse
        </section>

        {{-- Checklist mulai --}}
        <section class="card reveal p-6" style="--i:5">
            <div class="flex items-center justify-between"><h2 class="font-bold">Mulai cepat</h2><span class="badge-slate">{{ $done }}/{{ count($steps) }}</span></div>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/10"><div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400 transition-all duration-1000" style="width: {{ round($done / count($steps) * 100) }}%"></div></div>
            <ul class="mt-5 space-y-2.5">
                @foreach ($steps as [$label, $ok, $url, $ic])
                    <li>
                        <a href="{{ $url }}" class="flex items-center gap-3 rounded-xl border border-white/[0.07] p-3 text-sm font-semibold transition hover:border-emerald-400/30 hover:bg-white/[0.04] {{ $ok ? 'text-slate-500' : 'text-white' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $ok ? 'bg-emerald-400/15 text-emerald-300' : 'bg-white/5 text-slate-400' }}"><x-icon :name="$ok ? 'check' : $ic" class="h-4 w-4" /></span>
                            <span class="{{ $ok ? 'line-through' : '' }}">{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-app-layout>