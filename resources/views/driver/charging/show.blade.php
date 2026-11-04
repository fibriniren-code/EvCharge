<x-app-layout>
    @php
        // 1. Ambil Relasi Lokasi & Kendaraan secara aman
        $location = $session->charger->location ?? $session->location ?? null;
        $vehicle  = $session->reservation->vehicle ?? $session->vehicle ?? null;
        $sessionId = $session->id_session ?? $session->id_reservation ?? $session->id;

        // 2. Waktu Mulai untuk Timer JavaScript Initial
        $startTimeIso = $session->waktu_mulai ?? $session->start_time ?? $session->created_at ?? now();
        $startTimeIso = \Carbon\Carbon::parse($startTimeIso)->toIso8601String();

        // 3. Penanganan Route Stop Charging
        $stopRoute = '#';
        if (\Illuminate\Support\Facades\Route::has('driver.charging.stop')) {
            $stopRoute = route('driver.charging.stop', $sessionId);
        } elseif (\Illuminate\Support\Facades\Route::has('charging.stop')) {
            $stopRoute = route('charging.stop', $sessionId);
        }
    @endphp

    <div class="container py-8 max-w-xl mx-auto px-4 min-h-screen">
        <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-2xl text-white">
            
            {{-- HEADER SESI AKTIF --}}
            <div class="flex items-center justify-between mb-4">
                <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-semibold rounded-full flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> CHARGING ACTIVE
                </span>
                <span class="text-xs font-mono text-slate-400">ID Sesi: #EV-TRX-{{ $sessionId }}</span>
            </div>

            {{-- DETAIL SPKLU & KENDARAAN --}}
            <h3 class="text-lg font-bold text-white mb-1">
                {{ $location->nama_lokasi ?? $location->name ?? 'SPKLU Voltron Yogyakarta' }}
            </h3>
            <p class="text-xs text-slate-400 mb-6">
                {{ $session->charger->name ?? $session->charger->nama_charger ?? 'Charger Slot' }} • 
                @if($vehicle)
                    {{ ($vehicle->merek ?? $vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '') }} ({{ $vehicle->plat_nomor ?? $vehicle->license_plate ?? 'BEV' }})
                @else
                    Tesla 3 Long Range (AB 1234 EV)
                @endif
            </p>

            {{-- LIVE METRICS BOX --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800/80 text-center">
                    <p class="text-xs text-slate-400 mb-1">Total Energi</p>
                    <h2 class="text-2xl font-black text-cyan-400" id="live-kwh">
                        {{ number_format((float)($session->energy_kwh ?? $session->energi_kwh ?? 0), 2) }} 
                        <span class="text-xs font-normal">kWh</span>
                    </h2>
                </div>
                <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800/80 text-center">
                    <p class="text-xs text-slate-400 mb-1">Estimasi Biaya</p>
                    <h2 class="text-2xl font-black text-amber-400" id="live-cost">
                        Rp {{ number_format((float)($session->total_cost ?? $session->estimasi_biaya ?? 0), 0, ',', '.') }}
                    </h2>
                </div>
            </div>

            {{-- DURASI PENGISIAN --}}
            <div class="bg-slate-950/60 p-5 rounded-2xl border border-slate-800/80 text-center mb-6">
                <p class="text-xs text-slate-400 mb-1">Durasi Pengisian Daya</p>
                <h1 class="text-3xl font-mono font-bold text-white tracking-widest" id="live-timer">00:00:00</h1>
            </div>

            {{-- TOMBOL STOP SESSION --}}
            <form action="{{ $stopRoute }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghentikan pengisian daya?')">
                @csrf
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-sm shadow-lg shadow-red-900/30 flex items-center justify-center gap-2 transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                    </svg>
                    Berhenti Charging (Stop Session)
                </button>
            </form>
        </div>
    </div>

    {{-- SCRIPT TELEMETRY REAL-TIME --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sessionId = "{{ $sessionId }}";
        const startTime = new Date("{{ $startTimeIso }}").getTime();
        const powerKw = {{ $session->charger->power_kw ?? 22 }};
        const tarifPerKwh = 2466;

        function updateTelemetry() {
            fetch(`/charging/session/${sessionId}/telemetry`)
                .then(res => res.json())
                .then(data => {
                    if(data && data.energy_kwh !== undefined) {
                        document.getElementById('live-kwh').innerHTML = `${parseFloat(data.energy_kwh).toFixed(2)} <span class="text-xs font-normal">kWh</span>`;
                        document.getElementById('live-cost').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(data.total_cost || Math.round(data.energy_kwh * tarifPerKwh))}`;
                        if(data.formatted_time) {
                            document.getElementById('live-timer').innerText = data.formatted_time;
                        }
                    }
                })
                .catch(err => {
                    // Fallback hitung lokal jika server fetch delay
                    const now = new Date().getTime();
                    const secondsElapsed = Math.max(1, Math.floor((now - startTime) / 1000));
                    
                    const hours = String(Math.floor(secondsElapsed / 3600)).padStart(2, '0');
                    const minutes = String(Math.floor((secondsElapsed % 3600) / 60)).padStart(2, '0');
                    const seconds = String(secondsElapsed % 60).padStart(2, '0');
                    
                    document.getElementById('live-timer').innerText = `${hours}:${minutes}:${seconds}`;

                    const calcKwh = ((powerKw / 3600) * secondsElapsed).toFixed(2);
                    const calcCost = Math.round(calcKwh * tarifPerKwh);

                    document.getElementById('live-kwh').innerHTML = `${calcKwh} <span class="text-xs font-normal">kWh</span>`;
                    document.getElementById('live-cost').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(calcCost)}`;
                });
        }

        updateTelemetry();
        setInterval(updateTelemetry, 2000);
    });
    </script>
</x-app-layout>