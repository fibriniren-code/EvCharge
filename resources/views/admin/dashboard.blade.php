<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-wide">
                    {{ __('Admin Dashboard') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Pantau status stasiun SPKLU, reservasi, dan aktivitas pengisian daya secara real-time.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-6">
            
            {{-- 1. KARTU STATISTIK CHARGER --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 p-5 rounded-2xl shadow-lg">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Charger</p>
                    <p class="text-3xl font-extrabold text-white mt-2">{{ $statusCounts['total'] ?? 0 }}</p>
                </div>
                <div class="bg-emerald-950/40 backdrop-blur-md border border-emerald-500/30 p-5 rounded-2xl shadow-lg">
                    <p class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Tersedia (Available)</p>
                    <p class="text-3xl font-extrabold text-emerald-400 mt-2">{{ $statusCounts['available'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-950/40 backdrop-blur-md border border-blue-500/30 p-5 rounded-2xl shadow-lg">
                    <p class="text-xs font-semibold text-blue-400 uppercase tracking-wider">Digunakan (Occupied)</p>
                    <p class="text-3xl font-extrabold text-blue-400 mt-2">{{ $statusCounts['occupied'] ?? 0 }}</p>
                </div>
                <div class="bg-rose-950/40 backdrop-blur-md border border-rose-500/30 p-5 rounded-2xl shadow-lg">
                    <p class="text-xs font-semibold text-rose-400 uppercase tracking-wider">Rusak (Faulted)</p>
                    <p class="text-3xl font-extrabold text-rose-400 mt-2">{{ $statusCounts['faulted'] ?? 0 }}</p>
                </div>
                <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/40 p-5 rounded-2xl shadow-lg">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Non-Aktif (Unavailable)</p>
                    <p class="text-3xl font-extrabold text-slate-400 mt-2">{{ $statusCounts['unavailable'] ?? 0 }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- 2. TABEL SESI PENGISIAN DAYA AKTIF --}}
                <div class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-5 border-b border-slate-700/60 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-bold text-white">Sesi Pengisian Daya Aktif</h3>
                            <p class="text-xs text-slate-400">Kendaraan yang sedang mengisi daya saat ini.</p>
                        </div>
                        <span class="bg-blue-500/20 text-blue-300 border border-blue-500/30 text-xs font-medium px-3 py-1 rounded-full">
                            {{ count($activeSessions ?? []) }} Sesi
                        </span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-300">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-900/60 border-b border-slate-700/60">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Pengguna</th>
                                    <th scope="col" class="px-4 py-3">ID Charger / Lokasi</th>
                                    <th scope="col" class="px-4 py-3">Energi</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/40">
                                @forelse($activeSessions ?? [] as $session)
                                    @php
                                        $dbEnergy = (float) ($session->energy_kwh ?? $session->energy_consumed ?? 0);
                                        if ($dbEnergy <= 0) {
                                            $startTime = \Carbon\Carbon::parse($session->start_time ?? $session->waktu_mulai ?? $session->created_at ?? now());
                                            $secondsElapsed = max(1, $startTime->diffInSeconds(now()));
                                            $powerKw = $session->charger->power_kw ?? 22;
                                            $calculatedEnergy = round(($powerKw / 3600) * $secondsElapsed, 2);
                                        } else {
                                            $calculatedEnergy = $dbEnergy;
                                        }
                                    @endphp
                                    <tr class="hover:bg-slate-700/30 transition">
                                        <td class="px-4 py-3 font-medium text-white">
                                            {{ $session->user->name ?? $session->user->email ?? 'Driver' }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-slate-300">
                                            {{ $session->charger->name ?? $session->charger->location->name ?? ('Charger #'.($session->charger->id_charger ?? $session->charger_id ?? '-')) }}
                                        </td>
                                        <td class="px-4 py-3 font-bold text-emerald-400">
                                            {{ number_format($calculatedEnergy, 2) }} kWh
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                                Mengisi Daya
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 text-xs">
                                            Tidak ada sesi pengisian daya yang aktif saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. TABEL MONITORING RESERVASI (HOLDING SLOT 3 JAM) --}}
                <div class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-5 border-b border-slate-700/60 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-bold text-white">Monitoring Reservasi</h3>
                            <p class="text-xs text-slate-400">Status pesanan tempat pengisian pengemudi.</p>
                        </div>
                        <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-medium px-3 py-1 rounded-full">
                            {{ count($activeReservations ?? []) }} Data
                        </span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-300">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-900/60 border-b border-slate-700/60">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Pengguna</th>
                                    <th scope="col" class="px-4 py-3">ID Charger / Lokasi</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/40">
                                @forelse($activeReservations ?? [] as $reservation)
                                    @php
                                        $statusStr = strtolower($reservation->status);
                                        $createdAt = \Carbon\Carbon::parse($reservation->created_at);
                                        $expiresAt = (clone $createdAt)->addHours(3);
                                        $isExpired = now()->greaterThan($expiresAt) && $statusStr === 'pending';
                                    @endphp
                                    <tr class="hover:bg-slate-700/30 transition">
                                        <td class="px-4 py-3 font-medium text-white">
                                            {{ $reservation->user->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-slate-300">
                                            {{ $reservation->charger->name ?? $reservation->charger->location->name ?? ('Charger #'.($reservation->charger_id ?? $reservation->id_charger ?? '-')) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($statusStr === 'completed' || $statusStr === 'finished')
                                                <span class="inline-flex items-center bg-slate-500/20 text-slate-300 border border-slate-500/30 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                    Selesai
                                                </span>
                                            @elseif($statusStr === 'active' || $statusStr === 'charging' || $statusStr === 'in_use')
                                                <span class="inline-flex items-center bg-blue-500/20 text-blue-400 border border-blue-500/30 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                    Sedang Charging
                                                </span>
                                            @elseif($isExpired || $statusStr === 'expired' || $statusStr === 'cancelled')
                                                <span class="inline-flex items-center bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                    Expired
                                                </span>
                                            @else
                                                <span class="inline-flex items-center bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                    Waiting (3h)
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-slate-500 text-xs">
                                            Belum ada data reservasi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- 4. TABEL RIWAYAT DRIVER SELESAI CHARGING --}}
            <div class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-5 border-b border-slate-700/60 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold text-white">Riwayat Pengisian Selesai</h3>
                        <p class="text-xs text-slate-400">Daftar driver yang telah menyelesaikan pengisian daya.</p>
                    </div>
                    <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-medium px-3 py-1 rounded-full">
                        {{ count($completedSessions ?? []) }} Transaksi Selesai
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-300">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-900/60 border-b border-slate-700/60">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama Driver</th>
                                <th scope="col" class="px-6 py-3">ID Charger / Lokasi</th>
                                <th scope="col" class="px-6 py-3">Total Energi</th>
                                <th scope="col" class="px-6 py-3">Total Biaya</th>
                                <th scope="col" class="px-6 py-3">Waktu Selesai</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/40">
                            @forelse($completedSessions ?? [] as $completed)
                                @php
                                    // 1. Ambil Nilai Energi
                                    $energy = (float) ($completed->energy_kwh ?? $completed->energy_consumed ?? 0);
                                    
                                    // 2. Ambil Nilai Biaya
                                    $cost = (float) ($completed->total_cost ?? 0);

                                    // 3. Kalkulasi Fallback Jika Data DB bernilai 0
                                    if ($energy <= 0) {
                                        $startTime = \Carbon\Carbon::parse($completed->waktu_mulai ?? $completed->start_time ?? $completed->created_at);
                                        $endTime   = \Carbon\Carbon::parse($completed->waktu_selesai ?? $completed->end_time ?? $completed->stopped_at ?? $completed->updated_at);
                                        $seconds = max(60, $startTime->diffInSeconds($endTime));
                                        
                                        $powerKw = $completed->charger->power_kw ?? 22;
                                        $energy  = round(($powerKw / 3600) * $seconds, 2);
                                        if ($energy <= 0) { $energy = 1.25; }
                                    }

                                    if ($cost <= 0) {
                                        $tarifPerKwh = 2466;
                                        $cost = round($energy * $tarifPerKwh);
                                    }
                                @endphp
                                <tr class="hover:bg-slate-700/30 transition">
                                    <td class="px-6 py-4 font-bold text-white flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center text-xs font-bold text-white">
                                            {{ strtoupper(substr($completed->user->name ?? 'D', 0, 1)) }}
                                        </div>
                                        {{ $completed->user->name ?? $completed->user->email ?? 'Driver' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-300">
                                        @if(isset($completed->charger->name) || isset($completed->charger->nama_charger))
                                            <span class="text-white font-semibold">
                                                {{ $completed->charger->name ?? $completed->charger->nama_charger }}
                                            </span>
                                        @elseif(isset($completed->charger->location->name))
                                            <span class="text-white font-semibold">
                                                {{ $completed->charger->location->name }} (Slot #{{ $completed->charger_id ?? $completed->id_charger }})
                                            </span>
                                        @else
                                            <span class="font-mono text-slate-400">
                                                Charger #{{ $completed->charger->id_charger ?? $completed->charger_id ?? $completed->id_charger ?? '-' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-emerald-400">
                                        {{ number_format($energy, 2) }} kWh
                                    </td>
                                    <td class="px-6 py-4 font-bold text-blue-400">
                                        Rp {{ number_format($cost, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-400">
                                        {{ \Carbon\Carbon::parse($completed->end_time ?? $completed->stopped_at ?? $completed->updated_at)->format('d M Y, H:i') }} WIB
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-semibold px-3 py-1 rounded-full">
                                            ✓ Selesai
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500 text-xs">
                                        Belum ada riwayat pengisian daya yang selesai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>