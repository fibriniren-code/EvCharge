<x-app-layout title="Ulasan Terkirim">
    <div class="mx-auto flex max-w-md flex-col items-center py-16 text-center">
        <span class="relative flex h-24 w-24 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-300 ring-pulse animate-scale-in"><x-icon name="check" class="h-12 w-12" :stroke="2.6" /></span>
        <h1 class="reveal mt-8 text-3xl font-extrabold" style="--i:1">Terima kasih!</h1>
        <p class="reveal mt-2 text-slate-400" style="--i:2">Ulasan Anda membantu pengemudi lain dan operator meningkatkan layanan.</p>
        <div class="reveal mt-8 flex gap-3" style="--i:3"><a href="{{ route('spklu.index') }}" class="btn-primary">Cari SPKLU</a><a href="{{ route('dashboard') }}" class="btn-secondary">Ke beranda</a></div>
    </div>
</x-app-layout>