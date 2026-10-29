<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Monitoring Pengisian Daya
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6">

            <!-- Status Pengisian -->
            <div class="text-center">
                <span id="chargingStatus"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                    <span id="statusDot"
                        class="w-2 h-2 mr-2 bg-gray-400 rounded-full"></span>
                    Siap Memulai Pengisian
                </span>

                <h3 class="text-3xl font-extrabold text-gray-900 mt-4">
                    Reservasi #{{ $reservation->id_reservation ?? $reservation->id }}
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $reservation->charger->location->nama_lokasi ?? 'SPKLU Station' }}
                </p>

                <p class="text-sm text-gray-500 mt-1">
                    Daya Charger:
                    <strong>
                        {{ $reservation->charger->daya_kw ?? $reservation->charger->power_kw ?? 22 }} kW
                    </strong>
                </p>
            </div>

            <!-- Input Target Pengisian -->
            <div class="bg-gray-50 rounded-xl p-5">
                <label for="targetKwh"
                    class="block text-sm font-bold text-gray-700 mb-2">
                    Target Pengisian Mobil (kWh)
                </label>

                <input
                    type="number"
                    id="targetKwh"
                    min="1"
                    max="200"
                    step="0.5"
                    value="20"
                    class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                >

                <p class="text-xs text-gray-500 mt-2">
                    Masukkan jumlah energi yang ingin ditambahkan ke mobil.
                </p>
            </div>

            <!-- Informasi Energi -->
            <div class="p-6 bg-slate-900 text-white rounded-2xl space-y-5">

                <div class="text-center">
                    <p class="text-xs text-slate-400 uppercase tracking-widest">
                        Energi Masuk ke Mobil
                    </p>

                    <div class="text-5xl font-black text-green-400 font-mono mt-3">
                        <span id="energyKwh">0.00</span>
                        <span class="text-xl text-slate-300">kWh</span>
                    </div>

                    <p class="text-sm text-slate-400 mt-2">
                        dari target <span id="targetLabel">20</span> kWh
                    </p>
                </div>

                <!-- Progress Bar -->
                <div>
                    <div class="flex justify-between text-xs text-slate-300 mb-2">
                        <span>Progres Pengisian</span>
                        <span id="progressText">0%</span>
                    </div>

                    <div class="w-full bg-slate-700 rounded-full h-4 overflow-hidden">
                        <div
                            id="chargingProgress"
                            class="bg-green-500 h-4 rounded-full transition-all duration-500"
                            style="width: 0%">
                        </div>
                    </div>
                </div>

                <!-- Durasi dan Sisa Target -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-800 rounded-xl p-4">
                        <p class="text-xs text-slate-400">Durasi Pengisian</p>
                        <p id="elapsedTime" class="text-lg font-bold mt-1">
                            0 menit
                        </p>
                    </div>

                    <div class="bg-slate-800 rounded-xl p-4">
                        <p class="text-xs text-slate-400">Sisa Target</p>
                        <p class="text-lg font-bold mt-1">
                            <span id="remainingKwh">20.00</span> kWh
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tombol Mulai dan Stop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button
                    type="button"
                    id="startButton"
                    onclick="startCharging()"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-bold text-sm transition">
                    Mulai Charging
                </button>

                <button
                    type="button"
                    id="stopButton"
                    onclick="stopCharging()"
                    disabled
                    class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-3 rounded-xl font-bold text-sm transition">
                    Stop Session
                </button>
            </div>

            <p class="text-xs text-gray-500 text-center">
                Pengguna dapat menghentikan pengisian kapan saja.
                Hasil akan menampilkan energi yang sudah masuk ke mobil.
            </p>

            <!-- Hasil Pengisian -->
            <div
                id="chargingResult"
                class="hidden border border-green-200 bg-green-50 rounded-2xl p-6">

                <h4 class="text-xl font-extrabold text-green-800 mb-4">
                    Hasil Pengisian Daya
                </h4>

                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex justify-between gap-4">
                        <span>Total Energi Masuk</span>
                        <strong id="finalKwh">0.00 kWh</strong>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span>Target Pengisian</span>
                        <strong id="finalTarget">20 kWh</strong>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span>Durasi Pengisian</span>
                        <strong id="finalDuration">0 menit</strong>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span>Status</span>
                        <strong id="finalStatus">Dihentikan</strong>
                    </div>
                </div>

                <div class="mt-5 p-4 bg-white rounded-xl">
                    <p class="text-sm text-gray-600">
                        Energi yang berhasil ditambahkan ke mobil:
                    </p>

                    <p class="text-3xl font-black text-green-700 mt-1">
                        <span id="finalEnergyBig">0.00</span> kWh
                    </p>
                </div>
            </div>

            <!-- Kembali ke Riwayat Reservasi -->
            <a
                href="{{ route('reservations.index') }}"
                class="block text-center w-full bg-slate-100 hover:bg-slate-200 text-slate-800 py-3 rounded-xl font-bold text-sm transition">
                Kembali ke Riwayat Reservasi
            </a>

        </div>
    </div>

    <script>
        // Daya charger diambil dari data reservasi Laravel.
        const chargerPowerKw = Number(
            @json((float) ($reservation->charger->daya_kw ?? $reservation->charger->power_kw ?? 22))
        );

        let chargingInterval = null;
        let energy = 0;
        let elapsedSeconds = 0;
        let isCharging = false;

        function startCharging() {
            if (isCharging) return;

            const targetInput = document.getElementById('targetKwh');
            const target = Number(targetInput.value);

            if (!Number.isFinite(target) || target < 1 || target > 200) {
                alert('Masukkan target pengisian antara 1 sampai 200 kWh.');
                return;
            }

            if (!Number.isFinite(chargerPowerKw) || chargerPowerKw <= 0) {
                alert('Daya charger tidak valid.');
                return;
            }

            energy = 0;
            elapsedSeconds = 0;
            isCharging = true;

            targetInput.disabled = true;

            document.getElementById('targetLabel').textContent =
                target.toFixed(2);

            document.getElementById('chargingResult').classList.add('hidden');

            document.getElementById('startButton').disabled = true;
            document.getElementById('stopButton').disabled = false;

            setStatus('Pengisian Sedang Berlangsung', 'running');

            updateChargingDisplay(target);

            // Simulasi: 1 detik di browser = 1 menit waktu pengisian.
            chargingInterval = setInterval(function () {
                if (!isCharging) return;

                elapsedSeconds += 60;

                // Energi bertambah berdasarkan daya charger.
                // Daya (kW) / 60 = kWh per menit.
                energy += chargerPowerKw / 60;

                if (energy >= target) {
                    energy = target;
                    updateChargingDisplay(target);
                    stopCharging('Target Tercapai');
                    return;
                }

                updateChargingDisplay(target);
            }, 1000);
        }

        function updateChargingDisplay(target) {
            const percentage = Math.min((energy / target) * 100, 100);
            const remaining = Math.max(target - energy, 0);

            document.getElementById('energyKwh').textContent =
                energy.toFixed(2);

            document.getElementById('progressText').textContent =
                percentage.toFixed(0) + '%';

            document.getElementById('chargingProgress').style.width =
                percentage + '%';

            document.getElementById('elapsedTime').textContent =
                Math.floor(elapsedSeconds / 60) + ' menit';

            document.getElementById('remainingKwh').textContent =
                remaining.toFixed(2);
        }

        function stopCharging(status = 'Dihentikan Pengguna') {
            if (!isCharging) return;

            const target = Number(
                document.getElementById('targetKwh').value
            );

            isCharging = false;

            if (chargingInterval !== null) {
                clearInterval(chargingInterval);
                chargingInterval = null;
            }

            document.getElementById('startButton').disabled = false;
            document.getElementById('stopButton').disabled = true;
            document.getElementById('targetKwh').disabled = false;

            setStatus('Pengisian Berhenti', 'stopped');

            // Tampilkan hasil akhir.
            document.getElementById('finalKwh').textContent =
                energy.toFixed(2) + ' kWh';

            document.getElementById('finalEnergyBig').textContent =
                energy.toFixed(2);

            document.getElementById('finalTarget').textContent =
                target.toFixed(2) + ' kWh';

            document.getElementById('finalDuration').textContent =
                Math.floor(elapsedSeconds / 60) + ' menit';

            document.getElementById('finalStatus').textContent = status;

            document.getElementById('chargingResult').classList.remove('hidden');

            updateChargingDisplay(target);
        }

        function setStatus(message, state) {
            const statusElement = document.getElementById('chargingStatus');
            const dotElement = document.getElementById('statusDot');

            statusElement.className =
                'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold';

            if (state === 'running') {
                statusElement.classList.add('bg-green-100', 'text-green-700');
                dotElement.className =
                    'w-2 h-2 mr-2 bg-green-500 rounded-full animate-ping';
            } else if (state === 'stopped') {
                statusElement.classList.add('bg-red-100', 'text-red-700');
                dotElement.className =
                    'w-2 h-2 mr-2 bg-red-500 rounded-full';
            } else {
                statusElement.classList.add('bg-gray-100', 'text-gray-700');
                dotElement.className =
                    'w-2 h-2 mr-2 bg-gray-400 rounded-full';
            }

            // Perbarui teks status tanpa menghapus indikator titik.
            statusElement.lastChild.textContent = ' ' + message;
        }
    </script>
</x-app-layout>
