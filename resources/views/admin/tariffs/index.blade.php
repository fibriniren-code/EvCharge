<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-white tracking-wide">Manajemen Tarif &amp; Pengaturan Biaya</h2>
        <p class="text-xs text-slate-400 mt-1">Atur tarif per kWh, biaya layanan, dan penalti keterlambatan (overstay) untuk setiap stasiun.</p>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-sm">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @forelse($tariffs as $tariff)
                <form action="{{ route('admin.tariffs.update', $tariff->id_tariff) }}" method="POST"
                      class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-6 space-y-4">
                    @csrf

                    <h3 class="text-base font-bold text-white">
                        {{ $tariff->location->nama_lokasi ?? 'Stasiun #'.$tariff->id_location }}
                    </h3>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tarif per kWh (Rp)</label>
                        <input type="number" step="0.01" min="0" name="harga_per_kwh"
                               value="{{ (float) $tariff->harga_per_kwh }}"
                               class="w-full rounded-lg bg-slate-950 border border-slate-700 text-white" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Biaya Layanan (Rp)</label>
                        <input type="number" step="0.01" min="0" name="biaya_layanan"
                               value="{{ (float) $tariff->biaya_layanan }}"
                               class="w-full rounded-lg bg-slate-950 border border-slate-700 text-white" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Penalti Keterlambatan / Overstay per Menit (Rp)</label>
                        <input type="number" step="0.01" min="0" name="overstay_fee_per_menit"
                               value="{{ (float) $tariff->overstay_fee_per_menit }}"
                               class="w-full rounded-lg bg-slate-950 border border-slate-700 text-white" required>
                    </div>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold text-sm">
                        Simpan Perubahan Tarif
                    </button>
                </form>
            @empty
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center text-slate-400 text-sm">
                    Belum ada data tarif. Jalankan seeder atau tambahkan tarif untuk stasiun terlebih dahulu.
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
