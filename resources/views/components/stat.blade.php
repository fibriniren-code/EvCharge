@props(['label', 'value' => 0, 'icon' => 'bolt', 'tone' => 'emerald', 'prefix' => '', 'suffix' => '', 'decimals' => 0, 'hint' => null, 'i' => 0])

@php
    $t = [
        'emerald' => ['text-emerald-300', 'from-emerald-400/20', 'border-emerald-400/20'],
        'sky'     => ['text-sky-300', 'from-sky-400/20', 'border-sky-400/20'],
        'amber'   => ['text-amber-300', 'from-amber-400/20', 'border-amber-400/20'],
        'rose'    => ['text-rose-300', 'from-rose-400/20', 'border-rose-400/20'],
        'violet'  => ['text-violet-300', 'from-violet-400/20', 'border-violet-400/20'],
        'slate'   => ['text-slate-300', 'from-slate-400/15', 'border-white/10'],
    ][$tone];
@endphp

<div {{ $attributes->merge(['class' => 'card card-hover reveal relative overflow-hidden p-5']) }} style="--i: {{ $i }}">
    <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-gradient-to-br {{ $t[1] }} to-transparent blur-2xl"></div>
    <div class="flex items-start justify-between gap-3">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
        <span class="flex h-9 w-9 items-center justify-center rounded-xl border {{ $t[2] }} bg-white/[0.04] {{ $t[0] }}"><x-icon :name="$icon" class="h-[18px] w-[18px]" /></span>
    </div>
    <p class="mt-3 text-3xl font-extrabold tracking-tight text-white">
        <span data-count="{{ $value }}" data-prefix="{{ $prefix }}" data-suffix="{{ $suffix }}" data-decimals="{{ $decimals }}">{{ $prefix }}{{ number_format((float) $value, $decimals, ',', '.') }}{{ $suffix }}</span>
    </p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>