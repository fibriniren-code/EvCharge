@props(['status', 'type' => 'charger'])

@php
    $maps = [
        'charger' => [
            'available'   => ['Tersedia', 'emerald'],
            'reserved'    => ['Dipesan', 'amber'],
            'occupied'    => ['Digunakan', 'sky'],
            'faulted'     => ['Rusak', 'rose'],
            'unavailable' => ['Nonaktif', 'slate'],
        ],
        'reservation' => [
            'pending'   => ['Menunggu konfirmasi', 'amber'],
            'confirmed' => ['Terkonfirmasi', 'emerald'],
            'active'    => ['Sedang charging', 'sky'],
            'completed' => ['Selesai', 'slate'],
            'cancelled' => ['Dibatalkan', 'rose'],
            'expired'   => ['Kedaluwarsa', 'slate'],
        ],
        'session' => [
            'running'     => ['Mengisi daya', 'sky'],
            'completed'   => ['Selesai', 'emerald'],
            'interrupted' => ['Dihentikan operator', 'rose'],
            'cancelled'   => ['Dibatalkan', 'slate'],
        ],
        'payment' => [
            'pending'  => ['Belum lunas', 'amber'],
            'success'  => ['Berhasil', 'emerald'],
            'failed'   => ['Gagal', 'rose'],
            'refunded' => ['Dikembalikan', 'slate'],
        ],
        'station' => [
            'aktif' => ['Beroperasi', 'emerald'],
            'tutup' => ['Ditutup sementara', 'rose'],
        ],
    ];

    if ($type === 'station' && $status !== 'aktif') {
        $status = 'tutup';
    }

    [$label, $tone] = $maps[$type][$status] ?? [ucfirst((string) $status), 'slate'];

    $cls = [
        'emerald' => 'badge-emerald', 'amber' => 'badge-amber', 'rose' => 'badge-rose',
        'sky' => 'badge-sky', 'slate' => 'badge-slate', 'violet' => 'badge-violet',
    ][$tone];

    $live = in_array($status, ['occupied', 'running', 'active'], true);
@endphp

<span {{ $attributes->merge(['class' => $cls]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current {{ $live ? 'animate-pulse' : '' }}"></span>{{ $label }}
</span>