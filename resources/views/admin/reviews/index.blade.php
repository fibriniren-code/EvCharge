<x-app-layout title="Moderasi Ulasan">
    <x-page-header eyebrow="Admin" title="Moderasi ulasan" subtitle="Tinjau, tanggapi, atau hapus ulasan pengemudi." />
    @include('partials.reviews-board', ['prefix' => 'admin', 'filters' => ['all' => 'Semua', 'unreplied' => 'Belum dibalas']])
</x-app-layout>