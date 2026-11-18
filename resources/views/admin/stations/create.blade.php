<x-app-layout title="Tambah Stasiun">
    <x-page-header :back="route('admin.stations.index')" backLabel="Daftar stasiun" eyebrow="Admin" title="Tambah stasiun baru" subtitle="Setelah dibuat, Anda bisa langsung menambahkan charger." />
    @include('admin.stations._form', ['station' => null, 'action' => route('admin.stations.store'), 'method' => 'POST', 'withTariff' => true])
</x-app-layout>