@props(['title', 'subtitle' => null, 'eyebrow' => null, 'back' => null, 'backLabel' => 'Kembali'])

<div {{ $attributes->merge(['class' => 'reveal mb-7 flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="mb-3 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 transition hover:text-emerald-300">
                <x-icon name="arrow-left" class="h-3.5 w-3.5" /> {{ $backLabel }}
            </a>
        @endif
        @if ($eyebrow)
            <p class="eyebrow mb-1.5">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1.5 max-w-2xl text-sm text-slate-400">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>