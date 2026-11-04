<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manajemen Tarif & Pengaturan Biaya
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow">
                
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                        {{ session('success') }}
                    </div>
                @endif

                @foreach($tariffs as $tariff)
                    <form action="{{ route('admin.tariffs.update', $tariff->id) }}" method="POST" class="space-y-4 mb-6">
                        @csrf
                        
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Tarif per kWh (Rp)</label>
                            <input type="number" name="price_per_kwh" value="{{ $tariff->price_per_kwh }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700">Biaya Layanan (Rp)</label>
                            <input type="number" name="service_fee" value="{{ $tariff->service_fee }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700">Biaya Penalti Keterlambatan / Overstay Fee per Menit (Rp)</label>
                            <input type="number" name="overstay_fee_per_min" value="{{ $tariff->overstay_fee_per_min }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>

                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md font-bold">
                            Simpan Perubahan Tarif
                        </button>
                    </form>
                @endforeach

            </div>
        </div>
    </div>
</x-app-layout>