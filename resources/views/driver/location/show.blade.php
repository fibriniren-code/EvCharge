@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">{{ $location->name }}</h3>
    <p class="text-muted">{{ $location->address }}</p>

    <h5 class="mt-4 mb-3">Daftar Charger / Slot Available</h5>
    <div class="row" id="charger-list">
        @foreach($chargers as$charger)
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">{{ $charger->name }}</h5>
                            <span class="badge badge-status" id="badge-{{ $charger->id_charger }}">
                                {{ strtoupper($charger->status) }}
                            </span>
                        </div>
                        <p class="card-text text-muted small">
                            Tipe: {{ $charger->type ?? 'AC Fast Charging' }}
                        </p>
                        <a href="{{ route('reservations.create', ['location_id' => $location->id_location, 'charger_id' =>$charger->id_charger]) }}" 
                           class="btn btn-primary w-100 btn-reserve" 
                           id="btn-reserve-{{ $charger->id_charger }}">
                            Pesan Slot
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const locationId = "{{ $location->id_location }}";

    function pollChargerStatus() {
        fetch(`/locations/${locationId}/chargers/status`)
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    res.data.forEach(charger => {
                        const badge = document.getElementById(`badge-${charger.id_charger}`);
                        const btnReserve = document.getElementById(`btn-reserve-${charger.id_charger}`);

                        if (badge) {
                            badge.innerText = charger.status.toUpperCase();

                            if (charger.status === 'available') {
                                badge.className = 'badge badge-status bg-success';
                                if (btnReserve) btnReserve.classList.remove('disabled');
                            } else {
                                badge.className = 'badge badge-status bg-danger';
                                if (btnReserve) btnReserve.classList.add('disabled');
                            }
                        }
                    });
                }
            })
            .catch(err => console.error('Error polling charger status:', err));
    }

    pollChargerStatus();
    setInterval(pollChargerStatus, 5000);
});
</script>
@endsection