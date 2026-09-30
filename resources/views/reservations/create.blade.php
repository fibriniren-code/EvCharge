<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reservasi Slot Charger') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <!-- Detail Charger & Lokasi -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">{{ $charger->location->name ?? 'Stasiun SPKLU' }}</h3>
                    <p class="text-sm text-gray-600 mb-2">{{ $charger->location->address ?? '-' }}</p>
                    
                    <div class="flex items-center gap-4 text-sm mt-3">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-medium">
                            Slot / Charger: {{ $charger->name ?? 'Charger #'.$charger->id }}
                        </span>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium">
                            Tipe: {{ $charger->type ?? 'Fast Charging' }}
                        </span>
                        <span class="text-gray-700 font-semibold">
                            Tarif: Rp {{ number_format($charger->tariff->price_per_hour ?? 0, 0, ',', '.') }}/jam
                        </span>
                    </div>
                </div>

                <!-- Form Reservasi -->
                <form action="{{ route('reservations.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="charger_id" value="{{ $charger->id }}">

                    <!-- Pilih Kendaraan -->
                    <div>
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kendaraan Listrik</label>
                        <select name="vehicle_id" id="vehicle_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">-- Pilih Kendaraan Anda --</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->license_plate }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Waktu Mulai -->
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Waktu Waktu Masuk (Mulai)</label>
                            <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('start_time')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Durasi Pengisian -->
                        <div>
                            <label for="duration_hours" class="block text-sm font-medium text-gray-700 mb-1">Durasi Pengisian (Jam)</label>
                            <select name="duration_hours" id="duration_hours" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="1" {{ old('duration_hours') == 1 ? 'selected' : '' }}>1 Jam</option>
                                <option value="2" {{ old('duration_hours') == 2 ? 'selected' : '' }}>2 Jam</option>
                                <option value="3" {{ old('duration_hours') == 3 ? 'selected' : '' }}>3 Jam</option>
                                <option value="4" {{ old('duration_hours') == 4 ? 'selected' : '' }}>4 Jam</option>
                            </select>
                            @error('duration_hours')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('spklu.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800 text-sm font-medium shadow-sm">
                            Konfirmasi Reservasi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>