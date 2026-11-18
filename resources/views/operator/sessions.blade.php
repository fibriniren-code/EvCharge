@php
    $dur = fn (int $s) => sprintf('%02d:%02d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60);
@endphp

<x-app-layout title="Sesi Charging">
    <x-page-header eyebrow="Operator" title="Sesi charging" subtitle="Pantau sesi yang berjalan dan lakukan henti darurat bila diperlukan." />

    {{-- Sesi berjalan (disegarkan otomatis tiap 6 detik) --}}
    <section class="card reveal overflow-hidden" style="--i:1">
        <div class="card-head">
            <h2 class="text-sm font-bold text-white">Sedang berjalan</h2>
            <span class="badge-sky">{{ $running->count() }} aktif</span>
        </div>

        <div data-live data-live-id="running" data-interval="6000">
            @if ($running->count())
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Sesi</th><th>Pengemudi</th><th>Kendaraan</th><th>Stasiun · Port</th><th>Durasi</th><th>Energi</th><th>Estimasi biaya</th><th class="text-right">Aksi</th></tr></thead>
                        <tbody>
                            @foreach ($running as $s)
                                @php
                                    $bill = $s->computeBill();
                                    $v = $s->reservation?->vehicle;
                                @endphp
                                <tr>
                                    <td class="font-bold text-white">#{{ $s->id_session }}</td>
                                    <td>{{ $s->user->name ?? '—' }}</td>
                                    <td class="text-xs">@if ($v){{ $v->merek }} {{ $v->model }}<br><span class="text-slate-500">{{ $v->nomor_polisi }}</span>@else — @endif</td>
                                    <td>{{ $s->charger->location->nama_lokasi ?? '—' }} · <span class="text-slate-500">#{{ $s->id_charger }}</span></td>
                                    <td class="whitespace-nowrap font-mono text-sky-300">{{ $dur($s->durationSeconds()) }}</td>
                                    <td class="whitespace-nowrap">{{ number_format($bill['kwh'], 2, ',', '.') }} kWh</td>
                                    <td class="whitespace-nowrap">Rp {{ number_format($bill['total'], 0, ',', '.') }}</td>
                                    <td>
                                        <div class="flex justify-end">
                                            <x-confirm :action="route('operator.sessions.stop', $s->id_session)"
                                                       title="Hentikan sesi #{{ $s->id_session }}?"
                                                       text="Sesi dihentikan sekarang. Pengemudi hanya ditagih sebatas energi yang sudah tersalurkan."
                                                       confirm="Ya, hentikan sesi" icon="stop">
                                                <x-slot name="trigger"><button type="button" class="btn-soft-danger btn-sm"><x-icon name="stop" class="h-3.5 w-3.5" />Henti darurat</button></x-slot>
                                            </x-confirm>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty icon="bolt" title="Tidak ada sesi berjalan" text="Sesi pengemudi yang sedang mengisi daya akan tampil di sini." />
            @endif
        </div>
    </section>

    {{-- Riwayat terbaru --}}
    <section class="card reveal mt-6 overflow-hidden" style="--i:2">
        <div class="card-head"><h2 class="text-sm font-bold text-white">Riwayat terbaru</h2></div>

        @if ($recent->count())
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Sesi</th><th>Pengemudi</th><th>Stasiun · Port</th><th>Mulai</th><th>Durasi</th><th>Energi</th><th>Biaya</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($recent as $s)
                            <tr>
                                <td class="font-bold text-white">#{{ $s->id_session }}</td>
                                <td>{{ $s->user->name ?? '—' }}</td>
                                <td>{{ $s->charger->location->nama_lokasi ?? '—' }} · <span class="text-slate-500">#{{ $s->id_charger }}</span></td>
                                <td class="whitespace-nowrap text-xs">{{ $s->waktu_mulai?->format('d M Y, H:i') }}</td>
                                <td class="whitespace-nowrap font-mono text-xs">{{ $dur($s->durationSeconds()) }}</td>
                                <td class="whitespace-nowrap">{{ number_format((float) $s->energi_kwh, 2, ',', '.') }} kWh</td>
                                <td class="whitespace-nowrap">Rp {{ number_format((float) $s->estimasi_biaya, 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$s->status" type="session" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty icon="receipt" title="Belum ada riwayat" text="Sesi yang selesai atau dihentikan akan tercatat di sini." />
        @endif
    </section>
</x-app-layout>