@props(['name', 'show' => false, 'maxWidth' => '2xl'])

@php
    $maxWidth = ['sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl'][$maxWidth];
@endphp

<div
    x-data="{ show: @js($show) }"
    x-init="$watch('show', v => document.body.classList.toggle('modal-open', v))"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-[90] flex items-end justify-center p-4 sm:items-center"
>
    <div x-show="show" x-transition.opacity class="absolute inset-0 bg-ink-950/80 backdrop-blur-sm" x-on:click="show = false"></div>

    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 sm:scale-100"
         x-transition:leave-end="opacity-0 sm:scale-95"
         class="card relative w-full {{ $maxWidth }} overflow-hidden bg-ink-800 p-6 shadow-2xl">
        {{ $slot }}
    </div>
</div>