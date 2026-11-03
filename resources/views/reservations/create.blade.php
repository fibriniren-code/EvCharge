@extends('layouts.app')

@section('content')
<div class="container py-4 max-w-3xl mx-auto">
    <div class="mb-4">
        <small class="text-gray-400">Daftar SPKLU / Reservasi Slot</small>
        <h2 class="text-2xl font-bold text-white mt-1">Reservasi Slot Charging</h2>
        <p class="text-gray-400 text-sm">Tentukan kendaraan, waktu mulai, dan durasi reservasi Anda.</p>
    </div>

    {{-- Pesan Alert Error jika validasi gagal --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-900/50 border border-red-500/50 rounded-xl text-red-200">
            <h5 class="font-bold mb-1">Gagal melakukan reservasi:</h5>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UTAMA RESERVASI --}}
    <form action="{{ route('reservations.store') }}" method="POST">
        @csrf
        
        <input type="hidden" name="charger_id" value="{{ $charger->id_charger }}">

        {{-- Section Detail SPKLU --}}
        <div class="bg-gray-800 rounded-2xl p-4 border border-gray-700 text-white mb-6">
            <h5 class="text-blue-400 font-bold text-lg mb-1">{{ $location->name ?? $location->nama_lokasi ?? 'SPKLU Location' }}</h5>
            <p class="text-xs text-gray-300">
                <strong>Mesin Charger:</strong> #{{ $charger->id_charger }} 
                <span class="ml-2"><strong>Daya Maksimal:</strong> {{ $charger->power_kw ?? 22 }} kW</span>
            </p>
        </div>

        {{-- Dropdown Pilihan Kendaraan --}}
        <div class="mb-5">
            <label for="vehicle_id" class="block text-sm font-semibold text-white mb-2">Pilih Kendaraan Listrik <span class="text-red-500">*</span></label>
            <select name="vehicle_id" id="vehicle_id" class="w-full bg-gray-900 text-white border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500 @error('vehicle_id') border-red-500 @enderror" required>
                <option value="">-- Pilih Kendaraan Anda --</option>
                @forelse($vehicles as $vehicle)
                    <option value="{{ $vehicle->id_vehicle }}" {{ old('vehicle_id') == $vehicle->id_vehicle ? 'selected' : '' }}>
                        {{ $vehicle->brand ?? $vehicle->merk ?? 'EV Vehicle' }} 
                        ({{ $vehicle->license_plate ?? $vehicle->plat_nomor ?? 'No License' }})
                    </option>
                @empty
                    <option value="" disabled selected>⚠️ Belum ada kendaraan terdaftar. Silakan tambahkan di menu "Mobil Saya".</option>
                @endforelse
            </select>
            @error('vehicle_id')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Grid Waktu Mulai & Durasi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            {{-- Waktu Mulai --}}
            <div>
                <label for="start_time" class="block text-sm font-semibold text-white mb-2">Waktu Mulai Pengisian <span class="text-red-500">*</span></label>
                <input type="datetime-local" 
                       name="start_time" 
                       id="start_time" 
                       class="w-full bg-gray-900 text-white border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500 @error('start_time') border-red-500 @enderror" 
                       value="{{ old('start_time', now()->addMinutes(2)->format('Y-m-d\TH:i')) }}" 
                       required>
                @error('start_time')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Durasi Reservasi --}}
            <div>
                <label for="duration_hours" class="block text-sm font-semibold text-white mb-2">Durasi Reservasi (Jam) <span class="text-red-500">*</span></label>
                <select name="duration_hours" id="duration_hours" class="w-full bg-gray-900 text-white border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500 @error('duration_hours') border-red-500 @enderror" required>
                    @for ($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ old('duration_hours', 1) == $i ? 'selected' : '' }}>
                            {{ $i }} Jam
                        </option>
                    @endfor
                </select>
                @error('duration_hours')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Alert Info Batas Waktu Holding Slot --}}
        <div class="p-3.5 bg-amber-900/30 border border-amber-500/30 rounded-xl text-amber-300 text-xs mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
            <span>Slot charger ditahan selama <strong>10 menit</strong> setelah reservasi dibuat. Silakan lakukan validasi di lokasi sebelum batas waktu habis.</span>
        </div>

        {{-- Tombol Submit --}}
        <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg transition duration-200">
            Lanjutkan Reservasi →
        </button>
    </form>
</div>
@endsection