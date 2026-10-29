<x-app-layout>
    <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem 1.5rem;">
        
        <!-- Header Halaman -->
        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0; tracking-tight: -0.025em;">
                Daftar SPKLU & Stasiun Pengisian
            </h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.25rem; margin-bottom: 0;">
                Cari stasiun pengisian kendaraan listrik terdekat, cek ketersediaan slot charger real-time, dan lakukan reservasi.
            </p>
        </div>

        <!-- Filter / Form Pencarian Kota -->
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);">
            <form action="{{ route('spklu.index') }}" method="GET">
                <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                    CARI SPKLU / KOTA
                </label>
                
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Ketik nama kota (misal: Yogyakarta, Jakarta, Bandung...)" 
                           style="flex: 1; min-width: 250px; background-color: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.65rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; outline: none;"
                           required>
                    
                    <button type="submit" style="background-color: #2563EB; color: #FFFFFF; font-weight: 700; border: none; padding: 0.65rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">
                        🔍 Cari di Peta
                    </button>
                    
                    <a href="{{ route('spklu.index') }}" style="background-color: #334155; color: #CBD5E1; font-weight: 600; text-decoration: none; padding: 0.65rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; display: flex; align-items: center;">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        @if(!request('search'))
            <!-- Tampilan Awal / Empty State Sebelum Pencarian -->
            <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 4rem 2rem; text-align: center; color: #94A3B8;">
                <div style="font-size: 4rem; line-height: 1; margin-bottom: 1rem; opacity: 0.5;">🔍</div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.5rem;">Silakan Masukkan Nama Kota</h3>
                <p style="font-size: 0.85rem; color: #94A3B8; max-width: 28rem; margin: 0 auto;">
                    Ketik kota tujuan Anda pada kolom di atas lalu klik "Cari di Peta" untuk menampilkan lokasi SPKLU terdekat.
                </p>
            </div>
        @else
            <!-- Container Peta Google Maps / Peta SPKLU -->
            <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.25rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: #60A5FA; display: flex; align-items: center; gap: 0.35rem;">
                        📍 Peta SPKLU Terdekat: <span style="color: #FFFFFF;">{{ request('search') }}</span>
                    </span>
                    <a href="https://www.google.com/maps/search/SPKLU+{{ urlencode(request('search')) }}" target="_blank" style="font-size: 0.75rem; color: #94A3B8; text-decoration: none;">
                        Buka Aplikasi Google Maps ↗
                    </a>
                </div>

                <!-- Iframe Peta / Container Peta -->
                <div style="border-radius: 0.75rem; overflow: hidden; border: 1px solid #334155; height: 320px; background-color: #0F172A;">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        loading="lazy" 
                        allowfullscreen 
                        src="https://maps.google.com/maps?q=SPKLU%20{{ urlencode(request('search')) }}&t=&z=13&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
            </div>

            <!-- Banner Quick Action Scan QR Tepat Setelah Peta -->
            <div style="background-color: #1E293B; border: 1px solid #10B981; border-radius: 1.25rem; padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 4px 12px rgba(16,185,129,0.15);">
                <div>
                    <h4 style="font-size: 1rem; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span>⚡</span> Sudah Sampai di Lokasi Mesin Charger?
                    </h4>
                    <p style="font-size: 0.8rem; color: #94A3B8; margin: 0.25rem 0 0 0;">
                        Pindai kode QR pada fisik mesin SPKLU untuk langsung mengaktifkan sesi pengisian daya.
                    </p>
                </div>
                <a href="{{ route('driver.scan') }}" style="background-color: #10B981; color: #FFFFFF; font-weight: 700; padding: 0.65rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                    📷 Scan QR Mesin Charger
                </a>
            </div>

            <!-- List Detail Hasil SPKLU -->
            <div style="margin-bottom: 1rem;">
                <h3 style="font-size: 0.875rem; font-weight: 800; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                    DETAIL SPKLU DIKEMBANGKAN
                </h3>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @forelse($locations as $location)
                        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);">
                            <div style="margin-bottom: 1rem; border-bottom: 1px solid #334155; padding-bottom: 0.75rem;">
                                <h4 style="font-size: 1.125rem; font-weight: 800; color: #FFFFFF; margin: 0 0 0.25rem 0;">
                                    {{ $location->name ?? $location->nama_lokasi }}
                                </h4>
                                <p style="font-size: 0.8rem; color: #94A3B8; margin: 0;">
                                    📍 {{ $location->address ?? $location->alamat }}
                                </p>
                            </div>

                            <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #60A5FA; text-transform: uppercase; tracking-wider: 0.05em; margin-bottom: 0.75rem;">
                                SLOT CHARGER TERSEDIA:
                            </span>

                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @forelse($location->chargers as $charger)
                                    <div style="background-color: #0F172A; border: 1px solid #2A3859; border-radius: 0.75rem; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                                        <div>
                                            <span style="display: block; font-size: 0.875rem; font-weight: 700; color: #FFFFFF;">
                                                {{ $charger->name ?? 'Charger #'.$charger->id_charger }} ({{ $charger->connector_type ?? $charger->tipe_konektor }})
                                            </span>
                                            <span style="font-size: 0.75rem; color: #94A3B8;">
                                                Daya: {{ $charger->power_kw ?? $charger->daya_kw }} kW
                                            </span>
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            @if(($charger->status ?? 'available') === 'available')
                                                <span style="color: #34D399; font-size: 0.75rem; font-weight: 700; background-color: rgba(52,211,153,0.15); border: 1px solid rgba(52,211,153,0.3); padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                                    ● Tersedia
                                                </span>
                                                
                                                <!-- Tombol Scan QR Langsung pada Slot Charger -->
                                                <a href="{{ route('driver.scan') }}" 
                                                   style="background-color: #10B981; color: #FFFFFF; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 0.5rem; text-decoration: none;">
                                                    📷 Scan QR
                                                </a>

                                                <a href="{{ route('reservations.create', ['location' => $location->id_location ?? $location->id, 'charger' => $charger->id_charger ?? $charger->id]) }}" 
                                                   style="background-color: #2563EB; color: #FFFFFF; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 1rem; border-radius: 0.5rem; text-decoration: none; box-shadow: 0 2px 8px rgba(37,99,235,0.3);">
                                                    Pesan
                                                </a>
                                            @else
                                                <span style="color: #F87171; font-size: 0.75rem; font-weight: 700; background-color: rgba(248,113,113,0.15); border: 1px solid rgba(248,113,113,0.3); padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                                    ● Terpakai / Disewa
                                                </span>
                                                <button disabled style="background-color: #334155; color: #64748B; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 1rem; border-radius: 0.5rem; border: none; cursor: not-allowed;">
                                                    Tidak Tersedia
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p style="font-size: 0.8rem; color: #64748B; margin: 0;">Tidak ada slot charger terdaftar di stasiun ini.</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 2rem; text-align: center; color: #94A3B8;">
                            Tidak ditemukan lokasi SPKLU untuk kata kunci <strong>"{{ request('search') }}"</strong>.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

    </div>
</x-app-layout>