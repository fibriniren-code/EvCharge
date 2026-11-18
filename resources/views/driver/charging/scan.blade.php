<x-app-layout title="Scan & Charge">
    <x-page-header eyebrow="Mulai charging" title="Scan & Charge" subtitle="Pindai QR di charger atau pilih reservasi terkonfirmasi Anda." />

    <div class="grid gap-5 lg:grid-cols-5" x-data="scanner({ prefill: @js($prefill), url: '{{ route('driver.charging.processScan') }}' })">
        <section class="card reveal lg:col-span-3" style="--i:1">
            <div class="grid grid-cols-2 gap-1 border-b border-white/5 p-2">
                <button type="button" @click="setMode('camera')" :class="mode === 'camera' ? 'bg-white/10 text-white' : 'text-slate-400'" class="flex items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-semibold transition"><x-icon name="camera" class="h-4 w-4" />Kamera QR</button>
                <button type="button" @click="setMode('manual')" :class="mode === 'manual' ? 'bg-white/10 text-white' : 'text-slate-400'" class="flex items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-semibold transition"><x-icon name="edit" class="h-4 w-4" />Input manual</button>
            </div>

            <div class="p-6">
                {{-- Kamera --}}
                <div x-show="mode === 'camera'">
                    <div class="relative mx-auto aspect-square w-full max-w-sm overflow-hidden rounded-3xl border border-white/10 bg-ink-950">
                        <div id="qr-reader" class="h-full w-full"></div>
                        <div x-show="!scanning" class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-ink-950/90 p-6 text-center">
                            <span class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-400/10 text-emerald-300 ring-pulse"><x-icon name="scan" class="h-8 w-8" /></span>
                            <p class="text-sm text-slate-400">Arahkan kamera ke QR code di charger.</p>
                            <button type="button" class="btn-primary" @click="startCamera()">Aktifkan kamera</button>
                        </div>
                        <template x-if="scanning"><div class="pointer-events-none absolute inset-6"><span class="absolute left-0 top-0 h-8 w-8 rounded-tl-xl border-l-4 border-t-4 border-emerald-400"></span><span class="absolute right-0 top-0 h-8 w-8 rounded-tr-xl border-r-4 border-t-4 border-emerald-400"></span><span class="absolute bottom-0 left-0 h-8 w-8 rounded-bl-xl border-b-4 border-l-4 border-emerald-400"></span><span class="absolute bottom-0 right-0 h-8 w-8 rounded-br-xl border-b-4 border-r-4 border-emerald-400"></span></div></template>
                    </div>
                    <p x-show="camError" x-text="camError" class="mt-4 text-center text-sm text-rose-300"></p>
                    <div class="mt-5">
                        <label class="label">Atau ketik kode charger</label>
                        <div class="flex gap-2"><input x-model="qr" @keydown.enter.prevent="submit({ qr_code: qr })" class="input" placeholder="mis. CHG-VOLTRON-02"><button type="button" class="btn-secondary" @click="submit({ qr_code: qr })">Cek</button></div>
                    </div>
                </div>

                {{-- Manual --}}
                <div x-show="mode === 'manual'" x-cloak>
                    <label class="label" for="booking">ID Booking</label>
                    <div class="flex gap-2"><input id="booking" x-model="booking" @keydown.enter.prevent="submit({ booking_id: booking })" inputmode="numeric" class="input" placeholder="Nomor reservasi, mis. 12"><button type="button" class="btn-primary" @click="submit({ booking_id: booking })">Verifikasi</button></div>
                    <p class="mt-3 text-xs text-slate-500">ID booking ada di halaman detail reservasi.</p>
                </div>

                {{-- Hasil --}}
                <div x-show="state !== 'idle'" x-cloak x-transition class="mt-6">
                    <div x-show="state === 'loading'" class="flex items-center gap-3 rounded-xl bg-white/[0.04] p-4 text-sm text-slate-300"><span class="h-5 w-5 animate-spin rounded-full border-2 border-white/20 border-t-emerald-400"></span>Memverifikasi…</div>
                    <div x-show="state === 'error'" class="flex items-start gap-3 rounded-xl border border-rose-400/30 bg-rose-400/10 p-4 text-sm text-rose-200"><x-icon name="x-circle" class="h-5 w-5 shrink-0" /><span x-text="message"></span></div>
                    <div x-show="state === 'ok'" class="rounded-xl border border-emerald-400/30 bg-emerald-400/10 p-5">
                        <div class="flex items-center gap-3 text-emerald-300"><x-icon name="check-circle" class="h-6 w-6" /><p class="font-bold" x-text="result.charger_label"></p></div>
                        <p class="mt-1 pl-9 text-sm text-slate-400" x-text="result.location"></p>
                        <form method="POST" action="{{ route('driver.charging.start') }}" class="mt-4" data-no-loading>
                            @csrf
                            <input type="hidden" name="reservation_id" :value="result.reservation_id">
                            <input type="hidden" name="charging_type" value="full">
                            <button type="submit" class="btn-primary btn-lg btn-block" data-no-loading><x-icon name="play" class="h-5 w-5" />Mulai pengisian</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        {{-- Reservasi saya --}}
        <aside class="card reveal lg:col-span-2" style="--i:2">
            <div class="card-head"><h2 class="font-bold">Reservasi aktif</h2></div>
            <div class="space-y-3 p-4">
                @forelse ($reservations as $r)
                    <div class="rounded-xl border border-white/[0.07] p-4">
                        <div class="flex items-center justify-between gap-2"><p class="truncate text-sm font-bold text-white">{{ $r->charger?->location?->nama_lokasi }}</p><x-status-badge :status="$r->status" type="reservation" /></div>
                        <p class="mt-1 text-xs text-slate-500">#{{ $r->id_reservation }} · Charger #{{ $r->charger_id }} · {{ $r->waktu_mulai->format('H:i') }}</p>
                        @if ($r->status === 'confirmed')
                            <button type="button" class="btn-soft-emerald btn-sm mt-3 w-full" @click="booking = '{{ $r->id_reservation }}'; setMode('manual'); submit({ booking_id: '{{ $r->id_reservation }}' })">Pilih &amp; verifikasi</button>
                        @else
                            <a href="{{ route('reservations.show', $r->id_reservation) }}" class="btn-warn btn-sm mt-3 w-full">Konfirmasi dulu</a>
                        @endif
                    </div>
                @empty
                    <x-empty icon="calendar" title="Belum ada reservasi aktif" text="Pesan slot terlebih dahulu untuk memulai charging."><a href="{{ route('spklu.index') }}" class="btn-primary btn-sm">Cari SPKLU</a></x-empty>
                @endforelse
            </div>
        </aside>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('scanner', (cfg) => ({
                mode: cfg.prefill ? 'manual' : 'camera', qr: '', booking: cfg.prefill || '',
                scanning: false, camError: '', state: 'idle', message: '', result: {}, qrcode: null,
                init() { if (cfg.prefill) this.submit({ booking_id: cfg.prefill }); },
                setMode(m) { this.mode = m; if (m !== 'camera') this.stopCamera(); },
                async startCamera() {
                    this.camError = '';
                    if (typeof Html5Qrcode === 'undefined') { this.camError = 'Pemindai QR gagal dimuat. Gunakan input manual.'; return; }
                    try {
                        this.qrcode = new Html5Qrcode('qr-reader');
                        await this.qrcode.start({ facingMode: 'environment' }, { fps: 10, qrbox: 220 }, (text) => { this.stopCamera(); this.submit({ qr_code: text }); });
                        this.scanning = true;
                    } catch (e) { this.camError = 'Kamera tidak dapat diakses. Izinkan akses kamera atau gunakan input manual.'; }
                },
                async stopCamera() { try { if (this.qrcode && this.scanning) await this.qrcode.stop(); } catch (e) {} this.scanning = false; },
                async submit(payload) {
                    this.state = 'loading';
                    try {
                        const res = await fetch(cfg.url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify(payload) });
                        const data = await res.json();
                        if (!res.ok || !data.success) { this.state = 'error'; this.message = data.message || 'Verifikasi gagal.'; return; }
                        if (data.already_running) { window.location.href = data.redirect; return; }
                        this.result = data; this.state = 'ok';
                    } catch (e) { this.state = 'error'; this.message = 'Terjadi kesalahan jaringan.'; }
                },
            }));
        });
    </script>
    @endpush
</x-app-layout>