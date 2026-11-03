@extends('layouts.app')

@section('content')
<div class="container py-4 max-w-xl mx-auto">
    <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl text-white">
        <div class="flex items-center justify-between mb-4">
            <span class="px-3 py-1 bg-green-500/20 text-green-400 border border-green-500/30 text-xs font-semibold rounded-full flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-ping"></span> CHARGING ACTIVE
            </span>
            <span class="text-xs text-gray-400">ID Sesi: #{{ $session->id_session }}</span>
        </div>

        <h3 class="text-lg font-bold text-white mb-1">{{ $session->charger->location->name ?? 'SPKLU Station' }}</h3>
        <p class="text-xs text-gray-400 mb-6">{{ $session->charger->name ?? 'Charger Slot' }} • {{ $session->vehicle->brand ?? '' }} ({{ $session->vehicle->license_plate ?? 'BEV' }})</p>

        <!-- Live Metrics Box -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-900 p-4 rounded-xl border border-gray-700/50 text-center">
                <p class="text-xs text-gray-400 mb-1">Total Energi</p>
                <h2 class="text-2xl font-black text-cyan-400" id="live-kwh">0.00 <span class="text-xs font-normal">kWh</span></h2>
            </div>
            <div class="bg-gray-900 p-4 rounded-xl border border-gray-700/50 text-center">
                <p class="text-xs text-gray-400 mb-1">Estimasi Biaya</p>
                <h2 class="text-2xl font-black text-yellow-400" id="live-cost">Rp 0</h2>
            </div>
        </div>

        <div class="bg-gray-900 p-4 rounded-xl border border-gray-700/50 text-center mb-6">
            <p class="text-xs text-gray-400 mb-1">Durasi Pengisian Daya</p>
            <h1 class="text-3xl font-mono font-bold text-white" id="live-timer">00:00:00</h1>
        </div>

        <!-- Tombol Stop Session -->
        <form action="{{ route('driver.charging.stop', $session->id_session) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghentikan pengisian daya?')">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionId = "{{ $session->id_session }}";

    function updateTelemetry() {
        fetch(`/charging/session/${sessionId}/telemetry`)
            .then(res => res.json())
            .then(data => {
                if(data.energy_kwh !== undefined) {
                    document.getElementById('live-kwh').innerHTML = `${data.energy_kwh} <span class="text-xs font-normal">kWh</span>`;
                    document.getElementById('live-cost').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(data.total_cost)}`;
                    document.getElementById('live-timer').innerText = data.formatted_time || '00:00:00';
                }
            })
            .catch(err => console.error(err));
    }

    updateTelemetry();
    setInterval(updateTelemetry, 3000);
});
</script>
@endsection