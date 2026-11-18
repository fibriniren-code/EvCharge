@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li class="field-error animate-fade-in">{{ $message }}</li>
        @endforeach
    </ul>
@endif