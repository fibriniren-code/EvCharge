<x-app-layout title="Ulasan & Keluhan">
    <x-page-header eyebrow="Operator" title="Ulasan & keluhan" subtitle="Tanggapi ulasan dan keluhan pengemudi tentang alat dan tempat pengisian." />

    @include('partials.reviews-board', [
        'prefix'  => 'operator',
        'filters' => ['unreplied' => 'Belum dibalas', 'complaints' => 'Keluhan (rating ≤ 2)', 'all' => 'Semua'],
    ])
</x-app-layout>