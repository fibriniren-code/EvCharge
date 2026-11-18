<x-app-layout>
    <div class="py-6 bg-slate-900 min-h-screen text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6 shadow-xl">
                <div class="mb-6">
                    <h1 class="text-xl font-extrabold text-white">Tambah Stasiun SPKLU Baru</h1>
                    <p class="text-sm text-slate-400">Masukkan detail stasiun pengisian kendaraan listrik baru.</p>
                </div>

                <form action="{{ route('operator.stations.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nama Stasiun SPKLU</label>
                        <input type="text" name="name" required placeholder="Contoh: SPKLU Rest Area KM 19" 
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Alamat Lengkap</label>
                        <textarea name="address" required rows="3" placeholder="Masukkan alamat stasiun..." 
                                  class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kota</label>
                            <input type="text" name="city" required placeholder="Contoh: Jakarta Selatan" 
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Jumlah Slot Charger</label>
                            <input type="number" name="total_slots" value="4" min="1" required 
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Latitude</label>
                            <input type="text" name="latitude" required placeholder="-6.200000" 
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Longitude</label>
                            <input type="text" name="longitude" required placeholder="106.816666" 
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('operator.dashboard') }}" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-semibold transition">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 rounded-xl text-sm font-bold transition">Simpan Stasiun</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>