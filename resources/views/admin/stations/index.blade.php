<x-app-layout title="Stasiun & Charger">
    <x-page-header eyebrow="Admin" title="Stasiun & charger" subtitle="Kelola lokasi SPKLU, port charger, dan status operasional.">
        <x-slot name="actions"><a href="{{ route('admin.stations.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" />Tambah stasiun</a></x-slot>
    </x-page-header>

    <form method="GET" class="reveal mb-5" style="--i:1">
        <div class="relative"><x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" /><input name="search" value="{{ $search }}" placeholder="Cari nama atau alamat stasiun…" class="input input-icon"></div>
    </form>

    <section class="card reveal overflow-hidden" style="--i:2">
        @if ($stations->count())
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Stasiun</th><th>Charger</th><th>Tarif</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($stations as $st)
                            @php $t = $st->tariffs->where('periode_berlaku', '<=', now())->sortByDesc('periode_berlaku')->first(); @endphp
                            <tr>
                                <td class="max-w-xs"><p class="font-bold text-white">{{ $st->nama_lokasi }}</p><p class="truncate text-xs text-slate-500">{{ $st->alamat }}</p></td>
                                <td><div class="flex items-center gap-1.5">@forelse ($st->chargers as $c)<span title="#{{ $c->id_charger }} · {{ $c->status }}" class="h-3 w-3 rounded-full {{ ['available' => 'bg-emerald-400', 'reserved' => 'bg-amber-400', 'occupied' => 'bg-sky-400', 'faulted' => 'bg-rose-400'][$c->status] ?? 'bg-slate-500' }}"></span>@empty<span class="text-xs text-slate-500">—</span>@endforelse<span class="ml-1 text-xs text-slate-500">{{ $st->chargers->count() }} port</span></div></td>
                                <td class="whitespace-nowrap">@if ($t)Rp {{ number_format($t->harga_per_kwh, 0, ',', '.') }}/kWh @else<a href="{{ route('admin.tariffs.index') }}" class="badge-amber">Belum ada tarif</a>@endif</td>
                                <td><x-status-badge :status="$st->status" type="station" /></td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.stations.toggle-status', $st) }}">@csrf @method('PATCH')<button class="btn-secondary btn-sm" title="Buka/tutup"><x-icon name="power" class="h-3.5 w-3.5" /></button></form>
                                        <a href="{{ route('admin.stations.edit', $st) }}" class="btn-secondary btn-sm"><x-icon name="edit" class="h-3.5 w-3.5" />Edit</a>
                                        <x-confirm :action="route('admin.stations.destroy', $st)" method="DELETE" title="Hapus stasiun?" text="{{ $st->nama_lokasi }} beserta charger dan tarifnya akan dihapus permanen." confirm="Ya, hapus">
                                            <x-slot name="trigger"><button type="button" class="btn-soft-danger btn-sm" aria-label="Hapus"><x-icon name="trash" class="h-3.5 w-3.5" /></button></x-slot>
                                        </x-confirm>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($stations->hasPages())<div class="border-t border-white/5 p-4">{{ $stations->links() }}</div>@endif
        @else
            <x-empty icon="building" title="Belum ada stasiun" text="Tambahkan stasiun pertama untuk memulai."><a href="{{ route('admin.stations.create') }}" class="btn-primary btn-sm">Tambah stasiun</a></x-empty>
        @endif
    </section>
</x-app-layout>