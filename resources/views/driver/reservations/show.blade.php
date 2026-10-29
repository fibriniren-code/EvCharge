<x-app-layout>
    <div style="max-width: 48rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem 1.5rem;">
        
        <!-- Breadcrumb Navigasi -->
        <div style="margin-bottom: 0.5rem; font-size: 0.8rem; color: #60A5FA; font-weight: 600;">
            <a href="{{ route('reservations.index') }}" style="color: #60A5FA; text-decoration: none;">Reservasi Saya</a> 
            <span style="color: #64748B; margin: 0 0.3rem;">/</span> 
            <span style="color: #94A3B8;">Detail Sesi Pengisian</span>
        </div>

        <!-- Header Halaman -->
        <div style="margin-bottom: 1.5rem; text-align: center;">
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0;">
                Sesi Pengisian Daya SPKLU
            </h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.25rem; margin-bottom: 0;">
                Pantau sesi pengisian daya atau hentikan sesi kapan saja.
            </p>
        </div>

        <!-- Card Main Container -->
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            
            <!-- Alert Status / Notice Success -->
            @if(session('success'))
                <div style="background-color: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #6EE7B7; padding: 0.85rem 1rem; border-radius: 0.75rem; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 600; text-align: center;">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background-color: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #FCA5A5; padding: 0.85rem 1rem; border-radius: 0.75rem; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 600; text-align: center;">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            <!-- Detail Reservasi List -->
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                <!-- Lokasi SPKLU -->
                <div style="background-color: #0F172A; border: 1px solid #2A3859; padding: 0.85rem 1.25rem; border-radius: 0.75rem;">
                    <span style="display: block; font-size: 0.65rem; color: #94A3B8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.2rem;">
                        LOKASI SPKLU
                    </span>
                    <strong style="color: #FFFFFF; font-size: 1rem;">
                        {{ $reservation->charger->location->name ?? $reservation->charger->location->nama_lokasi ?? 'Lokasi SPKLU' }}
                    </strong>
                </div>

                <!-- Charger & Vehicle Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem;">
                    <div style="background-color: #0F172A; border: 1px solid #2A3859; padding: 0.85rem 1.25rem; border-radius: 0.75rem;">
                        <span style="display: block; font-size: 0.65rem; color: #94A3B8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.2rem;">
                            CHARGER / PLUG
                        </span>
                        <strong style="color: #60A5FA; font-size: 0.95rem;">
                            {{ $reservation->charger->name ?? 'Charger #'.$reservation->charger_id }}
                        </strong>
                    </div>

                    <div style="background-color: #0F172A; border: 1px solid #2A3859; padding: 0.85rem 1.25rem; border-radius: 0.75rem;">
                        <span style="display: block; font-size: 0.65rem; color: #94A3B8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.2rem;">
                            KENDARAAN
                        </span>
                        <strong style="color: #34D399; font-size: 0.95rem;">
                            🚗 {{ $reservation->vehicle->merek ?? $reservation->vehicle->brand ?? $reservation->vehicle->merk ?? 'Tesla' }} {{ $reservation->vehicle->model ?? $reservation->vehicle->nama_kendaran ?? '' }}
                        </strong>
                    </div>
                </div>

                <!-- Waktu Sesi -->
                <div style="background-color: #0F172A; border: 1px solid #2A3859; padding: 0.85rem 1.25rem; border-radius: 0.75rem;">
                    <span style="display: block; font-size: 0.65rem; color: #94A3B8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.2rem;">
                        WAKTU SESI PENGISIAN
                    </span>
                    <strong style="color: #E2E8F0; font-size: 0.9rem;">
                        {{ \Carbon\Carbon::parse($reservation->waktu_mulai)->format('d M Y, H:i') }} s/d {{ \Carbon\Carbon::parse($reservation->waktu_selesai)->format('d M Y, H:i') }}
                    </strong>
                </div>
            </div>

            <!-- Countdown Timer Container -->
            <div style="background-color: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 1rem; padding: 1.25rem; text-align: center; margin-bottom: 1.5rem;">
                <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #FBBF24; text-transform: uppercase; letter-spacing: 0.05em;">
                    SISA WAKTU PENGISIAN / HOLDING
                </span>
                <h1 id="countdown-timer" style="font-size: 2.25rem; font-weight: 800; color: #F59E0B; margin: 0.25rem 0; font-family: monospace;">
                    --:--
                </h1>
                <small style="color: #94A3B8; font-size: 0.75rem;">Sesi pengisian sedang berjalan secara aktif.</small>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                
                <!-- Tombol Stop Charging (Utama) -->
                <form action="{{ route('reservations.stop', $reservation->id_reservation) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghentikan sesi pengisian daya ini?');">
                    @csrf
                    <button type="submit" id="btn-stop" style="width: 100%; background-color: #DC2626; color: #FFFFFF; font-weight: 800; border: none; padding: 0.85rem; border-radius: 0.75rem; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 12px rgba(220,38,38,0.3); transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        ⏹ Berhenti Charging (Stop Session)
                    </button>
                </form>

                <!-- Tombol Batalkan Reservasi -->
                <form action="{{ route('reservations.cancel', $reservation->id_reservation) }}" method="POST">
                    @csrf
                    <button type="submit" id="btn-cancel" style="width: 100%; background-color: transparent; border: 1px solid #EF4444; color: #F87171; font-weight: 700; padding: 0.65rem; border-radius: 0.75rem; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                        Batalkan Reservasi
                    </button>
                </form>

            </div>

        </div>

    </div>

    <!-- Script Counter Timer AJAX -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const reservationId = "{{ $reservation->id_reservation }}";
        const timerElement = document.getElementById('countdown-timer');

        function checkTimer() {
            fetch(`/reservations/${reservationId}/timer`)
                .then(response => response.json())
                .then(data => {
                    const secondsLeft = data.remaining !== undefined ? data.remaining : data.remaining_seconds;

                    if (data.is_expired || data.status === 'expired' || secondsLeft <= 0) {
                        timerElement.innerText = "00:00";
                        timerElement.style.color = "#EF4444";
                        return;
                    }

                    if (data.status === 'completed' || data.status === 'stopped') {
                        timerElement.innerText = "DIHENTIKAN";
                        timerElement.style.color = "#EF4444";
                        return;
                    }

                    const totalSeconds = Math.floor(secondsLeft);
                    const minutes = Math.floor(totalSeconds / 60);
                    const seconds = Math.floor(totalSeconds % 60);

                    const formattedMinutes = String(minutes).padStart(2, '0');
                    const formattedSeconds = String(seconds).padStart(2, '0');

                    timerElement.innerText = `${formattedMinutes}:${formattedSeconds}`;
                })
                .catch(error => console.error('Error fetching timer:', error));
        }

        checkTimer();
        setInterval(checkTimer, 1000);
    });
    </script>
</x-app-layout>