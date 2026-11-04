
<x-app-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full">

            <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden">

                <!-- HEADER -->
                <div class="bg-gradient-to-br from-blue-900/60 to-slate-900 px-6 pt-10 pb-8 text-center">

                    <div class="w-24 h-24 rounded-full bg-green-500/10 border-2 border-green-500/30 flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-14 h-14 text-green-400"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <h1 class="text-2xl font-bold text-white mb-3">
                        Review Berhasil Disimpan!
                    </h1>

                    <p class="text-slate-400 text-sm leading-relaxed">
                        Terima kasih sudah meluangkan waktu untuk memberikan ulasan.
                        Review kamu berhasil tersimpan di EVChargeHub.
                    </p>

                </div>

                <!-- CONTENT -->
                <div class="px-6 py-7">

                    <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-5 mb-6">

                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-2xl">⭐</span>
                            <h2 class="text-white font-semibold">
                                Ulasan Kamu Berarti!
                            </h2>
                        </div>

                        <p class="text-slate-400 text-sm leading-relaxed">
                            Masukan kamu membantu pengguna lain mendapatkan informasi
                            mengenai kondisi charger dan fasilitas lokasi SPKLU.
                            Semoga pengalaman charging kamu selalu menyenangkan!
                        </p>

                    </div>

                    <a href="{{ route('spklu.index') }}"
                       class="block w-full text-center py-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition shadow-lg shadow-blue-900/20">
                        Kembali ke Halaman Utama SPKLU
                    </a>

                    <p class="text-center text-slate-500 text-xs mt-4">
                        Terima kasih telah menggunakan EVChargeHub.
                    </p>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
