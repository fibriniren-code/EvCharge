@php $stars = fn ($v) => str_repeat('★', (int) round($v)) . str_repeat('☆', 5 - (int) round($v)); @endphp

<div class="grid gap-4 sm:grid-cols-4">
    <x-stat label="Total ulasan" :value="$total" icon="chat" :i="1" />
    <x-stat label="Belum dibalas" :value="$unreplied" icon="alert" tone="amber" :i="2" />
    <x-stat label="Rating alat" :value="$avgAlat" icon="plug" tone="sky" suffix=" / 5" :decimals="1" :i="3" />
    <x-stat label="Rating tempat" :value="$avgTempat" icon="building" tone="violet" suffix=" / 5" :decimals="1" :i="4" />
</div>

<div class="reveal mb-5 mt-6 flex flex-wrap gap-2" style="--i:4">
    @foreach ($filters as $key => $label)
        <a href="{{ route($prefix . '.reviews.index', ['filter' => $key]) }}" class="chip {{ $filter === $key ? 'chip-active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="space-y-4">
    @forelse ($reviews as $r)
        @php $low = $r->rating_alat <= 2 || $r->rating_tempat <= 2; @endphp
        <article class="card reveal p-5 sm:p-6 {{ $low && ! $r->balasan_operator ? 'border-rose-400/25' : '' }}" style="--i: {{ min($loop->index + 1, 8) }}" x-data="{ reply: false }">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/5 font-extrabold text-white">{{ strtoupper(mb_substr($r->user->name ?? '?', 0, 1)) }}</span>
                    <div><p class="text-sm font-bold text-white">{{ $r->user->name ?? 'Pengguna dihapus' }}</p><p class="text-xs text-slate-500">{{ $r->location->nama_lokasi ?? '—' }} · {{ $r->created_at->diffForHumans() }}</p></div>
                </div>
                <div class="flex items-center gap-2">
                    @if ($low && ! $r->balasan_operator)<span class="badge-rose">Keluhan</span>@endif
                    @if ($r->balasan_operator)<span class="badge-emerald">Sudah dibalas</span>@else<span class="badge-amber">Menunggu balasan</span>@endif
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-white/[0.03] p-4"><p class="text-xs text-slate-500">Alat · <span class="text-amber-300">{{ $stars($r->rating_alat) }}</span></p><p class="mt-1.5 text-sm text-slate-300">{{ $r->ulasan_alat ?: '—' }}</p></div>
                <div class="rounded-xl bg-white/[0.03] p-4"><p class="text-xs text-slate-500">Tempat · <span class="text-amber-300">{{ $stars($r->rating_tempat) }}</span></p><p class="mt-1.5 text-sm text-slate-300">{{ $r->ulasan_tempat ?: '—' }}</p></div>
            </div>

            @if ($r->foto_lokasi)<a href="{{ asset('storage/' . $r->foto_lokasi) }}" target="_blank" rel="noopener" class="mt-4 inline-block"><img src="{{ asset('storage/' . $r->foto_lokasi) }}" alt="Foto lokasi" class="h-24 rounded-xl border border-white/10 object-cover transition hover:scale-105"></a>@endif

            @if ($r->balasan_operator)
                <div class="mt-4 rounded-xl border border-emerald-400/20 bg-emerald-400/[0.06] p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-emerald-300">Tanggapan</p><p class="mt-1 text-sm text-slate-300">{{ $r->balasan_operator }}</p></div>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <button type="button" class="btn-secondary btn-sm" @click="reply = !reply"><x-icon name="chat" class="h-3.5 w-3.5" />{{ $r->balasan_operator ? 'Ubah tanggapan' : 'Balas' }}</button>
                @if ($prefix === 'admin')
                    <x-confirm :action="route('admin.reviews.destroy', $r->id_review)" method="DELETE" title="Hapus ulasan?" text="Ulasan yang tidak pantas akan dihapus permanen." confirm="Ya, hapus">
                        <x-slot name="trigger"><button type="button" class="btn-soft-danger btn-sm"><x-icon name="trash" class="h-3.5 w-3.5" />Hapus</button></x-slot>
                    </x-confirm>
                @endif
            </div>

            <form x-show="reply" x-cloak x-transition method="POST" action="{{ route($prefix . '.reviews.reply', $r->id_review) }}" class="mt-4 space-y-3">
                @csrf
                <textarea name="balasan" rows="3" maxlength="1000" required class="input" placeholder="Tulis tanggapan untuk pengemudi…">{{ $r->balasan_operator }}</textarea>
                <div class="flex justify-end gap-2"><button type="button" class="btn-ghost btn-sm" @click="reply = false">Batal</button><button type="submit" class="btn-primary btn-sm">Kirim tanggapan</button></div>
            </form>
        </article>
    @empty
        <div class="card"><x-empty icon="chat" title="Tidak ada ulasan" text="Belum ada ulasan pada filter ini." /></div>
    @endforelse
</div>

@if ($reviews->hasPages())<div class="mt-6">{{ $reviews->links() }}</div>@endif