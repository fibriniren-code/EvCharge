@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <small class="text-muted">Daftar SPKLU / Reservasi Slot</small>
        <h2 class="fw-bold text-white mt-1">Reservasi Slot Charging</h2>
        <p class="text-secondary">Tentukan target energi, jenis pengisian, waktu, dan durasi reservasi.</p>
    </div>

    {{-- Pesan Alert Error jika validasi gagal --}}
    @if ($errors->any())
        <div class="alert alert-danger bg-danger text-white border-0 rounded-3 p-3 mb-4">
            <h5 class="fw-bold mb-2">Gagal melakukan reservasi:</h5>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- TAG FORM UTAMA --}}
    <form action="{{ route('reservations.store') }}" method="POST">
        @csrf
        
        <input type="hidden" name="charger_id" value="{{ $charger->id_charger }}">

        {{-- Section Detail SPKLU --}}
        <div class="card bg-dark text-white mb-4 border-secondary p-3">
            <h5 class="text-info">{{ $location->nama_lokasi ?? 'SPKLU Voltron Yogyakarta' }}</h5>
            <p class="mb-0"><strong>Charger:</strong> #{{ $charger->id_charger }} ({{ $charger->power_kw }} kW)</p>
        </div>

        {{-- Dropdown Pilihan Kendaraan --}}
        <div class="mb-4">
            <label for="vehicle_id" class="form-label text-white fw-bold">Pilih Kendaraan Listrik <span class="text-danger">*</span></label>
            <select name="vehicle_id" id="vehicle_id" class="form-select bg-dark text-white border-secondary @error('vehicle_id') is-invalid @enderror" required>
                <option value="">-- Pilih Kendaraan Anda --</option>
                @forelse($vehicles as $vehicle)
                    <option value="{{ $vehicle->id_vehicle }}" {{ old('vehicle_id') == $vehicle->id_vehicle ? 'selected' : '' }}>
                        {{ $vehicle->brand ?? $vehicle->merk ?? $vehicle->nama_kendaraan ?? 'Kendaraan' }} 
                        ({{ $vehicle->license_plate ?? $vehicle->plat_nomor ?? 'Plat Tidak Ada' }})
                    </option>
                @empty
                    <option value="" disabled selected>⚠️ Belum ada kendaraan terdaftar. Silakan tambahkan di menu "Mobil Saya".</option>
                @endforelse
            </select>
            @error('vehicle_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Waktu Mulai --}}
        <div class="mb-4">
            <label for="start_time" class="form-label text-white fw-bold">Waktu Mulai Pengisian <span class="text-danger">*</span></label>
            <input type="datetime-local" 
                   name="start_time" 
                   id="start_time" 
                   class="form-control bg-dark text-white border-secondary @error('start_time') is-invalid @enderror" 
                   value="{{ old('start_time', now()->addMinutes(5)->format('Y-m-d\TH:i')) }}" 
                   required>
            @error('start_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Durasi Reservasi --}}
        <div class="mb-4">
            <label for="duration_hours" class="form-label text-white fw-bold">Durasi Reservasi (Jam) <span class="text-danger">*</span></label>
            <select name="duration_hours" id="duration_hours" class="form-select bg-dark text-white border-secondary @error('duration_hours') is-invalid @enderror" required>
                @for ($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ old('duration_hours', 1) == $i ? 'selected' : '' }}>
                        {{ $i }} Jam
                    </option>
                @endfor
            </select>
            @error('duration_hours')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Tombol Submit --}}
        <button type="submit" class="btn btn-primary w-100 py-3 font-weight-bold">Lanjutkan Reservasi</button>
    </form>
</div>
@endsection