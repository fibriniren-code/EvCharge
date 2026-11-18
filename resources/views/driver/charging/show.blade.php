<x-app-layout title="Monitoring Charging">
    @php
        $loc = $session->charger?->location;
        $v = $session->reservation?->vehicle;
        $endsAt = $session->reservation?->waktu_selesai;
    @endphp

    <x-page-header eyebrow="Live" title="Sedang mengisi daya" :subtitle="($loc->nama_lokasi ?? 'SPKLU') . ' · Charger #' . $session->id_charger">
        <x-slot name="actions"><x-status-badge status="running" type="session" class="!px-3.5 !py-2 !text-xs" /></x-slot>
    </x-page-header>

    <div x-data="telemetry({ url: '{{ route('driver.charging.telemetry', $session->id_session) }}', init: @js($bill) })" class="grid gap-5 lg:grid-cols-5">
        {{-- Gauge --}}
        <section class="card reveal relative overflow-hidden p-8 text-center lg:col-span-3" style="--i:1">
            <div class="pointer-events-none absolute left-1/2 top-1/3 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rounded-full bg-emerald-400/15 blur-3xl"></div>

            <div x-show="low" x-cloak x-transition class="relative mb-6 flex items-center gap-3 rounded-xl border border-amber-400/30 bg-amber-400/10 p-3 text-left text-sm text-amber-200"><x-icon name="alert" class="h-5 w-5 shrink-0" />Saldo hampir habis. Biaya mendekati saldo Anda; sisa tagihan tercatat bila saldo kurang.<a href="{{ route('topup.index') }}" class="ml-auto shrink-0 font-bold underline">Top up</a></div>

            <div class="relative mx-auto flex h-64 w-64 items-center justify-center sm:h-72 sm:w-72">
                <span class="absolute inset-4 rounded-full ring-pulse"></span>
                <svg viewBox="0 0 120 120" class="absolute inset-0 -rotate-90">
                    <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.07)" stroke-width="7" />
                    <circle cx="60" cy="60" r="52" fill="none" stroke="url(#cg)" stroke-width="7" stroke-linecap="round" stroke-dasharray="326.7" stroke-dashoffset="230" class="animate-spin-slow" style="transform-origin:center;animation-duration:3.5s" />
                    <defs><linearGradient id="cg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#34d399" /><stop offset="1" stop-color="#22d3ee" /></linearGradient></defs>
                </svg>
                <div class="relative">
                    <x-icon name="bolt" class="mx-auto h-8 w-8 animate-pulse text-emerald-300" :stroke="2.4" />
                    <p class="mt-1 text-6xl font-extrabold tracking-tight text-white" x-text="kwh.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })">0,00</p>
                    <p class="text-sm font-semibold text-slate-400">kWh terisi</p>
                </div>
            </div>

            <div class="relative mt-8 grid grid-cols-3 gap-3">
                <div class="rounded-2xl bg-white/[0.05] p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Durasi</p><p class="mt-1 font-mono text-lg font-extrabold text-white sm:text-xl" x-text="time">00:00:00</p></div>
                <div class="rounded-2xl bg-white/[0.05] p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Daya</p><p class="mt-1 text-lg font-extrabold text-sky-300 sm:text-xl"><span x-text="power">0</span> kW</p></div>
                <div class="rounded-2xl bg-white/[0.05] p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Biaya</p><p class="mt-1 text-lg font-extrabold text-amber-300 sm:text-xl" x-text="rupiah(cost)">Rp 0</p></div>
            </div>
            <div class="relative mt-6 h-2 overflow-hidden rounded-full bg-white/10"><div class="stripe-flow h-full w-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400"></div></div>
        </section>

        {{-- Info & aksi --}}
        <aside class="space-y-5 lg:col-span-2">
            <section class="card reveal p-6" style="--i:2">
                <h2 class="font-bold">Detail sesi</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">ID sesi</dt><dd class="font-bold text-white">#{{ $session->id_session }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Mulai</dt><dd class="font-bold text-white">{{ $session->waktu_mulai->format('d M, H:i') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Kendaraan</dt><dd class="font-bold text-white">{{ $v ? $v->merek . ' ' . $v->model : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Tarif</dt><dd class="font-bold text-white">Rp <span x-text="Math.round(rate).toLocaleString('id-ID')"></span>/kWh</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Saldo</dt><dd class="font-bold text-emerald-300" x-text="rupiah(balance)">-</dd></div>
                    @if ($endsAt)<div class="flex justify-between"><dt class="text-slate-500">Batas reservasi</dt><dd class="font-bold text-white">{{ $endsAt->format('H:i') }}</dd></div>@endif
                </dl>
                <div x-show="overstay > 0" x-cloak class="mt-4 rounded-xl border border-rose-400/30 bg-rose-400/10 p-3 text-xs text-rose-200">Melewati batas reservasi <b x-text="overstay"></b> menit. Denda overstay berlaku.</div>
            </section>

            <section class="card reveal p-6" style="--i:3">
                <p class="text-sm text-slate-400">Selesai mengisi? Hentikan sesi untuk menutup transaksi. Biaya dipotong otomatis dari saldo.</p>
                <x-confirm :action="route('driver.charging.stop', $session->id_session)" tone="warn" icon="stop" title="Hentikan pengisian?" text="Sesi akan diakhiri dan biaya final dipotong dari saldo Anda." confirm="Ya, hentikan" class="mt-4 !block">
                    <x-slot name="trigger"><button type="button" class="btn-danger btn-lg btn-block"><x-icon name="stop" class="h-5 w-5" />Hentikan pengisian</button></x-slot>
                </x-confirm>
            </section>
        </aside>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('telemetry', (cfg) => ({
                kwh: cfg.init.kwh, cost: cfg.init.total, power: cfg.init.power_kw, rate: cfg.init.rate, overstay: cfg.init.overstay_minutes,
                time: '00:00:00', balance: 0, low: false,
                init() { this.poll(); setInterval(() => this.poll(), 2000); },
                async poll() {
                    try {
                        const d = await (await fetch(cfg.url, { headers: { Accept: 'application/json' } })).json();
                        if (d.finished) { window.location.href = d.redirect; return; }
                        this.kwh = d.energy_kwh; this.cost = d.total_cost; this.power = d.power_kw; this.rate = d.rate;
                        this.time = d.formatted_time; this.balance = d.balance; this.low = d.low_balance; this.overstay = d.overstay_minutes;
                    } catch (e) {}
                },
            }));
        });
    </script>
    @endpush
</x-app-layout>