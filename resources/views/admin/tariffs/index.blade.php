<x-app-layout title="Tarif & Biaya">
    <x-page-header eyebrow="Admin" title="Tarif & biaya" subtitle="Tarif per stasiun dipakai langsung pada perhitungan biaya sesi charging berikutnya." />

    <div class="grid gap-5 lg:grid-cols-2">
        @forelse ($stations as $st)
            @php $t = $st->activeTariff; @endphp
            <form method="POST" action="{{ route('admin.tariffs.update', $st->id_location) }}" class="card card-hover reveal p-6" style="--i: {{ min($loop->index, 8) }}"
                  x-data="{ rate: {{ (float) ($t->harga_per_kwh ?? 2466) }}, svc: {{ (float) ($t->biaya_layanan ?? 0) }}, min: {{ (float) ($t->biaya_minimum ?? 0) }}, over: {{ (float) ($t->overstay_fee_per_menit ?? 0) }}, o: { rate: {{ (float) ($t->harga_per_kwh ?? 2466) }}, svc: {{ (float) ($t->biaya_layanan ?? 0) }}, min: {{ (float) ($t->biaya_minimum ?? 0) }}, over: {{ (float) ($t->overstay_fee_per_menit ?? 0) }} },
                           get dirty() { return this.rate != this.o.rate || this.svc != this.o.svc || this.min != this.o.min || this.over != this.o.over; },
                           get sample() { return Math.max(20 * this.rate + Number(this.svc), Number(this.min)); } }">
                @csrf
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0"><h2 class="truncate font-bold text-white">{{ $st->nama_lokasi }}</h2><p class="truncate text-xs text-slate-500">{{ $st->alamat }}</p></div>
                    @if (! $t)<span class="badge-amber">Belum ada tarif</span>@else<x-status-badge :status="$st->status" type="station" />@endif
                </div>
                <div class="mt-5 grid grid-cols-2 gap-4">
                    <div><label class="label">Harga / kWh</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">Rp</span><input type="number" min="0" name="harga_per_kwh" x-model="rate" required class="input pl-10"></div></div>
                    <div><label class="label">Biaya layanan</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">Rp</span><input type="number" min="0" name="biaya_layanan" x-model="svc" required class="input pl-10"></div></div>
                    <div><label class="label">Biaya minimum</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">Rp</span><input type="number" min="0" name="biaya_minimum" x-model="min" required class="input pl-10"></div></div>
                    <div><label class="label">Overstay / menit</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">Rp</span><input type="number" min="0" name="overstay_fee_per_menit" x-model="over" required class="input pl-10"></div></div>
                </div>
                <div class="mt-5 flex items-center justify-between gap-3 rounded-xl bg-white/[0.04] p-4">
                    <p class="text-xs text-slate-400">Contoh pengisian <b class="text-white">20 kWh</b></p>
                    <p class="text-lg font-extrabold text-amber-300" x-text="rupiah(sample)"></p>
                </div>
                <div class="mt-5 flex items-center justify-between gap-3">
                    <p class="text-xs" :class="dirty ? 'text-amber-300' : 'text-slate-600'" x-text="dirty ? 'Ada perubahan belum disimpan' : 'Tidak ada perubahan'"></p>
                    <button type="submit" class="btn-primary btn-sm" :disabled="!dirty">Simpan tarif</button>
                </div>
            </form>
        @empty
            <div class="card lg:col-span-2"><x-empty icon="tag" title="Belum ada stasiun" text="Tambahkan stasiun terlebih dahulu." /></div>
        @endforelse
    </div>
</x-app-layout>