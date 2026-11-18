<x-app-layout title="Riwayat Transaksi">
    <x-page-header eyebrow="Aktivitas" title="Riwayat transaksi" subtitle="Seluruh sesi charging yang pernah Anda lakukan." />

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat label="Total sesi" :value="$summary['count']" icon="bolt" :i="1" />
        <x-stat label="Total energi" :value="$summary['kwh']" icon="battery" tone="sky" suffix=" kWh" :decimals="1" :i="2" />
        <x-stat label="Total biaya" :value="$summary['spend']" icon="wallet" tone="amber" prefix="Rp " :i="3" />
    </div>

    <section class="card reveal mt-6 overflow-hidden" style="--i:3">
        @if ($transactions->count())
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Waktu</th><th>Lokasi</th><th>Kendaraan</th><th class="text-right">Energi</th><th class="text-right">Biaya</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($transactions as $t)
                            <tr>
                                <td class="whitespace-nowrap"><p class="font-semibold text-white">{{ $t->waktu_mulai->format('d M Y') }}</p><p class="text-xs text-slate-500">{{ $t->waktu_mulai->format('H:i') }}</p></td>
                                <td><p class="font-semibold text-white">{{ $t->charger?->location?->nama_lokasi ?? '—' }}</p><p class="text-xs text-slate-500">Charger #{{ $t->id_charger }}</p></td>
                                <td class="whitespace-nowrap">{{ $t->reservation?->vehicle ? $t->reservation->vehicle->merek . ' ' . $t->reservation->vehicle->model : '—' }}</td>
                                <td class="whitespace-nowrap text-right">{{ number_format((float) $t->energi_kwh, 2, ',', '.') }} kWh</td>
                                <td class="whitespace-nowrap text-right font-bold text-white">Rp {{ number_format((float) $t->estimasi_biaya, 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$t->status" type="session" /></td>
                                <td class="text-right"><a href="{{ route('driver.charging.receipt', $t->id_session) }}" class="btn-secondary btn-sm">Struk</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($transactions->hasPages())<div class="border-t border-white/5 p-4">{{ $transactions->links() }}</div>@endif
        @else
            <x-empty icon="receipt" title="Belum ada transaksi" text="Sesi charging yang selesai akan tercatat di sini."><a href="{{ route('spklu.index') }}" class="btn-primary btn-sm">Cari SPKLU</a></x-empty>
        @endif
    </section>
</x-app-layout>