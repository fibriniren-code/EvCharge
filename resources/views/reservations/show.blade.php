@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold text-white mb-3">Detail Reservasi Slot</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card bg-dark text-white border-secondary mb-4">
        <div class="card-body">
            <h5 class="text-info">{{ $reservation->charger->location->name ?? 'Lokasi SPKLU' }}</h5>
            <p class="mb-1"><strong>Mesin Charger:</strong> {{ $reservation->charger->name ?? $reservation->charger_id }}</p>
            <p class="mb-1"><strong>Kendaraan:</strong> {{ $reservation->vehicle->brand ?? '' }} ({{ $reservation->vehicle->license_plate ?? $reservation->vehicle->plat_nomor }})</p>
            <p class="mb-1"><strong>Waktu Mulai:</strong> {{ \Carbon\Carbon::parse($reservation->waktu_mulai)->format('d M Y, H:i') }}</p>
            <p class="mb-1"><strong>Waktu Selesai:</strong> {{ \Carbon\Carbon::parse($reservation->waktu_selesai)->format('d M Y, H:i') }}</p>
            <p class="mb-0"><strong>Status:</strong> 
                <span class="badge bg-warning text-dark" id="status-badge">{{ strtoupper($reservation->status) }}</span>
            </p>
        </div>
    </div>

    <!-- Tombol Berhenti Charging -->
    <form action="{{ route('charging.stop', $reservation->id) }}" method="POST" class="mt-4">
    @csrf
    <button type="submit" 
            onclick="return confirm('Apakah Anda yakin ingin menghentikan sesi pengisian daya ini?')"
            class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 active:scale-[0.98] text-white font-bold text-sm shadow-lg shadow-red-900/30 border border-red-500/30 flex items-center justify-center gap-2 transition-all duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
        </svg>
        <span>Berhenti Charging (Stop Session)</span>
    </button>
    </form>

    {{-- Box Countdown Timer --}}
    @if($reservation->status === 'pending')
    <div class="card bg-secondary text-white text-center p-4 mb-4" id="timer-box">
        <h4>Sisa Waktu Holding Slot:</h4>
        <h1 class="display-4 fw-bold text-warning" id="countdown-timer">00:00</h1>
        <p class="small text-light">Selesaikan konfirmasi sebelum waktu habis agar slot tidak terlepas secara otomatis.</p>
        
        <form action="{{ route('reservations.confirm', $reservation->id_reservation) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success btn-lg w-100 mt-2">Konfirmasi Reservasi Sekarang</button>
        </form>

        <form action="{{ route('reservations.cancel', $reservation->id_reservation) }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Batalkan Reservasi</button>
        </form>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const timerElement = document.getElementById('countdown-timer');
    const reservationId = "{{ $reservation->id_reservation }}";

    function fetchTimer() {
        fetch(`/reservations/${reservationId}/timer`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'expired' || data.remaining_seconds <= 0) {
                    timerElement.innerText = "EXPIRED";
                    document.getElementById('status-badge').className = "badge bg-danger";
                    document.getElementById('status-badge').innerText = "EXPIRED";
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    let minutes = Math.floor(data.remaining_seconds / 60);
                    let seconds = data.remaining_seconds % 60;
                    timerElement.innerText = 
                        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            })
            .catch(error => console.error('Error fetching timer:', error));
    }

    fetchTimer();
    setInterval(fetchTimer, 3000);
});
</script>
@endsection