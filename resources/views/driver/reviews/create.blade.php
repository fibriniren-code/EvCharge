<x-app-layout title="Beri Ulasan">
    <x-page-header :back="route('spklu.show', $location->id_location)" backLabel="Detail stasiun" eyebrow="Ulasan" title="Bagaimana pengalaman Anda?" :subtitle="$location->nama_lokasi" />

    <form method="POST" action="{{ route('reviews.store', $location->id_location) }}" enctype="multipart/form-data" class="mx-auto max-w-2xl space-y-5">
        @csrf
        <input type="hidden" name="id_session" value="{{ old('id_session', $session->id_session) }}">
        <x-input-error :messages="$errors->get('id_session')" />

        @foreach ([['alat', 'Kondisi alat charger', 'plug', 'Kecepatan, kestabilan, dan kebersihan alat.'], ['tempat', 'Tempat & fasilitas', 'building', 'Kenyamanan, keamanan, dan fasilitas pendukung.']] as $i => [$key, $title, $ic, $hint])
            <section class="card reveal p-6" style="--i: {{ $i + 1 }}" x-data="{ rating: {{ (int) old('rating_' . $key, 0) }}, hover: 0 }">
                <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/10 text-emerald-300"><x-icon :name="$ic" class="h-5 w-5" /></span><div><h2 class="font-bold">{{ $title }}</h2><p class="text-xs text-slate-500">{{ $hint }}</p></div></div>
                <input type="hidden" name="rating_{{ $key }}" :value="rating">
                <div class="mt-5 flex items-center gap-1.5" @mouseleave="hover = 0">
                    @foreach ([1, 2, 3, 4, 5] as $n)
                        <button type="button" @click="rating = {{ $n }}" @mouseenter="hover = {{ $n }}" class="transition duration-150 hover:scale-125 active:scale-90" aria-label="{{ $n }} bintang">
                            <svg class="h-9 w-9 transition-colors" :class="(hover || rating) >= {{ $n }} ? 'text-amber-400' : 'text-slate-700'" viewBox="0 0 24 24" fill="currentColor"><path d="m12 3 2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.8l6.5-.9L12 3z"/></svg>
                        </button>
                    @endforeach
                    <span class="ml-3 text-sm font-bold text-amber-300" x-text="['', 'Buruk', 'Kurang', 'Cukup', 'Bagus', 'Luar biasa'][hover || rating]"></span>
                </div>
                <x-input-error :messages="$errors->get('rating_' . $key)" class="mt-2" />
                <textarea name="ulasan_{{ $key }}" rows="3" maxlength="1000" class="input mt-4" placeholder="Ceritakan pengalaman Anda (opsional)">{{ old('ulasan_' . $key) }}</textarea>
            </section>
        @endforeach

        <section class="card reveal p-6" style="--i:3" x-data="{ preview: null }">
            <h2 class="font-bold">Foto lokasi <span class="text-xs font-medium text-slate-500">(opsional)</span></h2>
            <label class="mt-4 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-white/10 p-8 text-center transition hover:border-emerald-400/40 hover:bg-white/[0.03]">
                <template x-if="!preview"><span class="flex flex-col items-center gap-2 text-slate-400"><x-icon name="image" class="h-8 w-8" /><span class="text-sm font-semibold">Klik untuk unggah foto</span><span class="text-xs text-slate-600">JPG, PNG, WEBP · maks 2 MB</span></span></template>
                <img x-show="preview" :src="preview" class="max-h-52 rounded-xl object-cover" alt="Pratinjau">
                <input type="file" name="foto_lokasi" accept="image/*" class="sr-only" @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
            </label>
            <x-input-error :messages="$errors->get('foto_lokasi')" class="mt-2" />
        </section>

        <div class="flex justify-end gap-3"><a href="{{ route('spklu.show', $location->id_location) }}" class="btn-secondary">Nanti saja</a><button type="submit" class="btn-primary btn-lg">Kirim ulasan</button></div>
    </form>
</x-app-layout>