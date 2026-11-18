<x-app-layout title="Tambah Mobil">
    <x-page-header :back="route('cars.index')" backLabel="Mobil saya" eyebrow="Garasi" title="Tambah mobil listrik" subtitle="Pilih jenis konektor dengan benar agar charger yang cocok bisa dipesan." />

    <form method="POST" action="{{ route('vehicles.store') }}" class="card reveal mx-auto max-w-2xl space-y-6 p-6 sm:p-8" style="--i:1">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div><x-input-label for="merek" value="Merek" /><x-text-input id="merek" name="merek" :value="old('merek')" required autofocus placeholder="Hyundai" /><x-input-error :messages="$errors->get('merek')" /></div>
            <div><x-input-label for="model" value="Model" /><x-text-input id="model" name="model" :value="old('model')" required placeholder="Ioniq 5" /><x-input-error :messages="$errors->get('model')" /></div>
            <div><x-input-label for="nomor_polisi" value="Plat nomor" /><x-text-input id="nomor_polisi" name="nomor_polisi" :value="old('nomor_polisi')" required placeholder="AB 1234 CD" class="uppercase" /><x-input-error :messages="$errors->get('nomor_polisi')" /></div>
            <div><x-input-label for="kapasitas_baterai_kwh" value="Kapasitas baterai (kWh)" /><x-text-input id="kapasitas_baterai_kwh" type="number" step="0.1" name="kapasitas_baterai_kwh" :value="old('kapasitas_baterai_kwh')" required placeholder="72.6" /><x-input-error :messages="$errors->get('kapasitas_baterai_kwh')" /></div>
        </div>

        <div>
            <x-input-label value="Tipe konektor" />
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($connectors as $c)
                    <label class="choice"><input type="radio" name="tipe_konektor" value="{{ $c }}" class="sr-only" @checked(old('tipe_konektor', $connectors[0]) === $c)><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/5 text-emerald-300"><x-icon name="plug" class="h-5 w-5" /></span><span class="text-sm font-bold text-white">{{ $c }}</span></label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('tipe_konektor')" class="mt-2" />
        </div>

        <div class="flex justify-end gap-3 border-t border-white/5 pt-6"><a href="{{ route('cars.index') }}" class="btn-secondary">Batal</a><button type="submit" class="btn-primary">Simpan mobil</button></div>
    </form>
</x-app-layout>