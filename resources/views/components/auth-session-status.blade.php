@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-300 animate-scale-in']) }}>
        <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0" />
        <span>{{ $status }}</span>
    </div>
@endif