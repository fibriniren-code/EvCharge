<x-app-layout title="Reservasi">
    @php
        $groups = [
            'active' => ['pending', 'confirmed', 'active'],
            'done' => ['completed'],
            'failed' => ['cancelled', 'expired'],
        ];
        $counts = collect($groups)->map(fn ($s) => $reservations->whereIn('status', $s)->count());
    @endphp

    <x-page-header eyebrow="Pemesanan" title="Reservasi saya" subtitle="Pantau slot yang Anda tahan dan riwayat pemesanan.">
        <x-slot name="actions"><a href="{{ route('spklu.index') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" />Reservasi baru</a></x-slot>
    </x-page-header>

    <div x-data="{ tab: 'all' }">
        <div class="reveal mb-5 flex flex-wrap gap-2" style="--i:1">
            @foreach (['all' => ['Semua', $reservations->count()], 'active' => ['Aktif', $counts['active']], 'done' => ['Selesai', $counts['done']], 'failed' => ['Batal / Kedaluwarsa', $counts['failed']]] as $key => [$label, $n])
                <button type="button" class="chip" :class="tab === '{{ $key }}' && 'chip-active'" @click="tab = '{{ $key }}'">{{ $label }} <span class="rounded-full bg-white/10 px-1.5 text-[10px]">{{ $n }}</span></button>
            @endforeach
        </div>

        <div class="space-y-4">
            @forelse ($reservations as $r)
                @php
                    $group = collect($groups)->search(fn ($s) => in_array($r->status, $s, true));
                    $loc = $r->charger?->location;
                @endphp
                <a href="{{ route('reservations.show', $r->id_reservation) }}" x-show="tab === 'all' || tab === '{{ $group }}'"
                   x-transition:enter="transition duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                   class="card card-hover reveal group flex flex-wrap items-center gap-4 p-5" style="--i: {{ min($loop->index + 1, 8) }}">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $group === 'active' ? 'bg-emerald-400/10 text-emerald-300' : 'bg-white/5 text-slate-500' }}"><x-icon name="calendar" class="h-6 w-6" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-bold text-white">{{ $loc->nama_lokasi ?? 'SPKLU' }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">#{{ $r->id_reservation }} · Charger #{{ $r->charger_id }} · {{ $r->waktu_mulai->format('d M Y, H:i') }} – {{ $r->waktu_selesai->format('H:i') }}</p>
                    </div>
                    <x-status-badge :status="$r->status" type="reservation" />
                    <x-icon name="chevron-right" class="h-5 w-5 text-slate-600 transition group-hover:translate-x-0.5 group-hover:text-slate-300" />
                </a>
            @empty
                <div class="card"><x-empty icon="calendar" title="Belum ada reservasi" text="Pesan slot di SPKLU favorit Anda agar tidak kehabisan."><a href="{{ route('spklu.index') }}" class="btn-primary btn-sm">Cari SPKLU</a></x-empty></div>
            @endforelse
        </div>
    </div>
</x-app-layout>