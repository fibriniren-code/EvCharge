<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            {{ __('Mulai Pengisian Daya SPKLU') }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ step: 'scan', chargerId: null, reservationId: null, chargingType: 'full' }">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 px-4">
            
            <!-- STEP 1: SCAN QR CODE -->
            <div x-show="step === 'scan'" class="bg-slate-900 border border-slate-800 overflow-hidden shadow-sm rounded-lg p-6 text-center">
                <p class="text-slate-400 mb-4 text-sm">
                    Arahkan kamera ke QR Code yang tertera pada mesin charger untuk memulai pengisian daya.
                </p>

                <div id="qr-reader" class="w-full mx-auto rounded-lg overflow-hidden border border-slate-700 bg-slate-950"></div>
                <div id="qr-reader-results" class="mt-4 font-semibold text-emerald-400 min-h-[24px]"></div>
            </div>

            <!-- STEP 2: FORM PILIH NOMINAL / DURASI (NFR-05) -->
            <div x-show="step === 'confirm'" x-cloak class="bg-slate-900 border border-slate-800 rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-100 mb-1">Pilih Nominal Pengisian</h3>
                <p class="text-slate-400 mb-6 text-sm">Tentukan target kWh atau nominal biaya sebelum pengisian dimulai.</p>

                <form action="{{ route('driver.charging.start') }}" method="POST">
                    @csrf
                    <input type="hidden" name="charger_id" :value="chargerId">
                    <input type="hidden" name="reservation_id" :value="reservationId">

                    <div class="space-y-3 mb-6">
                        <!-- Pilihan 1: Full Charge -->
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
                            :class="chargingType === 'full' ? 'border-emerald-500 bg-slate-800' : 'border-slate-800 bg-slate-950'">
                            <input type="radio" name="charging_type" value="full" x-model="chargingType" class="text-emerald-500 focus:ring-emerald-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-slate-100">Full Charge</span>
                                <span class="block text-xs text-slate-400">Pengisian daya hingga baterai penuh</span>
                            </div>
                        </label>

                        <!-- Pilihan 2: Berdasarkan kWh -->
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
                            :class="chargingType === 'kwh' ? 'border-emerald-500 bg-slate-800' : 'border-slate-800 bg-slate-950'">
                            <input type="radio" name="charging_type" value="kwh" x-model="chargingType" class="text-emerald-500 focus:ring-emerald-500">
                            <div class="ml-3 w-full">
                                <span class="block text-sm font-medium text-slate-100">Target Energi (kWh)</span>
                                <input x-show="chargingType === 'kwh'" type="number" name="target_value" placeholder="Contoh: 10" 
                                    class="mt-2 w-full bg-slate-900 border border-slate-700 rounded p-2 text-sm text-slate-100 focus:outline-none focus:border-emerald-500">
                            </div>
                        </label>

                        <!-- Pilihan 3: Berdasarkan Nominal Rupiah -->
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
                            :class="chargingType === 'nominal' ? 'border-emerald-500 bg-slate-800' : 'border-slate-800 bg-slate-950'">
                            <input type="radio" name="charging_type" value="nominal" x-model="chargingType" class="text-emerald-500 focus:ring-emerald-500">
                            <div class="ml-3 w-full">
                                <span class="block text-sm font-medium text-slate-100">Target Nominal (Rp)</span>
                                <input x-show="chargingType === 'nominal'" type="number" name="target_value" placeholder="Contoh: 50000" 
                                    class="mt-2 w-full bg-slate-900 border border-slate-700 rounded p-2 text-sm text-slate-100 focus:outline-none focus:border-emerald-500">
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                        Mulai Charging
                    </button>
                </form>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const resultContainer = document.getElementById('qr-reader-results');

            function onScanSuccess(decodedText) {
                html5QrcodeScanner.clear().then(() => {
                    resultContainer.className = "mt-4 font-semibold text-emerald-400";
                    resultContainer.innerText = 'QR Code Berhasil Dibaca!';

                    fetch("{{ route('driver.charging.processScan') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ qr_code: decodedText })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Berhasil scan -> Pindah ke Form Nominal (Step 2)
                            const root = document.querySelector('[x-data]');
                            root._x_dataStack[0].chargerId = data.charger_id;
                            root._x_dataStack[0].reservationId = data.reservation_id;
                            root._x_dataStack[0].step = 'confirm';
                        } else {
                            resultContainer.className = "mt-4 font-semibold text-rose-400";
                            resultContainer.innerText = 'Gagal: ' + (data.message || 'QR Code tidak valid');
                            setTimeout(() => location.reload(), 2500);
                        }
                    })
                    .catch(() => {
                        resultContainer.className = "mt-4 font-semibold text-rose-400";
                        resultContainer.innerText = 'Terjadi kesalahan sistem.';
                        setTimeout(() => location.reload(), 2500);
                    });
                });
            }

            let html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader", 
                { fps: 10, qrbox: { width: 250, height: 250 }, facingMode: "environment" }, 
                false
            );
            html5QrcodeScanner.render(onScanSuccess, () => {});
        });
    </script>
    @endpush
</x-app-layout>