<x-app-layout>
    <div style="max-width: 32rem; margin: 0 auto; padding: 2rem 1.5rem 3rem 1.5rem;">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; background-color: rgba(16, 185, 129, 0.15); border-radius: 50%; color: #34D399; margin-bottom: 0.75rem; border: 1px solid rgba(16, 185, 129, 0.3);">
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 2rem; height: 2rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #FFFFFF; margin: 0;">Sesi Charging Selesai</h1>
            <p style="font-size: 0.85rem; color: #94A3B8; margin-top: 0.25rem;">Silakan selesaikan pembayaran tagihan Anda.</p>
        </div>

        <!-- Struk Card (FR-05 & FR-06) -->
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.5rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            
            <div style="text-align: center; border-bottom: 1px dashed #475569; padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
                <span style="font-size: 0.75rem; color: #94A3B8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">TOTAL TAGIHAN</span>
                <h2 style="font-size: 2.25rem; font-weight: 900; color: #34D399; margin: 0.25rem 0;">
                    Rp {{ number_format($session->total_cost ?? 0, 0, ',', '.') }}
                </h2>
                <span style="font-size: 0.75rem; color: #64748B;">ID Transaksi: #{{ $session->id_session ?? $session->id ?? '-' }}</span>
            </div>

            <!-- Detail Biaya -->
            <div style="display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.875rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; color: #CBD5E1;">
                    <span style="color: #94A3B8;">Lokasi SPKLU</span>
                    <strong>{{ $session->charger->location->name ?? $session->charger->location->nama_lokasi ?? 'SPKLU Station' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; color: #CBD5E1;">
                    <span style="color: #94A3B8;">Charger</span>
                    <strong>{{ $session->charger->name ?? ('Charger #'.($session->charger_id ?? '1')) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; color: #CBD5E1;">
                    <span style="color: #94A3B8;">Kendaraan</span>
                    <strong>{{ $session->vehicle->merek ?? $session->vehicle->brand ?? 'EV Vehicle' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; color: #CBD5E1;">
                    <span style="color: #94A3B8;">Energi Terpakai</span>
                    <strong>{{ $session->energy_kwh ?? 0 }} kWh</strong>
                </div>
                <div style="display: flex; justify-content: space-between; color: #CBD5E1;">
                    <span style="color: #94A3B8;">Durasi Pengisian</span>
                    <strong>{{ $session->duration_minutes ?? 0 }} Menit</strong>
                </div>
            </div>

            <!-- Pilih Metode Pembayaran (FR-06) -->
            <form action="{{ route('reservations.index') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id_session ?? $session->id ?? '' }}">

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.75rem; color: #94A3B8; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Pilih Metode Pembayaran</label>
                    <select name="payment_method" style="width: 100%; background-color: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.75rem; border-radius: 0.75rem; font-size: 0.875rem; outline: none;">
                        <option value="saldo">Saldo EvChargeHub (Rp {{ number_format(auth()->user()->saldo ?? 150000, 0, ',', '.') }})</option>
                        <option value="qris">QRIS / E-Wallet (Gopay, OVO, ShopeePay)</option>
                        <option value="va">Virtual Account Bank (BCA, Mandiri, BRI)</option>
                    </select>
                </div>

                <button type="submit" style="width: 100%; background-color: #10B981; color: #FFFFFF; font-weight: 800; border: none; padding: 0.85rem; border-radius: 0.75rem; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 12px rgba(16,185,129,0.3); transition: all 0.2s;">
                    Bayar Sekarang
                </button>
            </form>

        </div>
    </div>
</x-app-layout>