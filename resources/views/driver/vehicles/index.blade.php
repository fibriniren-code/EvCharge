<x-app-layout>
    <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem 1.5rem;">
        
        <!-- Header Halaman & Tombol Tambah Mobil -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0;">
                    Daftar Mobil Saya
                </h1>
                <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.25rem; margin-bottom: 0;">
                    Kelola spesifikasi kendaraan listrik dan tipe konektor pengisian daya milikmu.
                </p>
            </div>
            <div>
                <a href="{{ route('cars.create') }}" style="background-color: #2563EB; color: #FFFFFF; font-weight: 700; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(37,99,235,0.4);">
                    + Tambah Mobil Baru
                </a>
            </div>
        </div>

        <!-- Alert Notification -->
        @if(session('success'))
            <div style="background-color: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #6EE7B7; padding: 0.85rem 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 600;">
                ✓ {{ session('success') }}
            </div>
        @endif

        <!-- Grid Kartu Kendaraan Aesthetic Showcase -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 2rem;">
            @forelse($vehicles as $vehicle)
                <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.5rem; overflow: hidden; box-shadow: 0 15px 30px rgba(0,0,0,0.35); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.3s ease, border-color 0.3s ease;">
                    
                    <div>
                        <!-- Showcase Image Full Banner -->
                        <div style="position: relative; width: 100%; height: 220px; background-color: #0F172A; overflow: hidden;">
                            <img src="https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=1000&q=80" 
                                 alt="Electric Vehicle" 
                                 style="width: 100%; height: 100%; object-fit: cover; object-position: center; transition: transform 0.5s ease;"
                                 onmouseover="this.style.transform='scale(1.08)'"
                                 onmouseout="this.style.transform='scale(1)'">
                            
                            <!-- Gradient Overlay biar teks di atas gambar kelihatan jelas -->
                            <div style="position: absolute; inset: 0; background: linear-gradient(to top, #1E293B 0%, rgba(15,23,42,0.4) 60%, rgba(15,23,42,0.8) 100%);"></div>

                            <!-- Badge Plat Nomor -->
                            <div style="position: absolute; top: 1rem; right: 1rem; background-color: rgba(15,23,42,0.85); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15); color: #FBBF24; font-size: 0.8rem; font-weight: 800; padding: 0.35rem 0.85rem; border-radius: 0.6rem; letter-spacing: 0.05em; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                                {{ $vehicle->nomor_polisi }}
                            </div>

                            <!-- Merek & Model di Atas Gradient Gambar -->
                            <div style="position: absolute; bottom: 0.75rem; left: 1.25rem; right: 1.25rem;">
                                <span style="font-size: 0.7rem; font-weight: 800; color: #60A5FA; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 0.1rem;">
                                    {{ $vehicle->merek }}
                                </span>
                                <h3 style="font-size: 1.5rem; font-weight: 800; color: #FFFFFF; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                    {{ $vehicle->model }}
                                </h3>
                            </div>
                        </div>

                        <!-- Panel Spesifikasi Baterai & Konektor -->
                        <div style="padding: 1.25rem;">
                            <div style="background-color: #0F172A; border: 1px solid #2A3859; border-radius: 1rem; padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span style="display: block; font-size: 0.65rem; color: #94A3B8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Kapasitas Baterai</span>
                                    <span style="font-size: 1rem; font-weight: 800; color: #FFFFFF; margin-top: 0.1rem; display: block;">⚡ {{ $vehicle->kapasitas_baterai_kwh ?? '-' }} kWh</span>
                                </div>
                                <div style="height: 2.2rem; width: 1px; background-color: #2A3859;"></div>
                                <div style="text-align: right;">
                                    <span style="display: block; font-size: 0.65rem; color: #94A3B8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Konektor Charger</span>
                                    <span style="font-size: 1rem; font-weight: 800; color: #34D399; margin-top: 0.1rem; display: block;">🔌 {{ $vehicle->tipe_konektor ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Kartu: Tombol Hapus -->
                    <div style="padding: 0 1.25rem 1.25rem 1.25rem; display: flex; justify-content: flex-end;">
                        <form action="{{ route('vehicles.destroy', $vehicle->id_vehicle ?? $vehicle->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kendaraan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background-color: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3); color: #F87171; font-weight: 700; padding: 0.5rem 1.125rem; border-radius: 0.65rem; font-size: 0.75rem; cursor: pointer; transition: all 0.2s;">
                                🗑️ Hapus Mobil
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div style="grid-column: 1 / -1; background-color: #1E293B; border: 1px solid #334155; border-radius: 1.5rem; padding: 4rem 1.5rem; text-align: center; color: #94A3B8;">
                    <div style="font-size: 3.5rem; margin-bottom: 0.75rem; opacity: 0.5;">🚗</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.5rem;">Belum Ada Kendaraan</h3>
                    <p style="font-size: 0.85rem; color: #94A3B8; max-width: 24rem; margin: 0 auto 1.5rem auto;">
                        Tambahkan mobil listrik kamu agar dapat memesan slot pengisian SPKLU.
                    </p>
                    <a href="{{ route('cars.create') }}" style="background-color: #2563EB; color: #FFFFFF; font-weight: 700; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-size: 0.85rem; display: inline-block;">
                        + Tambah Mobil Sekarang
                    </a>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>