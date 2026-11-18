<x-app-layout title="Dashboard Admin">
    @php
        $maxRev = max(1, collect($revenue7)->max('value'));
        $seg = [['available', 'Tersedia', 'bg-emerald-400'], ['reserved', 'Dipesan', 'bg-amber-400'], ['occupied', 'Digunakan', 'bg-sky-400'], ['faulted', 'Rusak', 'bg-rose-400'], ['unavailable', 'Nonaktif', 'bg-slate-500']];
    @endphp

    <x-page-header eyebrow="Administrator" title="Ringkasan sistem" subtitle="Performa pendapatan, kondisi charger, dan aktivitas terbaru jaringan SPKLU.">
        <x-slot name="actions"><a href="{{ route('admin.stations.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" />Tambah stasiun</a></x-slot>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Pendapatan hari ini" :value="$stats['revenue_today']" prefix="Rp " icon="wallet" :i="1" :hint="$stats['sessions_today'] . ' sesi selesai'" />
        <x-stat label="Energi hari ini" :value="$stats['kwh_today']" suffix=" kWh" :decimals="1" icon="battery" tone="sky" :i="2" />
        <x-stat label="Sesi aktif" :value="$stats['active_sessions']" icon="bolt" tone="amber" :i="3" :hint="$stats['active_reservations'] . ' reservasi menahan slot'" />
        <x-stat label="Stasiun" :value="$stats['stations']" icon="building" tone="violet" :i="4" :hint="$stats['stations_open'] . ' beroperasi · ' . $stats['drivers'] . ' pengemudi'" />
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-3">
        {{-- Grafik pendapatan --}}
        <section class="card reveal p-6 lg:col-span-2" style="--i:3">
            <div class="flex items-center justify-between"><h2 class="font-bold">Pendapatan 7 hari terakhir</h2><span class="badge-slate">Rp {{ number_format(collect($revenue7)->sum('value'), 0, ',', '.') }}</span></div>
            <div class="mt-6 flex h-56 items-end gap-3 sm:gap-5">
                @foreach ($revenue7 as $i => $d)
                    <div class="group flex h-full flex-1 flex-col items-center justify-end gap-2">
                        <span class="text-[10px] font-bold text-slate-500 opacity-0 transition group-hover:opacity-100">{{ $d['value'] > 0 ? number_format($d['value'] / 1000, 0, ',', '.') . 'rb' : '0' }}</span>
                        <div class="w-full rounded-t-xl bg-gradient-to-t from-emerald-500/60 to-cyan-400 transition duration-300 group-hover:brightness-125" style="height: {{ max(3, $d['value'] / $maxRev * 100) }}%; animation: fade-up .8s {{ $i * 80 }}ms backwards"></div>
                        <span class="text-[11px] font-semibold text-slate-500">{{ $d['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Status charger --}}
        <section class="card reveal p-6" style="--i:4">
            <h2 class="font-bold">Status charger</h2>
            <p class="mt-1 text-xs text-slate-500">{{ $statusCounts['total'] }} port terdaftar</p>
            <div class="mt-5 flex h-3 overflow-hidden rounded-full bg-white/10">
                @foreach ($seg as [$k, $l, $c])@if ($statusCounts[$k] > 0)<div class="{{ $c }} transition-all duration-700" style="width: {{ $statusCounts[$k] / max(1, $statusCounts['total']) * 100 }}%"></div>@endif @endforeach
            </div>
            <ul class="mt-5 space-y-3">
                @foreach ($seg as [$k, $l, $c])
                    <li class="flex items-center justify-between text-sm"><span class="flex items-center gap-2.5 text-slate-300"><span class="h-2.5 w-2.5 rounded-full {{ $c }}"></span>{{ $l }}</span><b class="text-white">{{ $statusCounts[$k] }}</b></li>
                @endforeach
            </ul>
            @if ($faultedChargers->count())
                <div class="mt-5 rounded-xl border border-rose-400/25 bg-rose-400/10 p-3 text-xs text-rose-200"><p class="font-bold">Perlu perhatian</p>@foreach ($faultedChargers as $f)<p class="mt-1">#{{ $f->id_charger }} · {{ $f->location->nama_lokasi ?? '' }}</p>@endforeach</div>
            @endif
        </section>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <section class="card reveal lg:col-span-2" style="--i:4">
            <div class="card-head"><h2 class="font-bold">Transaksi terbaru</h2></div>
            @if ($recentSessions->count())
                <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Pengemudi</th><th>Lokasi</th><th class="text-right">Energi</th><th class="text-right">Biaya</th><th>Status</th></tr></thead><tbody>
                    @foreach ($recentSessions as $s)
                        <tr><td class="font-semibold text-white">{{ $s->user->name ?? '—' }}</td><td>{{ $s->charger?->location?->nama_lokasi }}</td><td class="whitespace-nowrap text-right">{{ number_format((float) $s->energi_kwh, 2, ',', '.') }} kWh</td><td class="whitespace-nowrap text-right font-bold text-white">Rp {{ number_format((float) $s->estimasi_biaya, 0, ',', '.') }}</td><td><x-status-badge :status="$s->status" type="session" /></td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <x-empty icon="receipt" title="Belum ada transaksi" />
            @endif
        </section>

        <div class="space-y-5">
            <section class="card reveal p-6" style="--i:5">
                <h2 class="font-bold">Kepuasan pengguna</h2>
                <div class="mt-4 space-y-4">
                    @foreach ([['Kondisi alat', $avgAlat, 'bg-sky-400'], ['Tempat & fasilitas', $avgTempat, 'bg-violet-400']] as [$l, $v, $c])
                        <div><div class="flex justify-between text-sm"><span class="text-slate-400">{{ $l }}</span><b class="text-white">{{ number_format($v, 1, ',', '.') }} / 5</b></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-white/10"><div class="h-full rounded-full {{ $c }} transition-all duration-1000" style="width: {{ $v / 5 * 100 }}%"></div></div></div>
                    @endforeach
                </div>
                <a href="{{ route('admin.reviews.index', ['filter' => 'unreplied']) }}" class="btn-secondary btn-sm mt-5 w-full">{{ $unreplied }} ulasan belum dibalas</a>
            </section>

            <section class="card reveal" style="--i:6">
                <div class="card-head"><h2 class="font-bold">Reservasi terbaru</h2></div>
                @forelse ($reservations->take(5) as $r)
                    <div class="flex items-center justify-between gap-3 border-b border-white/5 px-6 py-3 last:border-0"><div class="min-w-0"><p class="truncate text-sm font-bold text-white">{{ $r->user->name ?? '—' }}</p><p class="truncate text-xs text-slate-500">{{ $r->charger?->location?->nama_lokasi }} · #{{ $r->charger_id }}</p></div><x-status-badge :status="$r->status" type="reservation" /></div>
                @empty
                    <x-empty icon="calendar" title="Belum ada reservasi" class="!py-10" />
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>