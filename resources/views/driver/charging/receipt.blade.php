<x-app-layout>
    <div class="py-10 min-h-screen">
        <div class="max-w-md mx-auto px-4 sm:px-6">
            
            <!-- Notice Status Success -->
            @if(session('success'))
                <div class="bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 p-3.5 rounded-xl mb-6 text-sm font-semibold text-center">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <!-- Struk Digital Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden">
                
                <!-- Header -->
                <div class="bg-gradient-to-b from-blue-900/50 to-slate-900 p-6 text-center border-b border-slate-800">
                    <div class="w-12 h-12 bg-blue-600/20 text-blue-400 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-blue-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Struk Pengisian Daya</h2>
                    <p class="text-xs text-slate-400 mt-1">Bukti Transaksi Resmi EVChargeHub</p>
                </div>

                @php
                    // 1. Dapatkan KWh & Durasi dengan Fallback
                    $kwh = $session->energy_kwh ?? $session->energy_consumed ?? 0;
                    
                    $startTime = \Carbon\Carbon::parse($session->start_time ?? $session->waktu_mulai ?? now());
                    $endTime = \Carbon\Carbon::parse($session->end_time ?? $session->waktu_selesai ?? now());
                    $seconds = max(5, $startTime->diffInSeconds($endTime));
                    $minutes = $session->duration_minutes ?? max(1, ceil($seconds / 60));

                    if ($kwh <= 0) {
                        $powerKw = $session->charger->power_kw ?? 22;
                        $kwh = round(($powerKw / 3600) * $seconds, 2);
                        if ($kwh <= 0) $kwh = 0.05;
                    }

                    // 2. Total Biaya
                    $totalCost = $session->total_cost ?? round($kwh * 2466);
                    if ($totalCost <= 0) {
                        $totalCost = round($kwh * 2466);
                    }
                @endphp

                <!-- Detail Rincian -->
                <div class="p-6 space-y-4 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-slate-800/60">
                        <span class="text-slate-400">ID Sesi / Transaksi</span>
                        <span class="font-mono font-bold text-white">#EV-TRX-{{ $session->id_session ?? $session->id ?? $session->id_reservation ?? '1' }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-slate-800/60">
                        <span class="text-slate-400">Waktu Mulai</span>
                        <span class="text-slate-200 font-medium">{{ $startTime->format('d M Y, H:i') }} WIB</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-slate-800/60">
                        <span class="text-slate-400">Lokasi SPKLU</span>
                        <span class="text-slate-200 font-medium text-right max-w-[200px] leading-tight">
                            {{ $session->charger->location->name ?? $session->charger->location->nama_lokasi ?? 'SPKLU Location' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-slate-800/60">
                        <span class="text-slate-400">Kendaraan</span>
                        <span class="text-slate-200 font-medium">
                            {{ $session->vehicle->merek ?? $session->vehicle->brand ?? 'EV Car' }} {{ $session->vehicle->model ?? '' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-slate-800/60">
                        <span class="text-slate-400">Durasi Charging</span>
                        <span class="text-slate-200 font-medium">{{ $minutes }} Menit</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-slate-800/60">
                        <span class="text-slate-400">Energi Terisi</span>
                        <span class="text-blue-400 font-bold">{{ number_format($kwh, 2) }} kWh</span>
                    </div>

                    <!-- Pembayaran & Saldo -->
                    <div class="pt-4 space-y-2">
                        <div class="flex justify-between items-center text-slate-400">
                            <span>Total Potong Saldo</span>
                            <span class="text-amber-400 font-bold text-lg">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-400">
                            <span>Metode Pembayaran</span>
                            <span class="text-emerald-400 font-semibold">Auto-Debit Saldo</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-400 pt-2 border-t border-slate-800">
                            <span>Sisa Saldo Anda</span>
                            <span class="text-emerald-400 font-mono font-bold">Rp {{ number_format(Auth::user()->balance ?? auth()->user()->balance ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Navigasi -->
                <div class="p-6 bg-slate-950/50 border-t border-slate-800 space-y-3">
                    <a href="{{ route('dashboard') }}" class="block w-full py-3 bg-blue-600 hover:bg-blue-500 text-white text-center font-bold text-sm rounded-xl transition">
                        Kembali ke Dashboard
                    </a>
                    <a href="{{ route('topup.index') }}" class="block w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-center font-medium text-sm rounded-xl border border-slate-700 transition">
                        Top Up Saldo
                    </a>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>