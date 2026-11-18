<x-app-layout :title="'Struk #' . $session->id_session">
    @php
        $loc = $session->charger?->location;
        $v = $session->reservation?->vehicle;
        $hasBreakdown = $session->biaya_energi !== null;
        $paid = (float) $payments->where('status', 'success')->sum('jumlah');
        $debt = (float) $payments->where('status', 'pending')->sum('jumlah');
        $seconds = $session->durationSeconds();
        $interrupted = $session->status === 'interrupted';
    @endphp

    <div class="mx-auto max-w-xl">
        <div class="no-print reveal mb-6 text-center">
            <span class="relative mx-auto flex h-16 w-16 items-center justify-center rounded-full {{ $interrupted ? 'bg-rose-400/15 text-rose-300' : 'bg-emerald-400/15 text-emerald-300' }}"><x-icon :name="$interrupted ? 'alert' : 'check-circle'" class="h-8 w-8" /></span>
            <h1 class="mt-4 text-2xl font-extrabold">{{ $interrupted ? 'Sesi dihentikan operator' : 'Pengisian selesai' }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $interrupted ? 'Anda hanya ditagih untuk energi yang sudah tersalurkan.' : 'Terima kasih sudah mengisi daya bersama EVCharge.' }}</p>
        </div>

        <article class="receipt card reveal overflow-hidden" style="--i:1">
            <div class="bg-gradient-to-br from-emerald-400/15 to-cyan-400/10 p-6 text-center">
                <x-application-logo class="justify-center" size="h-9 w-9" />
                <p class="mt-4 text-[11px] font-bold uppercase tracking-widest text-slate-400">Total tagihan</p>
                <p class="mt-1 text-4xl font-extrabold text-white">Rp {{ number_format((float) $session->estimasi_biaya, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-500">#{{ str_pad((string) $session->id_session, 6, '0', STR_PAD_LEFT) }} · {{ $session->waktu_selesai?->format('d M Y, H:i') }}</p>
            </div>

            <div class="relative my-1 flex items-center"><span class="-ml-3 h-6 w-6 rounded-full bg-ink-950"></span><span class="flex-1 border-t-2 border-dashed border-white/10"></span><span class="-mr-3 h-6 w-6 rounded-full bg-ink-950"></span></div>

            <dl class="space-y-3 p-6 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Lokasi</dt><dd class="text-right font-bold text-white">{{ $loc->nama_lokasi ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Charger</dt><dd class="font-bold text-white">#{{ $session->id_charger }} · {{ $session->charger->charger_type ?? '' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Kendaraan</dt><dd class="font-bold text-white">{{ $v ? $v->merek . ' ' . $v->model . ' (' . $v->nomor_polisi . ')' : '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Durasi</dt><dd class="font-bold text-white">{{ sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Energi</dt><dd class="font-bold text-white">{{ number_format((float) $session->energi_kwh, 2, ',', '.') }} kWh</dd></div>
            </dl>

            <div class="border-t border-white/5 p-6 text-sm">
                <p class="mb-3 text-[11px] font-bold uppercase tracking-widest text-slate-500">Rincian biaya</p>
                @if ($hasBreakdown)
                    <dl class="space-y-2.5">
                        <div class="flex justify-between"><dt class="text-slate-400">Energi ({{ number_format((float) $session->energi_kwh, 2, ',', '.') }} kWh × Rp {{ number_format((float) $session->harga_per_kwh, 0, ',', '.') }})</dt><dd class="font-semibold text-white">Rp {{ number_format((float) $session->biaya_energi, 0, ',', '.') }}</dd></div>
                        @if ((float) $session->biaya_layanan > 0)<div class="flex justify-between"><dt class="text-slate-400">Biaya layanan</dt><dd class="font-semibold text-white">Rp {{ number_format((float) $session->biaya_layanan, 0, ',', '.') }}</dd></div>@endif
                        @if ((float) $session->biaya_overstay > 0)<div class="flex justify-between"><dt class="text-rose-300">Denda overstay</dt><dd class="font-semibold text-rose-300">Rp {{ number_format((float) $session->biaya_overstay, 0, ',', '.') }}</dd></div>@endif
                    </dl>
                @endif
                <div class="mt-3 flex justify-between border-t border-white/5 pt-3 text-base"><dt class="font-bold text-white">Total</dt><dd class="font-extrabold text-emerald-300">Rp {{ number_format((float) $session->estimasi_biaya, 0, ',', '.') }}</dd></div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if ($paid > 0)<span class="badge-emerald">Dibayar saldo Rp {{ number_format($paid, 0, ',', '.') }}</span>@endif
                    @if ($debt > 0)<span class="badge-amber">Belum lunas Rp {{ number_format($debt, 0, ',', '.') }}</span>@endif
                </div>
                @if ($debt > 0)<a href="{{ route('topup.index') }}" class="no-print btn-warn btn-sm mt-4">Top up untuk melunasi</a>@endif
            </div>
        </article>

        <div class="no-print reveal mt-6 grid gap-3 sm:grid-cols-2" style="--i:2">
            @if (! $reviewExists && $loc)<a href="{{ route('reviews.create', $loc->id_location) }}" class="btn-primary"><x-icon name="star" class="h-4 w-4" />Beri ulasan</a>@endif
            <button type="button" onclick="window.print()" class="btn-secondary" data-no-loading><x-icon name="printer" class="h-4 w-4" />Cetak struk</button>
            <a href="{{ route('transactions.index') }}" class="btn-secondary">Riwayat transaksi</a>
            <a href="{{ route('dashboard') }}" class="btn-ghost">Ke beranda</a>
        </div>
    </div>
</x-app-layout>