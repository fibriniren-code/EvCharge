<x-app-layout>
    <div class="min-h-screen py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white">
                    Riwayat Transaksi
                </h1>

                <p class="text-sm text-slate-400 mt-2">
                    Semua riwayat pengisian daya kendaraan listrik kamu.
                </p>
            </div>

            @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-5 p-4 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-400">
                    {{ session('info') }}
                </div>
            @endif

            @forelse($transactions as $transaction)

                @php
                    // 1. Relasi Lokasi & Kendaraan
                    $location = $transaction->charger->location ?? $transaction->location ?? null;
                    $vehicle  = $transaction->reservation->vehicle ?? $transaction->vehicle ?? null;

                    // 2. Ambil Waktu Mulai & Selesai
                    $startRaw = $transaction->waktu_mulai ?? $transaction->start_time ?? $transaction->created_at ?? null;
                    $endRaw   = $transaction->waktu_selesai ?? $transaction->end_time ?? $transaction->stopped_at ?? $transaction->updated_at ?? null;

                    $start = $startRaw ? \Carbon\Carbon::parse($startRaw) : null;
                    $end   = $endRaw ? \Carbon\Carbon::parse($endRaw) : null;

                    // 3. Durasi (Menit)
                    $duration = ($start && $end)
                        ? max(1, ceil($start->diffInSeconds($end) / 60))
                        : 0;

                    // 4. Ambil Nilai Energi (kWh) & Biaya dari DB
                    $energy = (float) ($transaction->energi_kwh ?? $transaction->energy_kwh ?? $transaction->energy_consumed ?? 0);
                    $cost   = (float) ($transaction->estimasi_biaya ?? $transaction->total_cost ?? 0);

                    // 5. KALKULASI FALLBACK: Jika di DB bernilai 0 (Transaksi lama / durasi singkat)
                    if ($energy <= 0 && $start && $end) {
                        $secondsElapsed = max(60, $start->diffInSeconds($end));
                        $powerKw = $transaction->charger->power_kw ?? 22;
                        $energy  = round(($powerKw / 3600) * $secondsElapsed, 2);
                        if ($energy <= 0) { $energy = 0.37; } // Batas minimum kWh fallback
                    }

                    if ($cost <= 0 && $energy > 0) {
                        $tarifPerKwh = 2466;
                        $cost = round($energy * $tarifPerKwh);
                    }

                    // 6. ID Session
                    $sessionId = $transaction->id_session ?? $transaction->id_reservation ?? $transaction->id;

                    // 7. Penanganan Route Struk Aman
                    $receiptUrl = '#';
                    if (\Illuminate\Support\Facades\Route::has('driver.charging.receipt')) {
                        $receiptUrl = route('driver.charging.receipt', $sessionId);
                    } elseif (\Illuminate\Support\Facades\Route::has('charging.receipt')) {
                        $receiptUrl = route('charging.receipt', $sessionId);
                    }
                @endphp

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 mb-5 shadow-lg">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">

                        <div>
                            <p class="text-xs text-slate-400">
                                ID TRANSAKSI
                            </p>

                            <h2 class="text-lg font-bold text-white">
                                #EV-TRX-{{ $sessionId }}
                            </h2>

                            <p class="text-xs text-slate-400 mt-1">
                                {{ $start ? $start->format('d M Y, H:i') . ' WIB' : '-' }}
                            </p>
                        </div>

                        <span class="px-4 py-2 rounded-full text-xs font-bold bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 w-fit">
                            Selesai
                        </span>

                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                        <div>
                            <p class="text-xs text-slate-400 mb-1">
                                Lokasi SPKLU
                            </p>

                            <p class="text-sm font-semibold text-white">
                                {{ $location->nama_lokasi ?? $location->name ?? 'SPKLU Voltron Yogyakarta' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 mb-1">
                                Kendaraan
                            </p>

                            <p class="text-sm font-semibold text-white">
                                @if($vehicle)
                                    {{ ($vehicle->merek ?? $vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '') }}
                                @else
                                    Tesla 3 Long Range
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 mb-1">
                                Energi Terisi
                            </p>

                            <p class="text-sm font-bold text-blue-400">
                                {{ number_format($energy, 2, ',', '.') }} kWh
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 mb-1">
                                Total Biaya
                            </p>

                            <p class="text-sm font-bold text-amber-400">
                                Rp {{ number_format($cost, 0, ',', '.') }}
                            </p>
                        </div>

                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-5 pt-5 border-t border-slate-800">

                        <p class="text-xs text-slate-400">
                            Durasi: {{ $duration }} menit
                        </p>

                        <div class="flex flex-wrap gap-2">

                            <a href="{{ $receiptUrl }}"
                               class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold transition">
                                Lihat Struk
                            </a>

                            <a href="{{ $receiptUrl }}?print=1"
                               target="_blank"
                               class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition">
                                Cetak Ulang Struk
                            </a>

                        </div>
                    </div>
                </div>

            @empty

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center">

                    <div class="text-4xl mb-4">
                        ⚡
                    </div>

                    <h3 class="text-lg font-bold text-white">
                        Belum Ada Riwayat Transaksi
                    </h3>

                    <p class="text-sm text-slate-400 mt-2">
                        Transaksi charging yang sudah selesai akan muncul di sini.
                    </p>

                    @if(\Illuminate\Support\Facades\Route::has('spklu.index'))
                        <a href="{{ route('spklu.index') }}"
                           class="inline-block mt-6 px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold">
                            Cari SPKLU
                        </a>
                    @endif

                </div>

            @endforelse

            @if(method_exists($transactions, 'hasPages') && $transactions->hasPages())
                <div class="mt-6">
                    {{ $transactions->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>