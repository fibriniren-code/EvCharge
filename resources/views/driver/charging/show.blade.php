<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            {{ __('Sesi Pengisian Daya Aktif') }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="chargingMonitor('{{ route('driver.charging.telemetry', $session->id_session) }}')">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 px-4 space-y-6">
            
            <!-- Card Monitoring Utama -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 shadow-sm text-center">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Mengisi Daya (Charging)</span>
                </div>

                <!-- Display kWh & Timer -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-slate-950 p-4 rounded-lg border border-slate-800">
                        <p class="text-xs text-slate-400 font-medium">Energi Terpakai</p>
                        <p class="text-2xl font-black text-emerald-400 mt-1" x-text="kwh + ' kWh'"></p>
                    </div>
                    <div class="bg-slate-950 p-4 rounded-lg border border-slate-800">
                        <p class="text-xs text-slate-400 font-medium">Durasi Berjalan</p>
                        <p class="text-2xl font-black text-slate-100 mt-1" x-text="duration"></p>
                    </div>
                </div>

                <!-- Display Total Biaya -->
                <div class="bg-slate-950 p-4 rounded-lg border border-slate-800 mb-6">
                    <p class="text-xs text-slate-400 font-medium">Estimasi Biaya Saat Ini</p>
                    <p class="text-3xl font-extrabold text-slate-100 mt-1" x-text="'Rp ' + cost.toLocaleString('id-ID')"></p>
                </div>

                <!-- Tombol Stop Session (FR-04) -->
                <form action="{{ route('driver.charging.stop', $session->id_session) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghentikan pengisian daya?')">
                    @csrf
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-lg shadow-rose-600/20">
                        Hentikan Pengisian (Stop Session)
                    </button>
                </form>
            </div>

            <!-- Detail Stasiun & Kendaraan -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 text-sm text-slate-300 space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-400">Lokasi / Stasiun:</span>
                    <span class="font-semibold text-slate-100">{{ $session->charger->location->name ?? 'SPKLU Station' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kendaraan:</span>
                    <span class="font-semibold text-slate-100">{{ $session->vehicle->brand ?? '' }} {{ $session->vehicle->model ?? '-' }}</span>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function chargingMonitor(telemetryUrl) {
            return {
                kwh: '0.00',
                cost: 0,
                duration: '00:00:00',
                timer: null,

                init() {
                    this.fetchTelemetry();
                    // Polling data telemetry setiap 3 detik
                    this.timer = setInterval(() => this.fetchTelemetry(), 3000);
                },

                fetchTelemetry() {
                    fetch(telemetryUrl)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'completed') {
                                clearInterval(this.timer);
                                window.location.href = "{{ route('driver.charging.receipt', $session->id_session) }}";
                            } else {
                                this.kwh = data.energy_kwh;
                                this.cost = data.total_cost;
                                this.duration = data.formatted_time;
                            }
                        })
                        .catch(err => console.error("Gagal mengambil data telemetry:", err));
                }
            }
        }
    </script>
    @endpush
</x-app-layout>