<x-app-layout title="Mobil Saya">
    <x-page-header eyebrow="Garasi" title="Mobil listrik saya" subtitle="Kendaraan terdaftar dipakai untuk mengecek kecocokan konektor saat reservasi.">
        <x-slot name="actions"><a href="{{ route('cars.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" />Tambah mobil</a></x-slot>
    </x-page-header>

    @if ($vehicles->isEmpty())
        <div class="card reveal"><x-empty icon="car" title="Belum ada kendaraan" text="Tambahkan mobil listrik pertama Anda untuk mulai memesan slot."><a href="{{ route('cars.create') }}" class="btn-primary btn-sm">Tambah mobil</a></x-empty></div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($vehicles as $v)
                <article class="card card-hover reveal relative overflow-hidden p-6" style="--i: {{ min($loop->index, 8) }}" x-data="{ edit: false }">
                    <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-gradient-to-br from-emerald-400/20 to-transparent blur-2xl"></div>
                    <div class="relative flex items-start justify-between">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400/20 to-cyan-400/10 text-emerald-300"><x-icon name="car" class="h-7 w-7" /></span>
                        <span class="rounded-lg border border-white/15 bg-white/[0.06] px-2.5 py-1 font-mono text-xs font-bold tracking-wider text-white">{{ $v->nomor_polisi }}</span>
                    </div>
                    <h3 class="relative mt-5 text-lg font-bold text-white">{{ $v->merek }} {{ $v->model }}</h3>
                    <div class="relative mt-3 flex flex-wrap gap-2"><span class="badge-emerald">{{ $v->tipe_konektor }}</span><span class="badge-slate"><x-icon name="battery" class="h-3 w-3" />{{ (float) $v->kapasitas_baterai_kwh }} kWh</span></div>
                    <div class="relative mt-5 flex gap-2 border-t border-white/5 pt-4">
                        <button type="button" class="btn-secondary btn-sm flex-1" @click="edit = true"><x-icon name="edit" class="h-3.5 w-3.5" />Ubah</button>
                        <x-confirm :action="route('vehicles.destroy', $v->id_vehicle)" method="DELETE" title="Hapus kendaraan?" :text="$v->merek . ' ' . $v->model . ' (' . $v->nomor_polisi . ') akan dihapus dari akun Anda.'" confirm="Ya, hapus">
                            <x-slot name="trigger"><button type="button" class="btn-soft-danger btn-sm" aria-label="Hapus"><x-icon name="trash" class="h-3.5 w-3.5" /></button></x-slot>
                        </x-confirm>
                    </div>

                    {{-- Modal ubah --}}
                    <template x-teleport="body">
                        <div x-show="edit" x-cloak @keydown.escape.window="edit = false" class="fixed inset-0 z-[95] flex items-end justify-center p-4 sm:items-center">
                            <div x-show="edit" x-transition.opacity class="absolute inset-0 bg-ink-950/80 backdrop-blur-sm" @click="edit = false"></div>
                            <form method="POST" action="{{ route('vehicles.update', $v->id_vehicle) }}" x-show="edit" x-transition class="card relative w-full max-w-md space-y-4 bg-ink-800 p-6 shadow-2xl">
                                @csrf @method('PUT')
                                <h3 class="text-lg font-bold">Ubah kendaraan</h3>
                                <div class="grid grid-cols-2 gap-3">
                                    <div><label class="label">Merek</label><input name="merek" value="{{ $v->merek }}" required class="input"></div>
                                    <div><label class="label">Model</label><input name="model" value="{{ $v->model }}" required class="input"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div><label class="label">Plat nomor</label><input name="nomor_polisi" value="{{ $v->nomor_polisi }}" required class="input uppercase"></div>
                                    <div><label class="label">Baterai (kWh)</label><input type="number" step="0.1" name="kapasitas_baterai_kwh" value="{{ (float) $v->kapasitas_baterai_kwh }}" required class="input"></div>
                                </div>
                                <div><label class="label">Konektor</label><select name="tipe_konektor" class="input">@foreach ($connectors as $c)<option value="{{ $c }}" @selected($v->tipe_konektor === $c)>{{ $c }}</option>@endforeach</select></div>
                                <div class="flex justify-end gap-2 pt-2"><button type="button" class="btn-secondary" @click="edit = false">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
                            </form>
                        </div>
                    </template>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>