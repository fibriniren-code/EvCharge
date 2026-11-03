@extends('layouts.app')

@section('content')
<div class="container py-4 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-white mb-2 text-center">Aktivasi Mesin Charger</h2>
    <p class="text-gray-400 text-sm text-center mb-6">Pilih metode scan QR Code di mesin atau masukkan ID Booking / Reservasi Anda.</p>

    <div x-data="{ tab: 'manual' }" class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl">
        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-700 mb-6">
            <button @click="tab = 'manual'" :class="{ 'border-b-2 border-blue-500 text-blue-400 font-semibold': tab === 'manual', 'text-gray-400 hover:text-white': tab !== 'manual' }" class="flex-1 py-3 text-center transition">
                Input ID Booking Manual
            </button>
            <button @click="tab = 'qr'" :class="{ 'border-b-2 border-blue-500 text-blue-400 font-semibold': tab === 'qr', 'text-gray-400 hover:text-white': tab !== 'qr' }" class="flex-1 py-3 text-center transition">
                Scan QR Code
            </button>
        </div>

        <!-- Alert Notification -->
        <div id="scan-alert" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

        <!-- Tab 1: Manual Input ID Booking -->
        <div x-show="tab === 'manual'">
            <form id="form-manual-id" onsubmit="handleActivate(event, 'manual')">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm text-gray-400 mb-2">ID Booking / Reservasi</label>
                    <input type="text" id="booking_id" name="booking_id" placeholder="Contoh: 8 atau EV-RES-008" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
                </div>
                <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg transition">
                    Verifikasi & Aktifkan Charger
                </button>
            </form>
        </div>

        <!-- Tab 2: Scan QR Code Kamera -->
        <div x-show="tab === 'qr'">
            <div class="text-center py-6 border-2 border-dashed border-gray-700 rounded-xl mb-4 bg-gray-900/50">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m0 14v1m8-8h-1M5 12H4m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <p class="text-xs text-gray-400 mb-3">Arahkan kamera smartphone Anda ke QR Code pada mesin charger</p>
                <form id="form-qr-scan" onsubmit="handleActivate(event, 'qr')">
                    @csrf
                    <input type="hidden" id="qr_code" name="qr_code" value="CHG-VOLTRON-02">
                    <button type="submit" class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-sm font-semibold rounded-lg transition">
                        Simulasi Scan QR Mesin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function handleActivate(e, type) {
    e.preventDefault();
    const alertBox = document.getElementById('scan-alert');
    alertBox.classList.add('hidden');

    const formData = new FormData(e.target);

    fetch("{{ route('driver.charging.processScan') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alertBox.className = "mb-4 p-3 rounded-lg text-sm bg-red-900/50 border border-red-500 text-red-200 block";
            alertBox.innerText = data.message;
        } else {
            alertBox.className = "mb-4 p-3 rounded-lg text-sm bg-green-900/50 border border-green-500 text-green-200 block";
            alertBox.innerText = "Verifikasi Berhasil! Memulai sesi charging...";
            
            // Auto submit ke startSession
            let startForm = document.createElement('form');
            startForm.method = 'POST';
            startForm.action = "{{ route('driver.charging.start') }}";
            startForm.innerHTML = `
                @csrf
                <input type="hidden" name="reservation_id" value="${data.reservation_id}">
                <input type="hidden" name="charging_type" value="full">
            `;
            document.body.appendChild(startForm);
            startForm.submit();
        }
    })
    .catch(err => {
        console.error(err);
        alertBox.className = "mb-4 p-3 rounded-lg text-sm bg-red-900/50 border border-red-500 text-red-200 block";
        alertBox.innerText = "Terjadi kesalahan sistem.";
    });
}
</script>
@endsection