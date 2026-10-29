<x-app-layout>
    <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem;">

        <!-- Breadcrumb -->
        <div style="margin-bottom: 0.5rem; font-size: 0.8rem; color: #60A5FA; font-weight: 600;">
            <a href="{{ route('spklu.index') }}" style="color: #60A5FA; text-decoration: none;">Daftar SPKLU</a>
            <span style="color: #64748B; margin: 0 0.3rem;">/</span>
            <span style="color: #94A3B8;">Reservasi Slot</span>
        </div>

        <!-- Header -->
        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0;">
                Reservasi Slot Charging
            </h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.25rem;">
                Tentukan target energi, jenis pengisian, waktu, dan durasi reservasi.
            </p>
        </div>

        <!-- Error Message -->
        @if ($errors->any())
            <div style="background: rgba(239,68,68,0.2); border: 1px solid #EF4444; color: #FCA5A5; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem;">
                <strong>Gagal melakukan reservasi:</strong>
                <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div style="background: rgba(239,68,68,0.2); border: 1px solid #EF4444; color: #FCA5A5; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem;">
                {{ session('error') }}
            </div>
        @endif

        <!-- Informasi SPKLU -->
        <div style="background: linear-gradient(135deg, rgba(30,58,138,0.5), rgba(15,23,42,0.9)); border: 1px solid rgba(59,130,246,0.3); border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="background: rgba(59,130,246,0.2); color: #93C5FD; border: 1px solid rgba(147,197,253,0.3); font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 9999px; display: inline-block; margin-bottom: 0.5rem;">
                    STASIUN SPKLU
                </span>

                <h3 style="font-size: 1.25rem; font-weight: 800; color: #FFFFFF; margin: 0 0 0.25rem;">
                    {{ $location->name ?? $location->nama_lokasi ?? 'SPKLU' }}
                </h3>

                <p style="font-size: 0.8rem; color: #94A3B8; margin: 0;">
                    📍 {{ $location->address ?? $location->alamat ?? 'Alamat tidak tersedia' }}
                </p>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <div style="background: #0F172A; border: 1px solid #334155; padding: 0.75rem; border-radius: 0.75rem; text-align: center;">
                    <span style="display: block; font-size: 0.6rem; color: #94A3B8; font-weight: 700;">SLOT / CHARGER</span>
                    <span style="font-size: 0.85rem; font-weight: 800; color: #60A5FA;">
                        {{ $charger->name ?? 'Charger #'.$charger->id_charger }}
                    </span>
                </div>

                <div style="background: #0F172A; border: 1px solid #334155; padding: 0.75rem; border-radius: 0.75rem; text-align: center;">
                    <span style="display: block; font-size: 0.6rem; color: #94A3B8; font-weight: 700;">TIPE KONEKTOR</span>
                    <span style="font-size: 0.85rem; font-weight: 800; color: #34D399;">
                        {{ $charger->charger_type ?? $charger->connector_type ?? $charger->tipe_konektor ?? '-' }}
                    </span>
                </div>

                <div style="background: #0F172A; border: 1px solid #334155; padding: 0.75rem; border-radius: 0.75rem; text-align: center;">
                    <span style="display: block; font-size: 0.6rem; color: #94A3B8; font-weight: 700;">DAYA MAKSIMAL</span>
                    <span style="font-size: 0.85rem; font-weight: 800; color: #FBBF24;">
                        {{ $charger->power_kw ?? $charger->daya_kw ?? 0 }} kW
                    </span>
                </div>
            </div>
        </div>

        <!-- Form Reservasi -->
        <div style="background: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);">

            <form action="{{ route('reservations.store') }}" method="POST">
                @csrf

                <input type="hidden" name="location_id" value="{{ $location->id_location ?? $location->id }}">
                <input type="hidden" name="charger_id" value="{{ $charger->id_charger ?? $charger->id }}">

                <!-- Hidden Input untuk Kendaraan (Otomatis membawa vehicle_id pertama pengguna) -->
                @if(isset($vehicles) && $vehicles->isNotEmpty())
                    <input type="hidden" name="vehicle_id" value="{{ $vehicles->first()->id_vehicle ?? $vehicles->first()->id }}">
                @endif

                <!-- Target kWh -->
                <div style="margin-bottom: 1.25rem;">
                    <label for="energi_kwh" style="display: block; font-size: 0.9rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                        Target Energi Pengisian (kWh)
                    </label>

                    <select id="energi_kwh" name="energi_kwh" required
                            style="width: 100%; background: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.85rem 1rem; border-radius: 0.75rem; font-size: 0.9rem;">
                        @foreach ([5, 10, 15, 20, 30, 40, 50, 60, 80, 100] as $kwh)
                            <option value="{{ $kwh }}" {{ old('energi_kwh', 20) == $kwh ? 'selected' : '' }}>
                                {{ $kwh }} kWh{{ $kwh == 40 ? ' — Contoh kapasitas baterai 40 kWh' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <small style="display: block; color: #94A3B8; margin-top: 0.4rem;">
                        Pilih jumlah energi yang ingin diisikan ke baterai.
                    </small>
                </div>

                <!-- Jenis Charging -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="jenis_pengisian" style="display: block; font-size: 0.9rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                        Jenis Pengisian
                    </label>

                    <select id="jenis_pengisian" name="jenis_pengisian" required
                            style="width: 100%; background: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.85rem 1rem; border-radius: 0.75rem; font-size: 0.9rem;">
                        <option value="normal" {{ old('jenis_pengisian', 'normal') === 'normal' ? 'selected' : '' }}>
                            Normal / Standar
                        </option>
                        <option value="fast" {{ old('jenis_pengisian') === 'fast' ? 'selected' : '' }}>
                            Fast Charging
                        </option>
                    </select>
                </div>

                <!-- Estimasi Biaya -->
                <div style="background: #0F172A; border: 1px solid #334155; border-radius: 1rem; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <p style="color: #94A3B8; font-size: 0.75rem; font-weight: 800; margin: 0 0 0.35rem;">
                        ESTIMASI TOTAL BIAYA
                    </p>

                    <div id="total-harga" style="color: #34D399; font-size: 2rem; font-weight: 800;">
                        Rp49.320
                    </div>

                    <div style="height: 1px; background: #334155; margin: 1rem 0;"></div>

                    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.65rem;">
                        <span style="color: #94A3B8;">Target energi</span>
                        <strong id="rincian-kwh" style="color: #FFFFFF;">20 kWh</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.65rem;">
                        <span style="color: #94A3B8;">Tarif energi</span>
                        <strong style="color: #FFFFFF;">Rp2.466/kWh</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.65rem;">
                        <span style="color: #94A3B8;">Biaya energi</span>
                        <strong id="biaya-energi" style="color: #FFFFFF;">Rp49.320</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; gap: 1rem;">
                        <span style="color: #94A3B8;">Biaya layanan fast charging</span>
                        <strong id="biaya-layanan" style="color: #FFFFFF;">Rp0</strong>
                    </div>

                    <div style="background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.25); padding: 0.85rem; border-radius: 0.75rem; margin-top: 1rem;">
                        <p style="color: #93C5FD; font-size: 0.8rem; margin: 0 0 0.4rem;">
                            <strong>Rumus Perhitungan</strong>
                        </p>
                        <p id="rumus-harga" style="color: #CBD5E1; font-size: 0.8rem; margin: 0;">
                            20 kWh × Rp2.466 = Rp49.320
                        </p>
                    </div>

                    <p style="color: #64748B; font-size: 0.72rem; margin: 1rem 0 0;">
                        Tarif di atas merupakan simulasi. Harga aktual bisa berbeda sesuai tarif dan biaya layanan SPKLU.
                    </p>
                </div>

                <!-- Waktu dan Durasi -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                    <div>
                        <label for="start_time" style="display: block; font-size: 0.85rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                            Waktu Mulai Pengisian
                        </label>

                        <input id="start_time" type="datetime-local" name="start_time"
                               value="{{ old('start_time', \Carbon\Carbon::now()->addMinutes(5)->format('Y-m-d\TH:i')) }}"
                               min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}"
                               required
                               style="width: 100%; background: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.85rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; color-scheme: dark;">

                        <small style="color: #94A3B8; font-size: 0.7rem; margin-top: 0.25rem; display: block;">
                            Pilih waktu mulai sesuai ketersediaan slot.
                        </small>
                    </div>

                    <div>
                        <label for="duration_hours" style="display: block; font-size: 0.85rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                            Durasi Reservasi
                        </label>

                        <select id="duration_hours" name="duration_hours" required
                                style="width: 100%; background: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.85rem 1rem; border-radius: 0.75rem; font-size: 0.875rem;">
                            @foreach ([1, 2, 3, 4] as $jam)
                                <option value="{{ $jam }}" {{ old('duration_hours', 1) == $jam ? 'selected' : '' }}>
                                    ⏱️ {{ $jam }} Jam
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Informasi Holding Lock -->
                <div style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 0.75rem; padding: 0.85rem 1rem; margin-bottom: 1.75rem; display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.25rem;">⏳</span>
                    <p style="font-size: 0.8rem; color: #FCD34D; margin: 0; line-height: 1.5;">
                        Slot charger ditahan selama <strong>15 menit</strong> setelah reservasi dibuat. Selesaikan konfirmasi sebelum batas waktu habis.
                    </p>
                </div>

                <!-- Tombol -->
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="{{ route('spklu.index') }}"
                       style="background: #334155; color: #CBD5E1; font-weight: 700; text-decoration: none; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem;">
                        Batal
                    </a>

                    <button type="submit"
                            style="background: #2563EB; color: #FFFFFF; font-weight: 700; border: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-size: 0.875rem; cursor: pointer; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">
                        Lanjutkan Reservasi →
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kalkulasi harga otomatis -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputKwh = document.getElementById('energi_kwh');
            const inputJenis = document.getElementById('jenis_pengisian');

            const totalHarga = document.getElementById('total-harga');
            const rincianKwh = document.getElementById('rincian-kwh');
            const biayaEnergi = document.getElementById('biaya-energi');
            const biayaLayanan = document.getElementById('biaya-layanan');
            const rumusHarga = document.getElementById('rumus-harga');

            const TARIF_PER_KWH = 2466;
            const BIAYA_FAST = 25000;

            function rupiah(angka) {
                return 'Rp' + Math.round(angka).toLocaleString('id-ID');
            }

            function hitungHarga() {
                const kwh = Number(inputKwh.value);
                const energi = kwh * TARIF_PER_KWH;
                const layanan = inputJenis.value === 'fast' ? BIAYA_FAST : 0;
                const total = energi + layanan;

                rincianKwh.textContent = kwh + ' kWh';
                biayaEnergi.textContent = rupiah(energi);
                biayaLayanan.textContent = rupiah(layanan);
                totalHarga.textContent = rupiah(total);

                if (layanan > 0) {
                    rumusHarga.textContent =
                        '(' + kwh + ' kWh × ' + rupiah(TARIF_PER_KWH) + ') + ' +
                        rupiah(BIAYA_FAST) + ' = ' + rupiah(total);
                } else {
                    rumusHarga.textContent =
                        kwh + ' kWh × ' + rupiah(TARIF_PER_KWH) + ' = ' +
                        rupiah(total);
                }
            }

            inputKwh.addEventListener('change', hitungHarga);
            inputJenis.addEventListener('change', hitungHarga);

            hitungHarga();
        });
    </script>
</x-app-layout>