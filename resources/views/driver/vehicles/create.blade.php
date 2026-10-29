<x-app-layout>
    <div style="max-width: 48rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem 1.5rem;">
        
        <!-- Breadcrumb Navigasi -->
        <div style="margin-bottom: 0.5rem; font-size: 0.8rem; color: #60A5FA; font-weight: 600;">
            <a href="{{ route('cars.index') }}" style="color: #60A5FA; text-decoration: none;">Mobil Saya</a> 
            <span style="color: #64748B; margin: 0 0.3rem;">/</span> 
            <span style="color: #94A3B8;">Tambah Kendaraan</span>
        </div>

        <!-- Header Halaman -->
        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0;">
                Tambah Mobil Listrik Baru
            </h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.25rem; margin-bottom: 0;">
                Isi spesifikasi kendaraan listrikmu untuk kemudahan pencocokan jenis charger SPKLU.
            </p>
        </div>

        <!-- Error Validation Display -->
        @if ($errors->any())
            <div style="background-color: rgba(239,68,68,0.15); border: 1px solid #EF4444; color: #FCA5A5; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
                <strong style="display: block; margin-bottom: 0.25rem;">Mohon periksa inputan Anda:</strong>
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Tambah Kendaraan -->
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
            <form action="{{ route('vehicles.store') }}" method="POST">
                @csrf

                <!-- Grid Merek & Model -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                            Merek Kendaraan
                        </label>
                        <input type="text" name="merek" value="{{ old('merek') }}" placeholder="Contoh: Hyundai, Wuling, Tesla" required style="width: 100%; background-color: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.75rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                            Model / Tipe Mobil
                        </label>
                        <input type="text" name="model" value="{{ old('model') }}" placeholder="Contoh: Ioniq 5, Air EV, Model 3" required style="width: 100%; background-color: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.75rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; outline: none;">
                    </div>
                </div>

                <!-- Grid Nomor Polisi & Kapasitas Baterai -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                            Nomor Polisi (Plat)
                        </label>
                        <input type="text" name="nomor_polisi" value="{{ old('nomor_polisi') }}" placeholder="Contoh: B 1234 EV" required style="width: 100%; background-color: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.75rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                            Kapasitas Baterai (kWh)
                        </label>
                        <input type="number" step="0.1" name="kapasitas_baterai_kwh" value="{{ old('kapasitas_baterai_kwh') }}" placeholder="Contoh: 58.0" required style="width: 100%; background-color: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.75rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; outline: none;">
                    </div>
                </div>

                <!-- Tipe Konektor -->
                <div style="margin-bottom: 1.75rem;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                        Tipe Konektor Pengisian Utama
                    </label>
                    <select name="tipe_konektor" required style="width: 100%; background-color: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.75rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; outline: none;">
                        <option value="" disabled selected>-- Pilih Tipe Konektor --</option>
                        <option value="CCS2 (Fast Charging)">🔌 CCS2 (Fast Charging / DC)</option>
                        <option value="Type 2 (AC)">🔌 Type 2 (Standard AC)</option>
                        <option value="CHAdeMO">🔌 CHAdeMO</option>
                        <option value="GB/T">🔌 GB/T</option>
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <a href="{{ route('cars.index') }}" style="background-color: #334155; color: #CBD5E1; font-weight: 700; text-decoration: none; padding: 0.65rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem;">
                        Batal
                    </a>
                    <button type="submit" style="background-color: #2563EB; color: #FFFFFF; font-weight: 700; border: none; padding: 0.65rem 1.5rem; border-radius: 0.75rem; font-size: 0.875rem; cursor: pointer; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">
                        Simpan Mobil Baru →
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>