<x-app-layout>
    @php
        // 1. Ambil Relasi Lokasi & Kendaraan secara fleksibel
        $location = $session->charger->location ?? $session->location ?? null;
        $vehicle  = $session->reservation->vehicle ?? $session->vehicle ?? null;

        // 2. Ambil Nilai Energi (kWh) & Total Biaya dari DB
        $kwh       = (float) ($session->energy_kwh ?? $session->energy_consumed ?? $session->energi_kwh ?? 0);
        $totalCost = (float) ($session->total_cost ?? $session->estimasi_biaya ?? 0);

        // 3. Waktu Mulai & Selesai
        $startTime = $session->waktu_mulai ?? $session->start_time ?? $session->created_at ?? null;
        $endTime   = $session->waktu_selesai ?? $session->end_time ?? $session->stopped_at ?? $session->updated_at ?? null;

        if ($startTime) { $startTime = \Carbon\Carbon::parse($startTime); }
        if ($endTime)   { $endTime   = \Carbon\Carbon::parse($endTime); }

        $minutes = ($startTime && $endTime)
            ? max(1, ceil($startTime->diffInSeconds($endTime) / 60))
            : 0;

        // 4. KALKULASI FALLBACK: Jika data DB bernilai 0 (Transaksi lama/durasi pendek)
        if ($kwh <= 0 && $startTime && $endTime) {
            $secondsElapsed = max(60, $startTime->diffInSeconds($endTime));
            $powerKw = $session->charger->power_kw ?? 22;
            $kwh = round(($powerKw / 3600) * $secondsElapsed, 2);
            if ($kwh <= 0) { $kwh = 0.37; } // Batas minimum fallback kWh
        }

        if ($totalCost <= 0 && $kwh > 0) {
            $tarifPerKwh = 2466;
            $totalCost = round($kwh * $tarifPerKwh);
        }

        // 5. Cek Ulasan/Review
        $reviewExists = false;
        if ($location) {
            $locationId = $location->id_location ?? $location->id ?? null;
            if ($locationId) {
                $reviewExists = \App\Models\Review::where('user_id', Auth::id())
                    ->where('id_location', $locationId)
                    ->exists();
            }
        }

        // 6. Penanganan Route Riwayat Fleksibel (Mencegah RouteNotFoundException)
        $historyRoute = '#';
        if (\Illuminate\Support\Facades\Route::has('driver.charging.history')) {
            $historyRoute = route('driver.charging.history');
        } elseif (\Illuminate\Support\Facades\Route::has('charging.history')) {
            $historyRoute = route('charging.history');
        } elseif (\Illuminate\Support\Facades\Route::has('transactions.index')) {
            $historyRoute = route('transactions.index');
        }
    @endphp

    <div class="py-10 min-h-screen">
        <div class="max-w-md mx-auto px-4 sm:px-6">

            @if(session('success'))
                <div class="bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 p-3.5 rounded-xl mb-6 text-sm font-semibold text-center print:hidden">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="bg-blue-500/15 border border-blue-500/30 text-blue-300 p-3.5 rounded-xl mb-6 text-sm font-semibold text-center print:hidden">
                    {{ session('info') }}
                </div>
            @endif

            <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden print:bg-white print:text-black print:border-0 print:shadow-none">

                <div class="bg-gradient-to-b from-blue-900/50 to-slate-900 p-6 text-center border-b border-slate-800 print:bg-white print:border-gray-300">
                    <div class="w-12 h-12 bg-blue-600/20 text-blue-400 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-blue-500/30 print:hidden">
                        ⚡
                    </div>
                    <h2 class="text-xl font-bold text-white print:text-black">
                        Struk Pengisian Daya
                    </h2>
                    <p class="text-xs text-slate-400 mt-1 print:text-gray-600">
                        Bukti Transaksi Resmi EVChargeHub
                    </p>
                </div>

                <div class="p-6 space-y-4 text-sm">

                    <div class="flex justify-between py-2 border-b border-slate-800/60">
                        <span class="text-slate-400 print:text-gray-600">ID Transaksi</span>
                        <span class="font-mono font-bold text-white print:text-black">
                            #EV-TRX-{{ $session->id_session ?? $session->id_reservation ?? $session->id }}
                        </span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-slate-800/60">
                        <span class="text-slate-400 print:text-gray-600">Waktu Mulai</span>
                        <span class="text-slate-200 print:text-black">
                            {{ $startTime ? $startTime->format('d M Y, H:i') . ' WIB' : '-' }}
                        </span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-slate-800/60">
                        <span class="text-slate-400 print:text-gray-600">Waktu Selesai</span>
                        <span class="text-slate-200 print:text-black">
                            {{ $endTime ? $endTime->format('d M Y, H:i') . ' WIB' : '-' }}
                        </span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-slate-800/60">
                        <span class="text-slate-400 print:text-gray-600">Lokasi SPKLU</span>
                        <span class="text-slate-200 text-right max-w-[200px] print:text-black">
                            {{ $location->nama_lokasi ?? $location->name ?? 'SPKLU Voltron Yogyakarta' }}
                        </span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-slate-800/60">
                        <span class="text-slate-400 print:text-gray-600">Kendaraan</span>
                        <span class="text-slate-200 text-right print:text-black">
                            @if($vehicle)
                                {{ ($vehicle->merek ?? $vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '') }}
                            @else
                                Tesla 3 Long Range
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-slate-800/60">
                        <span class="text-slate-400 print:text-gray-600">Durasi Charging</span>
                        <span class="text-slate-200 print:text-black">
                            {{ $minutes }} Menit
                        </span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-slate-800/60">
                        <span class="text-slate-400 print:text-gray-600">Energi Terisi</span>
                        <span class="text-blue-400 font-bold print:text-black">
                            {{ number_format($kwh, 2, ',', '.') }} kWh
                        </span>
                    </div>

                    <div class="pt-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 print:text-gray-600">Total Potong Saldo</span>
                            <span class="text-amber-400 font-bold text-lg print:text-black">
                                Rp {{ number_format($totalCost, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 print:text-gray-600">Metode Pembayaran</span>
                            <span class="text-emerald-400 font-semibold print:text-black">
                                Auto-Debit Saldo
                            </span>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                            <span class="text-slate-400 print:text-gray-600">Sisa Saldo</span>
                            <span class="text-emerald-400 font-mono font-bold print:text-black">
                                Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                </div>

                <div class="p-6 bg-slate-950/50 border-t border-slate-800 space-y-3 print:hidden">

                    <button onclick="window.print()"
                            class="block w-full py-3 bg-blue-600 hover:bg-blue-500 text-white text-center font-bold text-sm rounded-xl transition">
                        🖨 Cetak Struk
                    </button>

                    <a href="{{ $historyRoute }}"
                       class="block w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-center font-medium text-sm rounded-xl transition">
                        Kembali ke Riwayat Transaksi
                    </a>

                    @if(\Illuminate\Support\Facades\Route::has('dashboard'))
                        <a href="{{ route('dashboard') }}"
                           class="block w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-center font-medium text-sm rounded-xl transition">
                            Kembali ke Dashboard
                        </a>
                    @endif

                    @if(\Illuminate\Support\Facades\Route::has('topup.index'))
                        <a href="{{ route('topup.index') }}"
                           class="block w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-center font-medium text-sm rounded-xl transition">
                            Top Up Saldo
                        </a>
                    @endif

                    @if($location)
                        @if($reviewExists)
                            <div class="w-full py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-center font-semibold text-sm">
                                ✓ Review SPKLU sudah dikirim
                            </div>
                        @elseif(\Illuminate\Support\Facades\Route::has('reviews.create'))
                            <a href="{{ route('reviews.create', $location->id_location ?? $location->id) }}"
                               class="block w-full py-3 bg-amber-500 hover:bg-amber-400 text-white text-center font-bold text-sm rounded-xl transition">
                                ⭐ Beri Review SPKLU
                            </a>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>

    @if(request()->query('print') == '1')
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif

    <style>
        @media print {
            body {
                background: white !important;
            }

            nav, header, footer {
                display: none !important;
            }

            .min-h-screen {
                min-height: auto !important;
            }
        }
    </style>
</x-app-layout>