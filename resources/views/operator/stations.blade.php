<x-app-layout title="Stasiun & Port">
    <x-page-header eyebrow="Operator" title="Stasiun & port" subtitle="Buka/tutup stasiun dan ubah status port: tersedia, nonaktif, atau rusak." />

    <div class="space-y-6">
        @forelse ($stations as $st)
            @php
                $tariff = $st->tariffs->where('periode_berlaku', '<=', now())->sortByDesc('periode_berlaku')->first();
            @endphp

            <section id="station-{{ $st->id_location }}" class="card reveal scroll-mt-24 overflow-hidden" style="--i: {{ min($loop->index + 1, 6) }}">
                <div class="card-head">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold text-white">{{ $st->nama_lokasi }}</h2>
                            <x-status-badge :status="$st->status" type="station" />
                        </div>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ $st->alamat }} ·
                            @if ($tariff) Rp {{ number_format($tariff->harga_per_kwh, 0, ',', '.') }}/kWh @else Belum ada tarif @endif
                        </p>
                    </div>

                    @if ($st->isOpen())
                        <x-confirm :action="route('operator.stations.toggle', $st)" method="PATCH" tone="warn" icon="power"
                                   title="Tutup {{ $st->nama_lokasi }}?"
                                   text="Reservasi aktif di stasiun ini akan dibatalkan. Sesi charging yang sedang berjalan tidak terganggu."
                                   confirm="Ya, tutup stasiun">
                            <x-slot name="trigger"><button type="button" class="btn-soft-danger btn-sm"><x-icon name="power" class="h-3.5 w-3.5" />Tutup stasiun</button></x-slot>
                        </x-confirm>
                    @else
                        <form method="POST" action="{{ route('operator.stations.toggle', $st) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-soft-emerald btn-sm"><x-icon name="power" class="h-3.5 w-3.5" />Buka stasiun</button>
                        </form>
                    @endif
                </div>

                <div class="divide-y divide-white/5">
                    @forelse ($st->chargers as $c)
                        @php
                            $run = $c->runningSession;
                            $busy = $c->status === 'occupied' || $run;
                        @endphp

                        <div id="charger-{{ $c->id_charger }}" class="flex scroll-mt-24 flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-6">
                            <div class="flex min-w-0 items-center gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-sm font-extrabold text-white">#{{ $c->id_charger }}</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-white">{{ $c->charger_type }} · {{ (float) $c->power_kw }} kW</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <x-status-badge :status="$c->status" />
                                        @if ($run)<span class="text-xs text-slate-400">dipakai {{ $run->user->name ?? 'pengemudi' }}</span>@endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if ($busy)
                                    <a href="{{ route('operator.sessions.index') }}" class="btn-secondary btn-sm"><x-icon name="stop" class="h-3.5 w-3.5" />Hentikan sesi dulu</a>
                                @else
                                    @if (! in_array($c->status, ['available', 'reserved'], true))
                                        <form method="POST" action="{{ route('operator.chargers.status', $c) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="available">
                                            <button type="submit" class="btn-soft-emerald btn-sm"><x-icon name="check" class="h-3.5 w-3.5" />Aktifkan</button>
                                        </form>
                                    @endif
                                    @if ($c->status !== 'unavailable')
                                        <form method="POST" action="{{ route('operator.chargers.status', $c) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="unavailable">
                                            <button type="submit" class="btn-secondary btn-sm"><x-icon name="power" class="h-3.5 w-3.5" />Nonaktifkan</button>
                                        </form>
                                    @endif
                                    @if ($c->status !== 'faulted')
                                        <form method="POST" action="{{ route('operator.chargers.status', $c) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="faulted">
                                            <button type="submit" class="btn-soft-danger btn-sm"><x-icon name="alert" class="h-3.5 w-3.5" />Tandai rusak</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <x-empty icon="plug" title="Belum ada port" text="Port ditambahkan oleh administrator." class="py-10" />
                    @endforelse
                </div>
            </section>
        @empty
            <div class="card"><x-empty icon="building" title="Belum ada stasiun" text="Stasiun dibuat oleh administrator." /></div>
        @endforelse
    </div>
</x-app-layout>