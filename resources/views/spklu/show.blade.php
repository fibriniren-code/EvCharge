<x-app-layout :title="$location->nama_lokasi">
    @php
        $open = $location->isOpen();
        $myVehicles = auth()->user()->vehicles;
        $freeCount = $open ? $location->chargers->where('status', 'available')->count() : 0;
        $stars = fn ($v) => str_repeat('★', (int) round($v)) . str_repeat('☆', 5 - (int) round($v));
    @endphp

    <x-page-header :back="route('spklu.index')" backLabel="Kembali ke daftar SPKLU" :title="$location->nama_lokasi">
        <x-slot name="actions">
            <x-status-badge :status="$location->status" type="station" class="!px-3.5 !py-2 !text-xs" />
        </x-slot>
    </x-page-header>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            {{-- Info utama --}}
            <section class="card reveal overflow-hidden" style="--i:1">
                <div class="relative bg-gradient-to-br from-emerald-400/15 via-transparent to-cyan-400/10 p-6">
                    <p class="flex items-start gap-2 text-sm text-slate-300"><x-icon name="pin" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-300" />{{ $location->alamat }}</p>
                    @if ($location->detail_area)<p class="mt-2 pl-6 text-xs text-slate-500">{{ $location->detail_area }}</p>@endif
                    @if (count($location->fasilitas_list))
                        <div class="mt-4 flex flex-wrap gap-2">@foreach ($location->fasilitas_list as $f)<span class="badge-slate">{{ $f }}</span>@endforeach</div>
                    @endif
                </div>
                <div class="grid grid-cols-2 divide-x divide-y divide-white/5 border-t border-white/5 sm:grid-cols-4 sm:divide-y-0">
                    <div class="p-5"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tarif</p><p class="mt-1 text-lg font-extrabold text-emerald-300">{{ $tariff ? 'Rp ' . number_format($tariff->harga_per_kwh, 0, ',', '.') : '—' }}<span class="text-xs font-semibold text-slate-500"> /kWh</span></p></div>
                    <div class="p-5"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jam buka</p><p class="mt-1 text-lg font-extrabold text-white">{{ $location->jam_operasional ?? '—' }}</p></div>
                    <div class="p-5"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Daya maks</p><p class="mt-1 text-lg font-extrabold text-white">{{ (float) $location->chargers->max('power_kw') }}<span class="text-xs font-semibold text-slate-500"> kW</span></p></div>
                    <div class="p-5"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tersedia</p><p class="mt-1 text-lg font-extrabold {{ $freeCount ? 'text-emerald-300' : 'text-slate-400' }}">{{ $freeCount }}<span class="text-xs font-semibold text-slate-500"> / {{ $location->chargers->count() }}</span></p></div>
                </div>
                @if ($tariff && ((float) $tariff->biaya_layanan > 0 || (float) $tariff->biaya_minimum > 0 || (float) $tariff->overstay_fee_per_menit > 0))
                    <p class="border-t border-white/5 px-6 py-3 text-xs text-slate-500">
                        @if ((float) $tariff->biaya_layanan > 0)Biaya layanan Rp {{ number_format($tariff->biaya_layanan, 0, ',', '.') }} · @endif
                        @if ((float) $tariff->biaya_minimum > 0)Minimum Rp {{ number_format($tariff->biaya_minimum, 0, ',', '.') }} · @endif
                        @if ((float) $tariff->overstay_fee_per_menit > 0)Denda overstay Rp {{ number_format($tariff->overstay_fee_per_menit, 0, ',', '.') }}/menit @endif
                    </p>
                @endif
            </section>

            {{-- Charger --}}
            <section class="card reveal" style="--i:2">
                <div class="card-head"><h2 class="font-bold">Charger ({{ $location->chargers->count() }})</h2><a href="{{ route('driver.scan') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">Sudah punya reservasi? Scan QR →</a></div>
                <div class="divide-y divide-white/5">
                    @forelse ($location->chargers as $charger)
                        @php
                            $free = $open && $charger->status === 'available';
                            $compat = $myVehicles->isEmpty() ? null : $myVehicles->contains(fn ($v) => $v->isCompatibleWith($charger));
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-4 p-5">
                            <div class="flex items-center gap-4">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl {{ $free ? 'bg-emerald-400/10 text-emerald-300' : 'bg-white/5 text-slate-500' }}"><x-icon name="plug" class="h-6 w-6" /></span>
                                <div>
                                    <p class="font-bold text-white">Charger #{{ $charger->id_charger }} <span class="font-medium text-slate-400">· {{ $charger->charger_type }}</span></p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ (float) $charger->power_kw }} kW @if ($compat === false)· <span class="text-amber-300">tidak cocok dengan mobil Anda</span>@endif</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$open ? $charger->status : 'unavailable'" />
                                @if ($free)
                                    <a href="{{ route('reservations.create', ['location' => $location->id_location, 'charger' => $charger->id_charger]) }}" class="btn-primary btn-sm">Pesan slot</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <x-empty icon="plug" title="Belum ada charger" text="Stasiun ini belum memiliki charger terdaftar." />
                    @endforelse
                </div>
            </section>

            {{-- Ulasan --}}
            <section class="card reveal" style="--i:3">
                <div class="card-head">
                    <h2 class="font-bold">Ulasan pengemudi <span class="text-slate-500">({{ $reviewCount }})</span></h2>
                    <a href="{{ route('reviews.create', $location->id_location) }}" class="btn-secondary btn-sm"><x-icon name="star" class="h-3.5 w-3.5" />Beri ulasan</a>
                </div>
                @if ($reviewCount)
                    <div class="grid gap-4 border-b border-white/5 p-5 sm:grid-cols-2">
                        <div><p class="text-xs text-slate-500">Kondisi alat</p><p class="mt-1 text-amber-300"><span class="text-lg">{{ $stars($avgAlat) }}</span> <b class="text-white">{{ number_format($avgAlat, 1, ',', '.') }}</b></p></div>
                        <div><p class="text-xs text-slate-500">Tempat &amp; fasilitas</p><p class="mt-1 text-amber-300"><span class="text-lg">{{ $stars($avgTempat) }}</span> <b class="text-white">{{ number_format($avgTempat, 1, ',', '.') }}</b></p></div>
                    </div>
                @endif
                @forelse ($reviews as $r)
                    <div class="border-b border-white/5 p-5 last:border-0">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold text-white">{{ $r->user->name ?? 'Pengemudi' }}</p>
                            <p class="text-xs text-slate-500">{{ $r->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="mt-1 text-sm text-amber-300">{{ $stars(($r->rating_alat + $r->rating_tempat) / 2) }}</p>
                        @if ($r->ulasan_alat)<p class="mt-2 text-sm text-slate-300"><span class="text-slate-500">Alat:</span> {{ $r->ulasan_alat }}</p>@endif
                        @if ($r->ulasan_tempat)<p class="mt-1 text-sm text-slate-300"><span class="text-slate-500">Tempat:</span> {{ $r->ulasan_tempat }}</p>@endif
                        @if ($r->balasan_operator)
                            <div class="mt-3 rounded-xl border border-emerald-400/20 bg-emerald-400/[0.06] p-3 text-sm"><p class="text-[11px] font-bold uppercase tracking-wider text-emerald-300">Tanggapan operator</p><p class="mt-1 text-slate-300">{{ $r->balasan_operator }}</p></div>
                        @endif
                    </div>
                @empty
                    <x-empty icon="star" title="Belum ada ulasan" text="Jadilah yang pertama memberi ulasan untuk stasiun ini." />
                @endforelse
            </section>
        </div>

        {{-- Sidebar kanan --}}
        <aside class="space-y-5">
            <section class="card reveal overflow-hidden" style="--i:2">
                <div id="mini-map" class="h-64 w-full"></div>
                <div class="grid gap-2 p-4">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $location->latitude }},{{ $location->longitude }}" target="_blank" rel="noopener" class="btn-primary btn-block"><x-icon name="navigation" class="h-4 w-4" />Navigasi Google Maps</a>
                    <a href="{{ route('spklu.index') }}" class="btn-secondary btn-block"><x-icon name="pin" class="h-4 w-4" />Lihat di peta SPKLU</a>
                </div>
            </section>
            @if ($myVehicles->isEmpty())
                <section class="card reveal border-amber-400/30 p-5" style="--i:3">
                    <p class="flex items-center gap-2 text-sm font-bold text-amber-300"><x-icon name="car" class="h-4 w-4" />Belum ada kendaraan</p>
                    <p class="mt-1 text-xs text-slate-400">Daftarkan mobil listrik Anda agar bisa memesan slot.</p>
                    <a href="{{ route('cars.create') }}" class="btn-warn btn-sm mt-3">Tambah mobil</a>
                </section>
            @endif
        </aside>
    </div>

    @push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const el = document.getElementById('mini-map');
            if (!el || typeof L === 'undefined') return;
            const pos = [{{ $location->latitude }}, {{ $location->longitude }}];
            const map = L.map(el, { scrollWheelZoom: false }).setView(pos, 15);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, className: 'map-tiles-dark', attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
            L.marker(pos, { icon: L.divIcon({ className: 'ev-pin-wrap', html: '<div class="ev-pin {{ $open ? '' : 'closed' }}"><span>⚡</span></div>', iconSize: [36, 36], iconAnchor: [18, 36] }) }).addTo(map);
        })();
    </script>
    @endpush
</x-app-layout>