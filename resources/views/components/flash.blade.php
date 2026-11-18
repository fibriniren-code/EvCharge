@php
    $toasts = [];
    foreach (['success' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'error'] as $key => $type) {
        if (session()->has($key) && is_string(session($key))) {
            $toasts[] = ['type' => $type, 'text' => session($key)];
        }
    }
    if ($errors->has('error')) {
        $toasts[] = ['type' => 'error', 'text' => $errors->first('error')];
    }
@endphp

@if (count($toasts))
    <div class="no-print pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-3 px-4 sm:items-end sm:px-6">
        @foreach ($toasts as $t)
            @php
                $tone = [
                    'success' => ['check-circle', 'text-emerald-300', 'bg-emerald-400', 'border-emerald-400/30'],
                    'info'    => ['info', 'text-sky-300', 'bg-sky-400', 'border-sky-400/30'],
                    'warning' => ['alert', 'text-amber-300', 'bg-amber-400', 'border-amber-400/30'],
                    'error'   => ['x-circle', 'text-rose-300', 'bg-rose-400', 'border-rose-400/30'],
                ][$t['type']];
            @endphp
            <div x-data="{ open: true }" x-init="setTimeout(() => open = false, {{ 5200 + $loop->index * 600 }})"
                 x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-2xl border {{ $tone[3] }} bg-ink-800/95 shadow-2xl backdrop-blur-xl" role="status">
                <div class="flex items-start gap-3 p-4">
                    <x-icon :name="$tone[0]" class="mt-0.5 h-5 w-5 shrink-0 {{ $tone[1] }}" />
                    <p class="flex-1 text-sm font-medium leading-relaxed text-slate-100">{{ $t['text'] }}</p>
                    <button type="button" @click="open = false" class="text-slate-500 transition hover:text-white" aria-label="Tutup"><x-icon name="x" class="h-4 w-4" /></button>
                </div>
                <div class="toast-bar absolute inset-x-0 bottom-0 h-0.5 {{ $tone[2] }}"></div>
            </div>
        @endforeach
    </div>
@endif