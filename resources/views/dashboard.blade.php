<x-app-layout>
    <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem 1.5rem;">
        
        <!-- Welcome Soft Banner (Soft Gradient) -->
        <div style="background: linear-gradient(135deg, rgba(30,58,138,0.4) 0%, rgba(15,23,42,0.8) 100%); border: 1px solid rgba(59,130,246,0.3); border-radius: 1.25rem; padding: 1.75rem; margin-bottom: 2rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            <div style="margin-bottom: 0.5rem;">
                <span style="background-color: rgba(59,130,246,0.2); color: #93C5FD; border: 1px solid rgba(147,197,253,0.3); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                    Driver Dashboard
                </span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0;">Selamat Datang, {{ Auth::user()->name }}!</h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.35rem; margin-bottom: 0;">Kelola kendaraan listrikmu, pesan slot charger, dan pantau seluruh aktivitas dengan mudah.</p>
        </div>

        <!-- Layout 2 Kolom Sejajar (Grid Card) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            
            <!-- Kartu Mobil Saya -->
            <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.75rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);">
                <div>
                    <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background-color: rgba(37,99,235,0.2); border: 1px solid rgba(59,130,246,0.3); font-size: 1.25rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                        🚗
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.5rem;">Mobil Saya</h3>
                    <p style="font-size: 0.85rem; color: #94A3B8; line-height: 1.5; margin-bottom: 1.5rem;">
                        Daftarkan spesifikasi mobil listrik, jenis konektor (CCS2/Type 2), dan kapasitas baterai kWh milikmu.
                    </p>
                </div>
                <div>
                    <a href="{{ route('cars.index') }}" style="display: inline-block; background-color: #2563EB; color: #FFFFFF; font-weight: 700; padding: 0.65rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">
                        Lihat & Kelola Kendaraan →
                    </a>
                </div>
            </div>

            <!-- Kartu Reservasi Saya -->
            <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.75rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);">
                <div>
                    <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background-color: rgba(16,185,129,0.2); border: 1px solid rgba(16,185,129,0.3); font-size: 1.25rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                        ⚡
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.5rem;">Reservasi & Riwayat</h3>
                    <p style="font-size: 0.85rem; color: #94A3B8; line-height: 1.5; margin-bottom: 1.5rem;">
                        Pantau sisa timer holding lock reservasi aktif serta cek bukti transaksi pengisian sebelumnya.
                    </p>
                </div>
                <div>
                    <a href="{{ route('reservations.index') }}" style="display: inline-block; background-color: #10B981; color: #FFFFFF; font-weight: 700; padding: 0.65rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                        Lihat Reservasi Saya →
                    </a>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>