@props(['icon' => 'bolt', 'title', 'text' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <div class="relative mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] text-slate-400">
        <x-icon :name="$icon" class="h-7 w-7" />
        <span class="absolute -inset-2 -z-10 rounded-3xl bg-gradient-to-br from-emerald-400/10 to-cyan-400/5 blur-xl"></span>
    </div>
    <h3 class="text-base font-bold text-white">{{ $title }}</h3>
    @if ($text)
        <p class="mt-1.5 max-w-sm text-sm text-slate-400">{{ $text }}</p>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="mt-5 flex flex-wrap justify-center gap-2">{{ $slot }}</div>
    @endif
</div>