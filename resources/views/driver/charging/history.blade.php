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
                    $location = $transaction->charger->location ?? null;
                    $vehicle = $transaction->reservation->vehicle ?? null;

                    $start = $transaction->waktu_mulai;
                    $end = $transaction->waktu_selesai;

                    $energy = (float) ($transaction->energi_kwh ?? 0);
                    $cost = (float) ($transaction->estimasi_biaya ?? 0);

                    $duration = ($start && $end)
                        ? max(1, ceil($start->diffInSeconds($end) / 60))
                        : 0;
                @endphp

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 mb-5 shadow-lg">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">

                        <div>
                            <p class="text-xs text-slate-400">
                                ID TRANSAKSI
                            </p>

                            <h2 class="text-lg font-bold text-white">
                                #EV-TRX-{{ $transaction->id_session }}
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
                                {{ $location->nama_lokasi ?? 'Lokasi tidak tersedia' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 mb-1">
                                Kendaraan
                            </p>

                            <p class="text-sm font-semibold text-white">
                                {{ $vehicle ? $vehicle->merek . ' ' . $vehicle->model : 'Tidak tersedia' }}
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

                            <a href="{{ route('driver.charging.receipt', $transaction->id_session) }}"
                               class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold transition">
                                Lihat Struk
                            </a>

                            <a href="{{ route('driver.charging.receipt', $transaction->id_session) }}?print=1"
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

                    <a href="{{ route('spklu.index') }}"
                       class="inline-block mt-6 px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold">
                        Cari SPKLU
                    </a>

                </div>

            @endforelse

            @if($transactions->hasPages())
                <div class="mt-6">
                    {{ $transactions->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
