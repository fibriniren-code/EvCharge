
<x-app-layout>
    <div class="min-h-screen py-10 px-4">
        <div class="max-w-2xl mx-auto">

            <a href="{{ route('spklu.show', $location->id_location) }}"
               class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm mb-6">
                ← Kembali ke Detail SPKLU
            </a>

            <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">

                <div class="bg-gradient-to-br from-blue-900/60 to-slate-900 p-7 text-center border-b border-slate-800">
                    <div class="w-16 h-16 rounded-2xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center mx-auto mb-4 text-3xl">
                        ⭐
                    </div>

                    <h1 class="text-2xl font-bold text-white">
                        Berikan Review SPKLU
                    </h1>

                    <p class="text-slate-400 text-sm mt-2">
                        Bagikan pengalaman charging kamu untuk membantu pengguna lainnya.
                    </p>
                </div>

                <div class="p-6">

                    <div class="bg-slate-800/70 rounded-2xl p-4 mb-6">
                        <p class="text-xs text-slate-400 mb-1">Lokasi SPKLU</p>

                        <h2 class="text-white font-bold text-lg">
                            {{ $location->nama_lokasi }}
                        </h2>

                        <p class="text-slate-400 text-sm mt-1">
                            {{ $location->alamat }}
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-500/10 border border-red-500/30 text-red-300 rounded-xl p-4 mb-6">
                            <p class="font-semibold mb-2">Periksa kembali isian kamu:</p>

                            <ul class="list-disc pl-5 text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('reviews.store', $location->id_location) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="space-y-7">

                        @csrf

                        <!-- RATING ALAT -->
                        <div>
                            <h3 class="text-white font-bold text-lg mb-1">
                                1. Penilaian Alat Charger
                            </h3>

                            <p class="text-slate-400 text-sm mb-4">
                                Bagaimana kondisi dan kinerja alat charger yang kamu gunakan?
                            </p>

                            <label class="block text-sm text-slate-300 mb-2">
                                Rating alat charger
                            </label>

                            <div class="star-rating flex gap-2"
                                 data-rating-name="rating_alat">

                                @for ($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio"
                                               name="rating_alat"
                                               value="{{ $i }}"
                                               class="sr-only"
                                               {{ old('rating_alat') == $i ? 'checked' : '' }}
                                               required>

                                        <span class="star text-4xl text-slate-600 transition-all duration-200">
                                            ★
                                        </span>
                                    </label>
                                @endfor

                            </div>

                            <p class="text-xs text-slate-500 mt-2">
                                Pilih 1 sampai 5 bintang.
                            </p>

                            <label class="block text-sm text-slate-300 mt-5 mb-2">
                                Ulasan alat charger
                            </label>

                            <textarea name="ulasan_alat"
                                      rows="3"
                                      maxlength="1000"
                                      placeholder="Contoh: Charger berfungsi dengan baik dan proses pengisian berjalan lancar..."
                                      class="w-full rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500">{{ old('ulasan_alat') }}</textarea>
                        </div>

                        <div class="border-t border-slate-800"></div>

                        <!-- RATING LOKASI -->
                        <div>
                            <h3 class="text-white font-bold text-lg mb-1">
                                2. Penilaian Lokasi SPKLU
                            </h3>

                            <p class="text-slate-400 text-sm mb-4">
                                Bagaimana pengalaman kamu dengan lokasi dan fasilitas SPKLU ini?
                            </p>

                            <label class="block text-sm text-slate-300 mb-2">
                                Rating lokasi
                            </label>

                            <div class="star-rating flex gap-2"
                                 data-rating-name="rating_tempat">

                                @for ($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio"
                                               name="rating_tempat"
                                               value="{{ $i }}"
                                               class="sr-only"
                                               {{ old('rating_tempat') == $i ? 'checked' : '' }}
                                               required>

                                        <span class="star text-4xl text-slate-600 transition-all duration-200">
                                            ★
                                        </span>
                                    </label>
                                @endfor

                            </div>

                            <p class="text-xs text-slate-500 mt-2">
                                Pilih 1 sampai 5 bintang.
                            </p>

                            <label class="block text-sm text-slate-300 mt-5 mb-2">
                                Ulasan lokasi
                            </label>

                            <textarea name="ulasan_tempat"
                                      rows="3"
                                      maxlength="1000"
                                      placeholder="Contoh: Lokasi mudah ditemukan, tempat bersih, dan fasilitas cukup memadai..."
                                      class="w-full rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500">{{ old('ulasan_tempat') }}</textarea>
                        </div>

                        <div class="border-t border-slate-800"></div>

                        <!-- FOTO LOKASI -->
                        <div>
                            <h3 class="text-white font-bold text-lg mb-2">
                                3. Foto Lokasi (Opsional)
                            </h3>

                            <p class="text-slate-400 text-sm mb-3">
                                Kamu bisa menambahkan foto kondisi lokasi SPKLU.
                            </p>

                            <input type="file"
                                   name="foto_lokasi"
                                   accept="image/png,image/jpeg,image/webp"
                                   class="block w-full text-sm text-slate-400
                                   file:mr-4 file:py-2 file:px-4
                                   file:rounded-lg file:border-0
                                   file:bg-blue-600 file:text-white
                                   hover:file:bg-blue-500">

                            <p class="text-xs text-slate-500 mt-2">
                                Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                            </p>
                        </div>

                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
                            <p class="text-blue-300 text-sm">
                                Terima kasih sudah membantu meningkatkan kualitas layanan EVChargeHub melalui ulasan kamu!
                            </p>
                        </div>

                        <button type="submit"
                                class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition shadow-lg shadow-blue-900/20">
                            Kirim Review
                        </button>

                        <a href="{{ route('spklu.show', $location->id_location) }}"
                           class="block text-center text-slate-400 hover:text-white text-sm">
                            Nanti saja
                        </a>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT BINTANG -->
    <script>
        document.querySelectorAll('.star-rating').forEach(function (ratingGroup) {

            const stars = ratingGroup.querySelectorAll('.star');
            const radios = ratingGroup.querySelectorAll('input[type="radio"]');

            function updateStars(selectedValue) {
                stars.forEach(function (star, index) {
                    if (index < selectedValue) {
                        star.classList.remove('text-slate-600');
                        star.classList.add('text-yellow-400');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-slate-600');
                    }
                });
            }

            radios.forEach(function (radio, index) {

                radio.addEventListener('change', function () {
                    updateStars(parseInt(this.value));
                });

                stars[index].addEventListener('mouseenter', function () {
                    updateStars(index + 1);
                });

                stars[index].parentElement.addEventListener('mouseleave', function () {
                    const selected = ratingGroup.querySelector('input:checked');

                    updateStars(selected ? parseInt(selected.value) : 0);
                });

            });

            // Menampilkan pilihan lama jika validasi gagal
            const selected = ratingGroup.querySelector('input:checked');

            if (selected) {
                updateStars(parseInt(selected.value));
            }
        });
    </script>
</x-app-layout>
