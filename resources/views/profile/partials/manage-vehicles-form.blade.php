<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Kelola Kendaraan Listrik') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Tambahkan data kendaraan listrik Anda untuk mempermudah transaksi pengisian daya.') }}
        </p>
    </header>

    <form method="post" action="{{ route('vehicles.store') }}" class="mt-6 space-y-6">
        @csrf

        <!-- Merek Kendaraan -->
        <div>
            <x-input-label for="merek" :value="__('Merek Kendaraan')" />
            <x-text-input id="merek" name="merek" type="text" class="mt-1 block w-full" :value="old('merek')" placeholder="Contoh: Tesla / Hyundai" required />
            <x-input-error class="mt-2" :messages="$errors->get('merek')" />
        </div>

        <!-- Model Kendaraan -->
        <div>
            <x-input-label for="model" :value="__('Model Kendaraan')" />
            <x-text-input id="model" name="model" type="text" class="mt-1 block w-full" :value="old('model')" placeholder="Contoh: Ioniq 5 / Model 3" required />
            <x-input-error class="mt-2" :messages="$errors->get('model')" />
        </div>

        <!-- Nomor Polisi -->
        <div>
            <x-input-label for="nomor_polisi" :value="__('Nomor Polisi / Plat Nomor')" />
            <x-text-input id="nomor_polisi" name="nomor_polisi" type="text" class="mt-1 block w-full" :value="old('nomor_polisi')" placeholder="Contoh: AB 8888 NW" required />
            <x-input-error class="mt-2" :messages="$errors->get('nomor_polisi')" />
        </div>

        <!-- Kapasitas Baterai -->
        <div>
            <x-input-label for="kapasitas_baterai_kwh" :value="__('Kapasitas Baterai (kWh)')" />
            <x-text-input id="kapasitas_baterai_kwh" name="kapasitas_baterai_kwh" type="number" step="0.1" class="mt-1 block w-full" :value="old('kapasitas_baterai_kwh')" placeholder="Contoh: 72.6" required />
            <x-input-error class="mt-2" :messages="$errors->get('kapasitas_baterai_kwh')" />
        </div>

        <!-- Tipe Konektor -->
        <div>
            <x-input-label for="tipe_konektor" :value="__('Tipe Konektor')" />
            <select id="tipe_konektor" name="tipe_konektor" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">-- Pilih Konektor --</option>
                <option value="CCS2" {{ old('tipe_konektor') == 'CCS2' ? 'selected' : '' }}>CCS2</option>
                <option value="CHAdeMO" {{ old('tipe_konektor') == 'CHAdeMO' ? 'selected' : '' }}>CHAdeMO</option>
                <option value="AC Type 2" {{ old('tipe_konektor') == 'AC Type 2' ? 'selected' : '' }}>AC Type 2</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('tipe_konektor')" />
        </div>

        <!-- Tombol Simpan -->
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Kendaraan') }}</x-primary-button>

            @if (session('status') === 'vehicle-added')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600"
                >{{ __('Kendaraan berhasil disimpan.') }}</p>
            @endif
        </div>
    </form>
</section>