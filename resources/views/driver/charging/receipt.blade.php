<x-app-layout>
    <div style="max-width: 32rem; margin: 2rem auto; padding: 1rem;">
        
        <!-- Notice Status Success -->
        @if(session('success'))
            <div style="background-color: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #6EE7B7; padding: 0.85rem 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 600; text-align: center;">
                ✓ {{ session('success') }}
            </div>
        @endif

        <!-- STRUK CARD CONTAINER -->
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 2rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.4); text-align: center;">
            
            <!-- Checkmark Icon -->
            <div style="width: 3rem; height: 3rem; background-color: rgba(16,185,129,0.1); border: 1px solid #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: #10B981; font-size: 1.5rem; font-weight: bold;">
                ✓
            </div>

            <h2 style="font-size: 1.35rem; font-weight: 800; color: #FFFFFF; margin: 0 0 0.25rem 0;">
                Struk Transaksi SPKLU
            </h2>
            <p style="font-size: 0.8rem; color: #94A3B8; margin-bottom: 1.75rem;">
                Pembayaran Berhasil via Auto-Debit Saldo
            </p>

            @php
                // 1. Dapatkan KWh
                $kwh = $session->energy_kwh ?? $session->energy_consumed ?? 0;
                
                // Jika kwh di DB masih 0, hitung perkiraan dari durasi
                $startTime = \Carbon\Carbon::parse($session->start_time ?? $session->waktu_mulai ?? now());
                $endTime = \Carbon\Carbon::parse($session->end_time ?? $session->waktu_selesai ?? now());
                $seconds = max(5, $startTime->diffInSeconds($endTime));
                $minutes = max(1, ceil($seconds / 60));

                if ($kwh <= 0) {
                    $powerKw = $session->charger->power_kw ?? 22;
                    $kwh = round(($powerKw / 3600) * $seconds, 2);
                    if ($kwh <= 0) $kwh = 0.05;
                }

                // 2. Dapatkan Total Biaya
                $totalCost = $session->total_cost ?? round($kwh * 2466);
                if ($totalCost <= 0) {
                    $totalCost = round($kwh * 2466);
                }
            @endphp

            <!-- Table Detail Transaksi -->
            <div style="display: flex; flex-direction: column; gap: 0.85rem; text-align: left; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px border-style: dashed; border-color: #334155; padding-bottom: 0.5rem;">
                    <span style="color: #94A3B8;">No. Transaksi</span>
                    <strong style="color: #FFFFFF; font-family: monospace;">#EV-TRX-{{ $session->id_session ?? $session->id ?? $session->id_reservation ?? '1' }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px border-style: dashed; border-color: #334155; padding-bottom: 0.5rem;">
                    <span style="color: #94A3B8;">SPKLU Location</span>
                    <strong style="color: #FFFFFF;">{{ $session->charger->location->name ?? $session->charger->location->nama_lokasi ?? 'SPKLU Location' }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px border-style: dashed; border-color: #334155; padding-bottom: 0.5rem;">
                    <span style="color: #94A3B8;">Kendaraan</span>
                    <strong style="color: #FFFFFF;">{{ $session->vehicle->merek ?? $session->vehicle->brand ?? 'EV Car' }} {{ $session->vehicle->model ?? '' }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px border-style: dashed; border-color: #334155; padding-bottom: 0.5rem;">
                    <span style="color: #94A3B8;">Durasi Pengisian</span>
                    <strong style="color: #FFFFFF;">{{ $session->duration_minutes ?? $minutes }} Menit</strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px border-style: dashed; border-color: #334155; padding-bottom: 0.5rem;">
                    <span style="color: #94A3B8;">Total Daya (kWh)</span>
                    <strong style="color: #60A5FA;">{{ number_format($kwh, 2) }} kWh</strong>
                </div>
            </div>

            <hr style="border: none; border-top: 1px dashed #334155; margin: 1.5rem 0;">

            <!-- Total Biaya & Saldo -->
            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <span style="color: #E2E8F0; font-weight: 700; font-size: 1rem;">Total Potong Saldo</span>
                    <strong style="color: #F59E0B; font-size: 1.5rem; font-weight: 800;">
                        Rp {{ number_format($totalCost, 0, ',', '.') }}
                    </strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;">
                    <span style="color: #94A3B8;">Sisa Saldo Akun Anda</span>
                    <span style="color: #34D399; font-weight: 700;">Rp {{ number_format(auth()->user()->balance ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Action Button -->
            <a href="{{ route('dashboard') }}" style="display: block; width: 100%; background-color: #334155; color: #FFFFFF; font-weight: 700; padding: 0.85rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.9rem; transition: all 0.2s;">
                Kembali ke Dashboard
            </a>

        </div>

    </div>
</x-app-layout>