<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            Riwayat & Daftar Reservasi SPKLU
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-4 p-4 bg-emerald-600/20 border border-emerald-500 text-emerald-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-slate-900 border border-slate-800 shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-200">Daftar Reservasi Anda</h3>
                    <a href="{{ route('spklu.index') }}" class="btn btn-primary btn-sm">
                        + Cari SPKLU & Buat Reservasi
                    </a>
                </div>

                @if ($reservations->isEmpty())
                    <div class="text-center py-12 text-slate-400">
                        <p class="text-base">Belum ada riwayat reservasi.</p>
                        <a href="{{ route('spklu.index') }}" class="text-blue-400 hover:underline mt-2 inline-block">
                            Temukan lokasi SPKLU terdekat
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-slate-400 border-b border-slate-800">
                                    <th># ID</th>
                                    <th>Lokasi SPKLU</th>
                                    <th>Charger / Daya</th>
                                    <th>Kendaraan</th>
                                    <th>Waktu Mulai</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservations as $reservation)
                                    <tr class="border-b border-slate-800/50">
                                        <td class="font-mono text-sm text-slate-400">
                                            #{{ $reservation->id_reservation ?? $reservation->id }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-slate-200">
                                                {{ $reservation->charger->location->name ?? $reservation->charger->location->location_name ?? 'Lokasi SPKLU' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-slate-800 text-slate-300 border border-slate-700">
                                                {{ $reservation->charger->type ?? $reservation->charger->charger_type ?? 'AC/DC' }} 
                                                ({{ $reservation->charger->power_kw ?? $reservation->charger->power ?? '-' }} kW)
                                            </span>
                                        </td>
                                        <td class="text-slate-300">
                                            {{ $reservation->vehicle->brand ?? '' }} {{ $reservation->vehicle->model ?? '-' }}
                                        </td>
                                        <td class="text-slate-300 text-sm">
                                            {{ \Carbon\Carbon::parse($reservation->waktu_mulai ?? $reservation->start_time)->format('d M Y, H:i') }}
                                        </td>
                                        <td>
                                            @switch($reservation->status)
                                                @case('pending')
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge bg-success">Dikonfirmasi</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-info text-dark">Selesai</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Dibatalkan</span>
                                                    @break
                                                @case('expired')
                                                    <span class="badge bg-secondary">Expired</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($reservation->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('reservations.show', $reservation->id_reservation ?? $reservation->id) }}" class="btn btn-outline-info btn-sm">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>