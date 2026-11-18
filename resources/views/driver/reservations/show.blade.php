<x-app-layout :title="'Reservasi #' . $reservation->id_reservation">
    @php
        $r = $reservation;
        $status = $r->status;
        $location = $r->charger?->location;
        $session = $r->chargingSession;
        $holding = in_array($status, ['pending', 'confirmed'], true);
        $failed = in_array($status, ['cancelled', 'expired'], true);
        $stepIndex = ['pending' => 0, 'confirmed' => 1, 'active' => 2, 'completed' => 3][$status] ?? -1;
        $steps = ['Dibuat', 'Dikonfirmasi', 'Charging', 'Selesai'];
        $balance = (float) auth()->user()->balance;
        $remaining = $holding && $r->expires_at ? max(0, (int) now()->diffInSeconds($r->expires_at, false)) : 0;
        $total = $status === 'pending' ? \App\Http\Controllers\ReservationController::HOLD_HOURS * 3600 : max(1, (int) $r->created_at->diffInSeconds($r->waktu_selesai, true));
    @endphp

    <x-page-header :back="route('reservations.index')" backLabel="Semua reservasi" eyebrow="Reservasi" :title="'Reservasi #' . $r->id_reservation">
        <x-slot name="actions"><x-status-badge :status="$status" type="reservation" class="!px-3.5 !py-2 !text-xs" /></x-slot>
    </x-page-header>

    {{-- Stepper --}}
    @if (! $failed)
        <div class="card reveal mb-5 p-5 sm:p-6" style="--i:1">
            <ol class="relative grid grid-cols-4">
                @foreach ($steps as $i => $label)
                    @php $done = $i < $stepIndex; $now = $i === $stepIndex; @endphp
                    <li class="relative flex flex-col items-center text-center">
                        @if ($i > 0)<span class="absolute right-1/2 top-4 h-0.5 w-full {{ $i <= $stepIndex ? 'bg-gradient-to-r from-emerald-400 to-cyan-400' : 'bg-white/10' }}"></span>@endif
                        <span class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full border text-xs font-extrabold transition {{ $done ? 'border-transparent bg-gradient-to-br from-emerald-400 to-cyan-400 text-ink-950' : ($now ? 'border-emerald-400 bg-ink-900 text-emerald-300 shadow-glow' : 'border-white/10 bg-ink-900 text-slate-600') }}">
                            @if ($done)<x-icon name="check" class="h-4 w-4" :stroke="3" />@else{{ $i + 1 }}@endif
                        </span>
                        <span class="mt-2 text-[11px] font-bold {{ $now ? 'text-emerald-300' : ($done ? 'text-slate-300' : 'text-slate-600') }}">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    @else
        <div class="reveal mb-5 flex items-start gap-3 rounded-2xl border border-rose-400/30 bg-rose-400/10 p-4 text-rose-200" style="--i:1">
            <x-icon name="x-circle" class="mt-0.5 h-5 w-5 shrink-0" />
            <p class="text-sm font-semibold">{{ $status === 'expired' ? 'Reservasi kedaluwarsa karena tidak dikonfirmasi tepat waktu atau jadwal terlewat.' : 'Reservasi ini telah dibatalkan.' }} Port sudah dilepas untuk pengemudi lain.</p>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            {{-- Detail --}}
            <section class="card reveal overflow-hidden" style="--i:2">
                <div class="bg-gradient-to-br from-emerald-400/10 to-transparent p-6">
                    <h2 class="text-lg font-bold">{{ $location->nama_lokasi ?? 'SPKLU' }}</h2>
                    <p class="mt-1 flex items-start gap-2 text-sm text-slate-400"><x-icon name="pin" class="mt-0.5 h-4 w-4 shrink-0" />{{ $location->alamat ?? '-' }}</p>
                </div>
                <dl class="grid grid-cols-2 divide-white/5 border-t border-white/5 sm:grid-cols-4 sm:divide-x">
                    <div class="p-5"><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Charger</dt><dd class="mt-1 text-sm font-bold text-white">#{{ $r->charger_id }}</dd><dd class="text-xs text-slate-500">{{ $r->charger->charger_type ?? '' }}</dd></div>
                    <div class="p-5"><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Daya</dt><dd class="mt-1 text-sm font-bold text-white">{{ (float) ($r->charger->power_kw ?? 0) }} kW</dd></div>
                    <div class="p-5"><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kendaraan</dt><dd class="mt-1 text-sm font-bold text-white">{{ $r->vehicle ? $r->vehicle->merek . ' ' . $r->vehicle->model : '—' }}</dd><dd class="text-xs text-slate-500">{{ $r->vehicle->nomor_polisi ?? '' }}</dd></div>
                    <div class="p-5"><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jadwal</dt><dd class="mt-1 text-sm font-bold text-white">{{ $r->waktu_mulai->format('d M, H:i') }}</dd><dd class="text-xs text-slate-500">s/d {{ $r->waktu_selesai->format('H:i') }}</dd></div>
                </dl>
                @if ($tariff)
                    <p class="border-t border-white/5 px-6 py-3 text-xs text-slate-500">Tarif Rp {{ number_format($tariff->harga_per_kwh, 0, ',', '.') }}/kWh. Melewati jam selesai dikenakan denda Rp {{ number_format($tariff->overstay_fee_per_menit, 0, ',', '.') }}/menit.</p>
                @endif
            </section>

            {{-- Aksi --}}
            <section class="card reveal p-6" style="--i:3">
                @if ($status === 'pending')
                    <h3 class="font-bold">Konfirmasi reservasi Anda</h3>
                    <p class="mt-1 text-sm text-slate-400">Setelah dikonfirmasi, slot ditahan sampai {{ $r->waktu_selesai->format('H:i') }} dan Anda bisa scan QR untuk memulai.</p>
                    @if ($balance < $minBalance)
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-400/30 bg-amber-400/10 p-3 text-sm text-amber-200"><span>Saldo minimal Rp {{ number_format($minBalance, 0, ',', '.') }} dibutuhkan.</span><a href="{{ route('topup.index') }}" class="btn-warn btn-sm">Top up</a></div>
                    @endif
                    <div class="mt-5 flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('reservations.confirm', $r->id_reservation) }}">@csrf<button class="btn-primary btn-lg"><x-icon name="check" class="h-5 w-5" />Konfirmasi reservasi</button></form>
                        <x-confirm :action="route('reservations.cancel', $r->id_reservation)" title="Batalkan reservasi?" text="Slot akan dilepas dan bisa dipesan orang lain." confirm="Ya, batalkan">
                            <x-slot name="trigger"><button type="button" class="btn-soft-danger btn-lg">Batalkan</button></x-slot>
                        </x-confirm>
                    </div>
                @elseif ($status === 'confirmed')
                    <h3 class="font-bold">Reservasi terkonfirmasi</h3>
                    <p class="mt-1 text-sm text-slate-400">Datang ke stasiun, lalu scan QR di charger atau masukkan ID booking untuk memulai.</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('driver.scan', ['booking_id' => $r->id_reservation]) }}" class="btn-primary btn-lg"><x-icon name="scan" class="h-5 w-5" />Scan &amp; mulai charging</a>
                        <x-confirm :action="route('reservations.cancel', $r->id_reservation)" title="Batalkan reservasi?" text="Slot akan dilepas dan bisa dipesan orang lain." confirm="Ya, batalkan">
                            <x-slot name="trigger"><button type="button" class="btn-soft-danger btn-lg">Batalkan</button></x-slot>
                        </x-confirm>
                    </div>
                @elseif ($status === 'active')
                    <h3 class="font-bold">Sedang mengisi daya</h3>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if ($session)<a href="{{ route('driver.charging.monitor', $session->id_session) }}" class="btn-primary btn-lg"><x-icon name="bolt" class="h-5 w-5" />Pantau charging</a>@endif
                    </div>
                @elseif ($status === 'completed')
                    <h3 class="font-bold">Pengisian selesai</h3>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if ($session)<a href="{{ route('driver.charging.receipt', $session->id_session) }}" class="btn-primary"><x-icon name="receipt" class="h-4 w-4" />Lihat struk</a>@endif
                        <a href="{{ route('reservations.index') }}" class="btn-secondary">Semua reservasi</a>
                    </div>
                @else
                    <div class="flex flex-wrap gap-3">
                        @if ($location)<a href="{{ route('spklu.show', $location->id_location) }}" class="btn-primary">Pesan ulang</a>@endif
                        <a href="{{ route('reservations.index') }}" class="btn-secondary">Semua reservasi</a>
                    </div>
                @endif
            </section>
        </div>

        {{-- Countdown --}}
        <aside>
            @if ($holding)
                <section class="card reveal p-6 text-center" style="--i:3"
                         x-data="countdown({ remaining: {{ $remaining }}, total: {{ $total }}, url: '{{ route('reservations.timer', $r->id_reservation) }}', status: '{{ $status }}' })">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">{{ $status === 'pending' ? 'Konfirmasi dalam' : 'Slot ditahan hingga' }}</p>
                    <div class="relative mx-auto my-5 flex h-44 w-44 items-center justify-center">
                        <svg viewBox="0 0 120 120" class="absolute inset-0 -rotate-90">
                            <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="8" />
                            <circle cx="60" cy="60" r="52" fill="none" stroke-width="8" stroke-linecap="round" stroke-dasharray="326.7" :stroke-dashoffset="326.7 * (1 - pct)" :stroke="pct < .15 ? '#fb7185' : (pct < .4 ? '#fbbf24' : '#34d399')" class="transition-all duration-1000 ease-linear" />
                        </svg>
                        <div><p class="font-mono text-3xl font-extrabold text-white" x-text="label">--:--:--</p><p class="mt-1 text-[11px] text-slate-500">sisa waktu</p></div>
                    </div>
                    <p class="text-xs text-slate-500">Berakhir {{ $r->expires_at->format('d M, H:i') }}</p>
                </section>
            @endif
        </aside>
    </div>

    @if ($holding)
        @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('countdown', (cfg) => ({
                    remaining: cfg.remaining, total: cfg.total,
                    get pct() { return Math.max(0, Math.min(1, this.remaining / this.total)); },
                    get label() {
                        const s = Math.max(0, this.remaining), p = n => String(n).padStart(2, '0');
                        return p(Math.floor(s / 3600)) + ':' + p(Math.floor(s % 3600 / 60)) + ':' + p(s % 60);
                    },
                    init() {
                        setInterval(() => {
                            this.remaining = Math.max(0, this.remaining - 1);
                            if (this.remaining === 0) setTimeout(() => location.reload(), 1200);
                        }, 1000);
                        setInterval(async () => {
                            try {
                                const d = await (await fetch(cfg.url, { headers: { Accept: 'application/json' } })).json();
                                this.remaining = d.remaining_seconds;
                                if (d.status !== cfg.status) location.reload();
                            } catch (e) {}
                        }, 20000);
                    },
                }));
            });
        </script>
        @endpush
    @endif
</x-app-layout>