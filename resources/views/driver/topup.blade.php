<x-app-layout title="Isi Saldo">
    <x-page-header eyebrow="Dompet" title="Isi saldo" subtitle="Saldo dipakai untuk membayar sesi charging secara otomatis." />

    <div class="grid gap-5 lg:grid-cols-5" x-data="{ amount: {{ (int) old('amount', 100000) }}, method: '{{ old('metode', $methods[0]) }}' }">
        <form method="POST" action="{{ route('topup.store') }}" class="space-y-5 lg:col-span-3">
            @csrf
            @if ($outstanding > 0)
                <div class="reveal flex items-start gap-3 rounded-2xl border border-amber-400/30 bg-amber-400/10 p-4 text-sm text-amber-200"><x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0" />Tagihan tertunggak Rp {{ number_format($outstanding, 0, ',', '.') }} akan dilunasi otomatis dari top up ini.</div>
            @endif

            <section class="card reveal p-6" style="--i:1">
                <h2 class="font-bold">Nominal</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ([50000, 100000, 200000, 300000, 500000, 1000000] as $n)
                        <label class="choice justify-center !p-3.5 text-center"><input type="radio" class="sr-only" value="{{ $n }}" x-model.number="amount"><span class="text-sm font-extrabold text-white">Rp {{ number_format($n, 0, ',', '.') }}</span></label>
                    @endforeach
                </div>
                <div class="relative mt-4">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-500">Rp</span>
                    <input type="number" name="amount" min="10000" max="10000000" step="1000" x-model.number="amount" required class="input pl-11 text-lg font-extrabold" placeholder="Nominal lain">
                </div>
                <p class="mt-2 text-xs text-slate-500">Minimal Rp 10.000 · maksimal Rp 10.000.000.</p>
                <x-input-error :messages="$errors->get('amount')" class="mt-1" />
            </section>

            <section class="card reveal p-6" style="--i:2">
                <h2 class="font-bold">Metode pembayaran</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($methods as $m)
                        <label class="choice"><input type="radio" name="metode" value="{{ $m }}" x-model="method" class="sr-only"><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/5 text-slate-300"><x-icon :name="$m === 'QRIS' ? 'scan' : ($m === 'Kartu Kredit' ? 'receipt' : 'wallet')" class="h-5 w-5" /></span><span class="text-sm font-bold text-white">{{ $m }}</span></label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('metode')" class="mt-2" />
                <p class="mt-4 text-xs text-slate-500">Mode simulasi: saldo langsung bertambah tanpa gateway pembayaran sungguhan.</p>
            </section>

            <button type="submit" class="btn-primary btn-lg btn-block reveal" style="--i:3" :disabled="!amount || amount < 10000">Top up <span x-text="rupiah(amount)"></span> <x-icon name="arrow-right" class="h-4 w-4" /></button>
        </form>

        <aside class="space-y-5 lg:col-span-2">
            <section class="card reveal relative overflow-hidden p-6" style="--i:1">
                <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-emerald-400/20 blur-3xl"></div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Saldo saat ini</p>
                <p class="mt-2 text-4xl font-extrabold text-white"><span data-count="{{ (float) auth()->user()->balance }}" data-prefix="Rp ">Rp {{ number_format((float) auth()->user()->balance, 0, ',', '.') }}</span></p>
                <p class="mt-3 text-xs text-slate-500">Setelah top up: <b class="text-emerald-300" x-text="rupiah({{ (float) auth()->user()->balance }} + (Number(amount) || 0))"></b></p>
            </section>

            <section class="card reveal" style="--i:2">
                <div class="card-head"><h2 class="font-bold">Top up terakhir</h2></div>
                @forelse ($payments as $p)
                    <div class="flex items-center justify-between gap-3 border-b border-white/5 px-6 py-3.5 last:border-0">
                        <div><p class="text-sm font-bold text-white">{{ $p->metode }}</p><p class="text-xs text-slate-500">{{ ($p->waktu_pembayaran ?? $p->created_at)->format('d M Y, H:i') }}</p></div>
                        <p class="text-sm font-extrabold text-emerald-300">+Rp {{ number_format((float) $p->jumlah, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <x-empty icon="wallet" title="Belum ada top up" class="!py-10" />
                @endforelse
            </section>
        </aside>
    </div>
</x-app-layout>