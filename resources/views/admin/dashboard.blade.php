<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Operator Real-Time
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Peringatan Otomatis (FR-09) -->
            @if(count($faultedChargers) > 0)
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p class="font-bold">⚠️ Peringatan Gangguan Teknis (Alarm Alert)</p>
                    <p>Terdapat {{ count($faultedChargers) }} unit charger mengalami error/kerusakan teknis!</p>
                </div>
            @endif

            <!-- Cards Indikator Status Port Real-Time (FR-08) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-green-100 p-6 rounded-lg shadow border-l-4 border-green-500">
                    <h3 class="text-sm font-medium text-green-800">Available</h3>
                    <p class="text-3xl font-bold text-green-900">{{ $statusCounts['available'] }} Port</p>
                </div>
                <div class="bg-blue-100 p-6 rounded-lg shadow border-l-4 border-blue-500">
                    <h3 class="text-sm font-medium text-blue-800">Occupied</h3>
                    <p class="text-3xl font-bold text-blue-900">{{ $statusCounts['occupied'] }} Port</p>
                </div>
                <div class="bg-red-100 p-6 rounded-lg shadow border-l-4 border-red-500">
                    <h3 class="text-sm font-medium text-red-800">Faulted</h3>
                    <p class="text-3xl font-bold text-red-900">{{ $statusCounts['faulted'] }} Port</p>
                </div>
                <div class="bg-gray-100 p-6 rounded-lg shadow border-l-4 border-gray-500">
                    <h3 class="text-sm font-medium text-gray-800">Unavailable</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ $statusCounts['unavailable'] }} Port</p>
                </div>
            </div>

            <!-- Tabel Sesi Pengisian Daya Aktif -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold mb-4">Sesi Pengisian Daya Berlangsung</h3>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="p-2">Pengemudi</th>
                            <th class="p-2">Charger ID</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Waktu Mulai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeSessions as $session)
                            <tr class="border-b">
                                <td class="p-2">{{ $session->user->name ?? 'Guest' }}</td>
                                <td class="p-2">{{ $session->charger->name ?? $session->charger_id }}</td>
                                <td class="p-2"><span class="bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs">Aktif</span></td>
                                <td class="p-2">{{ $session->created_at->format('H:i:s - d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">Tidak ada sesi pengisian aktif saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>