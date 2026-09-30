<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar SPKLU & Stasiun Pengisian') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Form Filter & Pencarian -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('spklu.index') }}" class="space-y-4">
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">CARI SPKLU / KOTA</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Ketik nama kota (misal: Jakarta, Bandung, Surabaya...)" 
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 py-2.5 px-3"
                           required>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-4 pt-1">
                    <button type="submit" class="w-1/2 bg-slate-900 hover:bg-slate-800 text-white py-2.5 px-4 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari di Peta
                    </button>
                    <a href="{{ route('spklu.index') }}" class="w-1/2 text-center bg-gray-50 hover:bg-gray-100 text-gray-700 py-2.5 px-4 rounded-lg text-sm font-semibold transition border border-gray-200">
                        Reset
                    </a>
                </div>

            </form>
        </div>

        @if(request()->filled('search'))
            <!-- Section Google Maps Embed (Hanya muncul jika sudah mencari) -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-3 px-1">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Peta SPKLU Terdekat: {{ request('search') }}
                    </h3>
                    
                    @php
                        $searchQuery = "SPKLU " . request('search');
                        $googleMapsUrl = "https://www.google.com/maps/search/" . urlencode($searchQuery);
                    @endphp
                    
                    <a href="{{ $googleMapsUrl }}" target="_blank" class="text-xs text-slate-700 font-semibold hover:underline flex items-center gap-1">
                        Buka Aplikasi Google Maps 
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>

                <div class="w-full h-80 rounded-lg overflow-hidden border border-gray-200">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        loading="lazy" 
                        allowfullscreen
                        src="https://maps.google.com/maps?q={{ urlencode($searchQuery) }}&t=&z=12&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
            </div>

            <!-- Daftar Stasiun SPKLU (Grid) -->
            <div class="space-y-4">
                @forelse ($locations as $location)
                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base">
                                    {{ $location->nama_lokasi }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    {{ $location->alamat }}
                                </p>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Slot Charger -->
                        <div>
                            <h4 class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Slot Charger Tersedia:</h4>
                            <div class="space-y-2">
                                @forelse ($location->chargers as $charger)
                                    <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg text-xs">
                                        <div>
                                            <p class="font-bold text-gray-800">{{ $charger->type }}</p>
                                            <p class="text-gray-500 text-[11px]">{{ $charger->power_kw }} kW</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($charger->status === 'available')
                                                <span class="font-semibold text-green-600 text-xs mr-1">Tersedia</span>
                                                <a href="{{ url('/location/' . $location->id_location . '/charger/' . ($charger->id_charger ?? $charger->id) . '/reserve') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold transition">
                                                    Pesan
                                                </a>
                                            @else
                                                <span class="font-semibold text-gray-500 text-xs">{{ ucfirst($charger->status) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 italic">Belum ada unit charger terpasang.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-8 text-center rounded-xl border border-gray-100 text-gray-500">
                        <p class="text-sm font-semibold">Tidak ditemukan stasiun SPKLU untuk kata kunci "{{ request('search') }}"</p>
                    </div>
                @endforelse
            </div>

            @if(method_exists($locations, 'links'))
                <div class="mt-4">
                    {{ $locations->links() }}
                </div>
            @endif
        @else
            <!-- Tampilan awal sebelum pengguna mengetik pencarian -->
            <div class="bg-white p-12 text-center rounded-xl border border-gray-100 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <p class="text-base font-semibold text-gray-700">Silakan Masukkan Nama Kota</p>
                <p class="text-xs text-gray-400 mt-1">Ketik kota tujuan Anda pada kolom di atas lalu klik "Cari di Peta" untuk menampilkan lokasi SPKLU.</p>
            </div>
        @endif

    </div>
</x-app-layout>