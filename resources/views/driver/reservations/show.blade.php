<x-app-layout>
    <div style="max-width: 48rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem 1.5rem;">
        
        <!-- Breadcrumb Navigasi -->
        <div style="margin-bottom: 0.5rem; font-size: 0.8rem; color: #60A5FA; font-weight: 600;">
            <a href="{{ route('reservations.index') }}" style="color: #60A5FA; text-decoration: none;">Reservasi Saya</a> 
            <span style="color: #64748B; margin: 0 0.3rem;">/</span> 
            <span style="color: #94A3B8;">Catatan & QR Aktivasi</span>
        </div>

        <!-- Header Halaman -->
        <div style="margin-bottom: 1.5rem; text-align: center;">
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0;">
                @if($reservation->status === 'pending')
                    Konfirmasi Reservasi Slot
                @else
                    Catatan Reservasi & QR Aktivasi
                @endif
            </h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.25rem; margin-bottom: 0;">
                @if($reservation->status === 'pending')
                    Periksa kembali detail reservasi Anda sebelum melakukan aktivasi mesin.
                @else
                    Scan QR Code ini pada mesin SPKLU atau gunakan ID Booking untuk mulai charging.
                @endif
            </p>
        </div>

        <!-- Card Main Container -->
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            
            <!-- Alert Notice Success / Error -->
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

            <!-- BADGE ID BOOKING / RESERVASI MANUAL -->
            <div style="background-color: #0F172A; border: 1px solid #3B82F6; padding: 0.85rem 1.25rem; border-radius: 0.75rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="display: block; font-size: 0.65rem; color: #60A5FA; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                        ID BOOKING / RESERVASI MANUAL
                    </span>
                    <strong style="color: #FFFFFF; font-size: 1.25rem; font-family: monospace;">
                        #{{ $reservation->id_reservation }}
                    </strong>
                </div>
                
                <span style="background-color: #1E293B; border: 1px solid {{ in_array($reservation->status, ['cancelled', 'expired']) ? '#EF4444' : '#3B82F6' }}; color: {{ in_array($reservation->status, ['cancelled', 'expired']) ? '#F87171' : '#60A5FA' }}; font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.75rem; border-radius: 0.5rem; text-transform: uppercase;">
                    {{ $reservation->status }}
                </span>
            </div>

            <!-- Detail Informasi Reservasi -->
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
                            🚗 {{ $reservation->vehicle->merek ?? $reservation->vehicle->brand ?? $reservation->vehicle->merk ?? 'EV Car' }} {{ $reservation->vehicle->model ?? $reservation->vehicle->nama_kendaran ?? '' }}
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

            <!-- TAHAP 1: JIKA STATUS PENDING (TAHAP PENGECEKAN DATA MAX 3 JAM) -->
            @if($reservation->status === 'pending')
                <div style="background-color: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 1rem; padding: 1.25rem; text-align: center; margin-bottom: 1.5rem;">
                    <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #FBBF24; text-transform: uppercase; letter-spacing: 0.05em;">
                        SISA WAKTU HOLDING SLOT (MAX 3 JAM)
                    </span>
                    <h1 id="countdown-timer" style="font-size: 2.25rem; font-weight: 800; color: #F59E0B; margin: 0.25rem 0; font-family: monospace;">
                        --:--:--
                    </h1>
                    <small style="color: #94A3B8; font-size: 0.75rem;">Slot Anda ditahan selama maksimal 3 jam. Tekan konfirmasi untuk membuka QR Code aktivasi.</small>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <form action="{{ route('reservations.confirm', $reservation->id_reservation) }}" method="POST">
                        @csrf
                        <button type="submit" style="width: 100%; background-color: #2563EB; color: #FFFFFF; font-weight: 800; border: none; padding: 0.85rem; border-radius: 0.75rem; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">
                            Lanjut ke Detail & QR Aktivasi →
                        </button>
                    </form>

                    <form action="{{ route('reservations.cancel', $reservation->id_reservation) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?');" style="width: 100%; background-color: transparent; border: 1px solid #EF4444; color: #F87171; font-weight: 700; padding: 0.65rem; border-radius: 0.75rem; font-size: 0.85rem; cursor: pointer;">
                            Batalkan Reservasi
                        </button>
                    </form>
                </div>

            <!-- TAHAP 2: JIKA STATUS CONFIRMED -->
            @elseif($reservation->status === 'confirmed')
                <div style="background-color: #0F172A; border: 1px dashed #3B82F6; border-radius: 1rem; padding: 1.5rem; text-align: center; margin-bottom: 1.5rem;">
                    <span style="display: block; font-size: 0.75rem; font-weight: 800; color: #60A5FA; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                        SCAN QR CODE INI PADA MESIN SPKLU
                    </span>
                    
                    <div style="background: white; padding: 1rem; display: inline-block; border-radius: 0.75rem; margin-bottom: 0.75rem;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ $reservation->id_reservation }}" alt="QR Code Reservasi" style="width: 160px; height: 160px;">
                    </div>

                    <p style="color: #94A3B8; font-size: 0.8rem; margin: 0;">
                        Tunjukkan QR ini ke scanner mesin SPKLU atau masukkan ID Manual <strong style="color: #FFF;">#{{ $reservation->id_reservation }}</strong> pada mesin.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="{{ route('driver.scan', ['booking_id' => $reservation->id_reservation]) }}" style="width: 100%; background-color: #10B981; color: #FFFFFF; font-weight: 800; padding: 0.85rem; border-radius: 0.75rem; font-size: 0.95rem; text-decoration: none; text-align: center; display: block; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                        ⚡ Mulai / Aktivasi Charging (Scan / Input ID)
                    </a>

                    <form action="{{ route('reservations.cancel', $reservation->id_reservation) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?');" style="width: 100%; background-color: transparent; border: 1px solid #EF4444; color: #F87171; font-weight: 700; padding: 0.65rem; border-radius: 0.75rem; font-size: 0.85rem; cursor: pointer;">
                            Batalkan Reservasi
                        </button>
                    </form>
                </div>

            <!-- TAHAP 3: CHARGING BERJALAN -->
            @elseif(in_array($reservation->status, ['active', 'charging']))
                <div style="background-color: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.3); border-radius: 1rem; padding: 1.25rem; text-align: center; margin-bottom: 1.5rem;">
                    <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #60A5FA; text-transform: uppercase; letter-spacing: 0.05em;">
                        STATUS SESI
                    </span>
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #3B82F6; margin: 0.25rem 0;">
                        ⚡ Pengisian Daya Sedang Berlangsung
                    </h2>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="{{ route('driver.charging.monitor', $reservation->id_reservation) }}" style="width: 100%; background-color: #2563EB; color: #FFFFFF; font-weight: 800; padding: 0.85rem; border-radius: 0.75rem; font-size: 0.95rem; text-decoration: none; text-align: center; display: block;">
                        📊 Pantau Telemetry Charging Real-Time
                    </a>

                    <form action="{{ route('driver.charging.stop', $reservation->id_reservation) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghentikan pengisian daya dan memotong saldo?');">
                        @csrf
                        <button type="submit" style="width: 100%; background-color: #DC2626; color: #FFFFFF; font-weight: 800; border: none; padding: 0.85rem; border-radius: 0.75rem; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 12px rgba(220,38,38,0.3);">
                            ⏹ Berhenti Charging & Lihat Struk Pembayaran
                        </button>
                    </form>
                </div>

            <!-- TAHAP 4: RESERVASI DIBATALKAN / EXPIRED -->
            @elseif(in_array($reservation->status, ['cancelled', 'expired']))
                <div style="background-color: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 1rem; padding: 1.25rem; text-align: center; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #EF4444; margin: 0.25rem 0;">
                        ✖ Reservasi Telah Dibatalkan / Kadaluarsa
                    </h2>
                </div>

                <div>
                    <a href="{{ route('reservations.index') }}" style="width: 100%; background-color: #334155; color: #FFFFFF; font-weight: 800; padding: 0.85rem; border-radius: 0.75rem; font-size: 0.95rem; text-decoration: none; text-align: center; display: block;">
                        ← Kembali ke Daftar Reservasi
                    </a>
                </div>

            <!-- TAHAP 5: COMPLETED -->
            @else
                <div style="background-color: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 1rem; padding: 1.25rem; text-align: center; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #10B981; margin: 0.25rem 0;">
                        ✓ Pengisian Daya Telah Berhasil
                    </h2>
                </div>

                <div>
                    <a href="{{ route('driver.charging.receipt', $reservation->id_reservation) }}" style="width: 100%; background-color: #059669; color: #FFFFFF; font-weight: 800; padding: 0.85rem; border-radius: 0.75rem; font-size: 0.95rem; text-decoration: none; text-align: center; display: block;">
                        🧾 Lihat Struk / Bukti Transaksi Saldo
                    </a>
                </div>
            @endif

        </div>

    </div>

    @if($reservation->status === 'pending')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const reservationId = "{{ $reservation->id_reservation }}";
        const timerElement = document.getElementById('countdown-timer');

        // Parse ISO format string secara aman
        const createdAtTime = new Date("{{ \Carbon\Carbon::parse($reservation->created_at)->toIso8601String() }}").getTime();
        const expiresAtTime = createdAtTime + (3 * 60 * 60 * 1000); // 3 Jam

        function checkTimer() {
            if (!timerElement) return;

            fetch(`/reservations/${reservationId}/timer`)
                .then(response => response.json())
                .then(data => {
                    const secondsLeft = data.remaining_seconds !== undefined ? data.remaining_seconds : data.remaining;

                    if (data.is_expired || data.status === 'expired' || secondsLeft <= 0) {
                        timerElement.innerText = "00:00:00";
                        timerElement.style.color = "#EF4444";
                        setTimeout(() => window.location.reload(), 1500);
                        return;
                    }

                    const totalSeconds = Math.floor(secondsLeft);
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = Math.floor(totalSeconds % 60);

                    timerElement.innerText = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                    timerElement.style.color = "#F59E0B";
                })
                .catch(error => {
                    // Fallback hitung mundur jika API timer offline
                    const now = new Date().getTime();
                    const distance = expiresAtTime - now;

                    if (distance <= 0) {
                        timerElement.innerText = "00:00:00";
                        timerElement.style.color = "#EF4444";
                        return;
                    }

                    const hours = Math.floor(distance / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    timerElement.innerText = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                    timerElement.style.color = "#F59E0B";
                });
        }

        checkTimer();
        setInterval(checkTimer, 1000);
    });
    </script>
    @endif
</x-app-layout>