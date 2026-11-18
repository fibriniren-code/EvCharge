@props(['action', 'method' => 'POST', 'title' => 'Konfirmasi tindakan', 'text' => null, 'confirm' => 'Ya, lanjutkan', 'tone' => 'danger', 'icon' => 'alert'])

@php
    $toneCls = $tone === 'danger'
        ? ['bg-rose-400/10 text-rose-300 border-rose-400/25', 'btn-danger']
        : ($tone === 'warn' ? ['bg-amber-400/10 text-amber-300 border-amber-400/25', 'btn-warn'] : ['bg-emerald-400/10 text-emerald-300 border-emerald-400/25', 'btn-primary']);
@endphp

<div x-data="{ open: false }" x-effect="document.body.classList.toggle('modal-open', open)" {{ $attributes->merge(['class' => 'inline-block']) }}>
    <span @click="open = true" class="contents">{{ $trigger }}</span>

    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-[95] flex items-end justify-center p-4 sm:items-center">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-ink-950/80 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 sm:scale-100"
                 x-transition:leave-end="opacity-0 sm:scale-95"
                 class="card relative w-full max-w-md bg-ink-800 p-6 shadow-2xl">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border {{ $toneCls[0] }}"><x-icon :name="$icon" class="h-5 w-5" /></span>
                    <div>
                        <h3 class="text-base font-bold text-white">{{ $title }}</h3>
                        @if ($text)
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-400">{{ $text }}</p>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ $action }}" class="mt-6">
                    @csrf
                    @if (strtoupper($method) !== 'POST')
                        @method($method)
                    @endif
                    {{ $slot }}
                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" class="btn-secondary" @click="open = false">Batal</button>
                        <button type="submit" class="{{ $toneCls[1] }}">{{ $confirm }}</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>