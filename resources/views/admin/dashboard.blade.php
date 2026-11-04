<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-wide">
                    {{ __('Admin Dashboard') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Pantau status stasiun SPKLU dan aktivitas pengisian daya secara real-time.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-6">
            
            {{-- 1. Kartu Ringkasan Statistik Status Charger --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                
                {{-- Total Charger --}}
                <div class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 p-5 rounded-2xl shadow-lg transition hover:border-slate-500">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Charger</p>
                    <p class="text-3xl font-extrabold text-white mt-2">{{ $statusCounts['total'] ?? 0 }}</p>
                </div>

                {{-- Tersedia --}}
                <div class="bg-emerald-950/40 backdrop-blur-md border border-emerald-500/30 p-5 rounded-2xl shadow-lg transition hover:border-emerald-500/60">
                    <p class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Tersedia (Available)</p>
                    <p class="text-3xl font-extrabold text-emerald-400 mt-2">{{ $statusCounts['available'] ?? 0 }}</p>
                </div>

                {{-- Digunakan --}}
                <div class="bg-blue-950/40 backdrop-blur-md border border-blue-500/30 p-5 rounded-2xl shadow-lg transition hover:border-blue-500/60">
                    <p class="text-xs font-semibold text-blue-400 uppercase tracking-wider">Digunakan (Occupied)</p>
                    <p class="text-3xl font-extrabold text-blue-400 mt-2">{{ $statusCounts['occupied'] ?? 0 }}</p>
                </div>

                {{-- Rusak --}}
                <div class="bg-rose-950/40 backdrop-blur-md border border-rose-500/30 p-5 rounded-2xl shadow-lg transition hover:border-rose-500/60">
                    <p class="text-xs font-semibold text-rose-400 uppercase tracking-wider">Rusak (Faulted)</p>
                    <p class="text-3xl font-extrabold text-rose-400 mt-2">{{ $statusCounts['faulted'] ?? 0 }}</p>
                </div>

                {{-- Non-Aktif --}}
                <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/40 p-5 rounded-2xl shadow-lg transition hover:border-slate-600">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Non-Aktif (Unavailable)</p>
                    <p class="text-3xl font-extrabold text-slate-400 mt-2">{{ $statusCounts['unavailable'] ?? 0 }}</p>
                </div>

            </div>

            {{-- 2. Alert Peringatan Kendala Teknis (FR-09 Alert) --}}
            @if(isset($faultedChargers) && $faultedChargers->count() > 0)
                <div class="bg-rose-500/10 border border-rose-500/40 text-rose-200 p-4 rounded-xl shadow-lg backdrop-blur-md flex items-center">
                    <svg class="h-6 w-6 text-rose-400 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <span class="font-semibold text-rose-300">Peringatan Kendala Teknis!</span>
                        <p class="text-xs text-rose-200/80 mt-0.5">Terdapat {{ $faultedChargers->count() }} stasiun charger dengan status <strong>Faulted</strong> yang memerlukan penanganan tim teknis.</p>
                    </div>
                </div>
            @endif

            {{-- 3. Tabel Sesi Pengisian Daya Aktif --}}
            <div class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-5 border-b border-slate-700/60 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold text-white">Sesi Pengisian Daya Aktif</h3>
                        <p class="text-xs text-slate-400">Daftar kendaraan yang sedang mengisi daya saat ini.</p>
                    </div>
                    <span class="bg-blue-500/20 text-blue-300 border border-blue-500/30 text-xs font-medium px-3 py-1 rounded-full">
                        {{ $activeSessions->count() }} Sesi Berjalan
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-300">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-900/60 border-b border-slate-700/60">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">Pengguna</th>
                                <th scope="col" class="px-6 py-3.5">ID Charger</th>
                                <th scope="col" class="px-6 py-3.5">Waktu Mulai</th>
                                <th scope="col" class="px-6 py-3.5">Energi (kWh)</th>
                                <th scope="col" class="px-6 py-3.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/40">
                            @forelse($activeSessions as $session)
                                <tr class="hover:bg-slate-700/30 transition">
                                    <td class="px-6 py-4 font-medium text-white">
                                        {{ $session->user->name ?? 'Pengguna Tidak Dikenal' }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-slate-300">
                                        {{ $session->charger->id_charger ?? $session->charger_id ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ optional($session->start_time ?? $session->waktu_mulai)->format('d M Y, H:i') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-emerald-400">
                                        {{ number_format($session->energy_consumed ?? $session->energy_kwh ?? 0, 2) }} kWh
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                                            Aktif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                        <svg class="mx-auto h-8 w-8 text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Tidak ada sesi pengisian daya yang sedang aktif saat ini.
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