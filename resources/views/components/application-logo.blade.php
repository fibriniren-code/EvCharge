@props(['text' => true, 'size' => 'h-10 w-10'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="relative inline-flex {{ $size }} shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-400 text-ink-950 shadow-lg shadow-emerald-500/30">
        <x-icon name="bolt" class="h-[55%] w-[55%]" :stroke="2.4" />
        <span class="absolute inset-0 rounded-xl ring-1 ring-inset ring-white/40"></span>
    </span>
    @if ($text)
        <span class="text-lg font-extrabold tracking-tight text-white">EV<span class="text-gradient">Charge</span></span>
    @endif
</span>