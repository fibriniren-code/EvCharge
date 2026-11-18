<x-app-layout :title="'Edit ' . $station->nama_lokasi">
    <x-page-header :back="route('admin.stations.index')" backLabel="Daftar stasiun" eyebrow="Admin" :title="$station->nama_lokasi">
        <x-slot name="actions"><x-status-badge :status="$station->status" type="station" class="!px-3.5 !py-2 !text-xs" /></x-slot>
    </x-page-header>

    <div class="space-y-6">
        @include('admin.stations._form', ['station' => $station, 'action' => route('admin.stations.update', $station), 'method' => 'PUT', 'withTariff' => false])

        {{-- Charger --}}
        <section class="card reveal" style="--i:3">
            <div class="card-head"><h2 class="font-bold">Charger ({{ $station->chargers->count() }})</h2></div>
            <div class="divide-y divide-white/5">
                @forelse ($station->chargers as $c)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                        <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/5 text-slate-300"><x-icon name="plug" class="h-5 w-5" /></span><div><p class="font-bold text-white">#{{ $c->id_charger }} <span class="font-medium text-slate-500">· {{ $c->charger_type }}</span></p><p class="text-xs text-slate-500">{{ (float) $c->power_kw }} kW</p></div></div>
                        <div class="flex items-center gap-3">
                            <x-status-badge :status="$c->status" />
                            <x-confirm :action="route('admin.chargers.destroy', $c->id_charger)" method="DELETE" title="Hapus charger #{{ $c->id_charger }}?" text="Port akan dihapus permanen dari stasiun ini." confirm="Ya, hapus">
                                <x-slot name="trigger"><button type="button" class="btn-soft-danger btn-sm" aria-label="Hapus"><x-icon name="trash" class="h-3.5 w-3.5" /></button></x-slot>
                            </x-confirm>
                        </div>
                    </div>
                @empty
                    <x-empty icon="plug" title="Belum ada charger" text="Tambahkan charger agar stasiun bisa dipakai pengemudi." class="!py-10" />
                @endforelse
            </div>
            <form method="POST" action="{{ route('admin.stations.chargers.store', $station) }}" class="grid gap-3 border-t border-white/5 p-6 sm:grid-cols-[1fr_10rem_auto] sm:items-end">
                @csrf
                <div><label class="label">Jenis konektor</label><input name="charger_type" list="charger-types" required class="input" placeholder="CCS2 (Fast Charging)"><datalist id="charger-types">@foreach ($chargerTypes as $t)<option value="{{ $t }}">@endforeach</datalist></div>
                <div><label class="label">Daya (kW)</label><input type="number" step="0.1" min="1" name="power_kw" required class="input" placeholder="50"></div>
                <button type="submit" class="btn-primary"><x-icon name="plus" class="h-4 w-4" />Tambah</button>
            </form>
        </section>
    </div>
</x-app-layout>