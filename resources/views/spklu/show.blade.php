<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Detail SPKLU') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-slate-950 min-h-screen text-slate-100">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Tombol Kembali -->
            <div>
                <a href="{{ route('spklu.index') }}" class="inline-flex items-center text-sm text-blue-400 hover:text-blue-300 transition">
                    ← Kembali ke Daftar SPKLU
                </a>
            </div>

            @php
                $isAktif = $location->status === 'aktif';
                $mapQuery = $location->latitude . ',' . $location->longitude;
            @endphp

            <!-- Card Informasi Utama -->
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl shadow-lg space-y-6">

                <!-- Header Info -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-800 pb-4 gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $location->nama_lokasi }}</h1>
                        <p class="text-sm text-slate-400 mt-1">📍 {{ $location->alamat }}</p>
                    </div>
                    <div>
                        @if($isAktif)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Beroperasi</span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20">Ditutup Sementara</span>
                        @endif
                    </div>
                </div>

                <!-- Spesifikasi & Detail -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-slate-800/50 p-4 rounded-lg border border-slate-800">
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jenis Charger</h3>
                        <p class="text-lg font-bold text-white mt-1">
                            {{ $location->chargers->pluck('charger_type')->unique()->implode(', ') ?: '-' }}
                        </p>
                    </div>

                    <div class="bg-slate-800/50 p-4 rounded-lg border border-slate-800">
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tarif Pengisian Daya</h3>
                        <p class="text-lg font-bold text-blue-400 mt-1">
                            @if($tariff)
                                Rp {{ number_format($tariff->harga_per_kwh, 0, ',', '.') }} / kWh
                            @else
                                Belum ditetapkan
                            @endif
                        </p>
                    </div>

                    <div class="bg-slate-800/50 p-4 rounded-lg border border-slate-800">
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jam Operasional</h3>
                        <p class="text-lg font-bold text-white mt-1">{{ $location->jam_operasional ?? '-' }}</p>
                    </div>
                </div>

                <!-- Detail Area -->
                <div>
                    <h3 class="text-sm font-semibold text-slate-300 mb-2">Detail Area</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        {{ $location->detail_area ?: 'Tidak ada keterangan area tambahan untuk lokasi SPKLU ini.' }}
                    </p>
                </div>

                <!-- Daftar Charger -->
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-300">Daftar Charger</h3>

                    @forelse($location->chargers as $charger)
                        <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-slate-950 border border-slate-800 rounded-lg">
                            <div>
                                <p class="text-sm font-bold text-white">Charger #{{ $charger->id_charger }} ({{ $charger->charger_type }})</p>
                                <p class="text-xs text-slate-400">Daya: {{ (float) $charger->power_kw }} kW</p>
                            </div>

                            @if($isAktif && $charger->status === 'available')
                                <a href="{{ route('reservations.create', ['location' => $location->id_location, 'charger' => $charger->id_charger]) }}"
                                   class="px-4 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg transition">
                                    Pesan Slot
                                </a>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-800 text-slate-400 border border-slate-700">
                                    {{ $isAktif ? 'Tidak Tersedia' : 'Stasiun Ditutup' }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada charger terdaftar di stasiun ini.</p>
                    @endforelse
                </div>

                <!-- PETA GOOGLE MAPS EMBED -->
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-slate-300">Peta Lokasi SPKLU</h3>
                    <div class="w-full h-72 rounded-lg overflow-hidden border border-slate-800">
                        <iframe
                            class="w-full h-full border-0"
                            loading="lazy"
                            allowfullscreen
                            src="https://maps.google.com/maps?q={{ $mapQuery }}&hl=id&z=15&output=embed">
                        </iframe>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="pt-4 border-t border-slate-800 flex flex-wrap gap-3">
                    <a href="{{ route('driver.scan') }}" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-lg text-sm shadow transition duration-150 flex items-center gap-2">
                        📷 Scan QR &amp; Mulai Charging
                    </a>

                    <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium rounded-lg text-sm transition duration-150 border border-slate-700 flex items-center gap-2">
                        🗺️ Navigasi Google Maps
                    </a>

                    <a href="{{ route('reviews.create', $location->id_location) }}"
                       class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium rounded-lg text-sm transition duration-150 border border-slate-700 flex items-center gap-2">
                        ⭐ Beri Ulasan
                    </a>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
