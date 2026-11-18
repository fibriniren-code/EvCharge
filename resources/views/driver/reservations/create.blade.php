<x-app-layout title="Reservasi Slot">
    @php
        $rate = (float) ($tariff->harga_per_kwh ?? \App\Models\ChargingSession::FALLBACK_RATE);
        $service = (float) ($tariff->biaya_layanan ?? 0);
        $minimum = (float) ($tariff->biaya_minimum ?? 0);
        $power = (float) $charger->power_kw;

        $vehicleData = $vehicles->map(fn ($v) => [
            'id' => $v->id_vehicle,
            'cap' => (float) $v->kapasitas_baterai_kwh,
        ])->values();

        $selected = old('vehicle_id', $vehicles->first()?->id_vehicle);

        $minTime = now()->format('Y-m-d\TH:i');
        $maxTime = now()->addHours($holdHours)->format('Y-m-d\TH:i');
        $defTime = old('start_time', now()->addMinutes(10)->format('Y-m-d\TH:i'));
    @endphp

    <x-page-header
        :back="route('spklu.show', $location->id_location)"
        backLabel="Kembali ke detail stasiun"
        eyebrow="Reservasi"
        title="Pesan slot charging"
        subtitle="Pilih kendaraan dan jadwal. Slot Anda ditahan agar tidak diambil orang lain."
    />

    @if ($errors->any() && ! $errors->has('error'))
        <div class="mb-5 rounded-2xl border border-rose-400/30 bg-rose-400/10 p-4 text-sm text-rose-200">
            <p class="font-bold">Reservasi belum bisa dibuat:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('reservations.store') }}"
        x-data="reservation({
            rate: {{ $rate }},
            service: {{ $service }},
            minimum: {{ $minimum }},
            power: {{ $power }},
            vehicles: @js($vehicleData),
            vehicle: @js($selected ? (int) $selected : null),
            start: @js($defTime),
            duration: {{ (int) old('duration_hours', 1) }}
        })"
        class="grid gap-5 lg:grid-cols-3"
    >
        @csrf

        <input type="hidden" name="charger_id" value="{{ $charger->id_charger }}">

        <div class="space-y-5 lg:col-span-2">

            {{-- Kendaraan --}}
            <section class="card reveal p-6" style="--i:1">
                <h2 class="flex items-center gap-2 font-bold">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-400/15 text-xs text-emerald-300">1</span>
                    Pilih kendaraan
                </h2>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @forelse ($vehicles as $v)
                        <label class="choice">
                            <input
                                type="radio"
                                name="vehicle_id"
                                value="{{ $v->id_vehicle }}"
                                x-model.number="vehicle"
                                class="sr-only"
                                @checked((int) $selected === $v->id_vehicle)
                            >

                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/5 text-slate-300">
                                <x-icon name="car" class="h-5 w-5" />
                            </span>

                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-white">
                                    {{ $v->merek }} {{ $v->model }}
                                </span>

                                <span class="block text-xs text-slate-500">
                                    {{ $v->nomor_polisi }} · {{ (float) $v->kapasitas_baterai_kwh }} kWh
                                </span>
                            </span>
                        </label>
                    @empty
                        <div class="rounded-xl border border-amber-400/30 bg-amber-400/10 p-4 text-sm text-amber-200 sm:col-span-2">
                            Anda belum memiliki kendaraan.
                            <a href="{{ route('cars.create') }}" class="font-bold underline">
                                Tambah kendaraan
                            </a>
                            terlebih dahulu.
                        </div>
                    @endforelse
                </div>

                <x-input-error :messages="$errors->get('vehicle_id')" class="mt-3" />
            </section>

            {{-- Jadwal --}}
            <section class="card reveal p-6" style="--i:2">
                <h2 class="flex items-center gap-2 font-bold">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-400/15 text-xs text-emerald-300">2</span>
                    Jadwal pengisian
                </h2>

                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="start_time" value="Waktu mulai" />

                        <input
                            id="start_time"
                            type="datetime-local"
                            name="start_time"
                            x-model="start"
                            min="{{ $minTime }}"
                            max="{{ $maxTime }}"
                            required
                            class="input"
                        >

                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ([0 => 'Sekarang', 30 => '+30 mnt', 60 => '+1 jam', 120 => '+2 jam'] as $min => $lbl)
                                <button
                                    type="button"
                                    class="chip"
                                    @click="setStart({{ $min }})"
                                >
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            Maksimal {{ $holdHours }} jam dari sekarang.
                        </p>

                        <x-input-error :messages="$errors->get('start_time')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Durasi" />

                        <div class="grid grid-cols-4 gap-2">
                            @foreach ([1, 2, 3, 4] as $jam)
                                <label class="choice justify-center !p-3 text-center">
                                    <input
                                        type="radio"
                                        name="duration_hours"
                                        value="{{ $jam }}"
                                        x-model.number="duration"
                                        class="sr-only"
                                    >

                                    <span class="text-sm font-bold text-white">
                                        {{ $jam }} jam
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <x-input-error :messages="$errors->get('duration_hours')" class="mt-1" />
                    </div>
                </div>
            </section>

            {{-- Simulasi biaya --}}
            <section class="card reveal p-6" style="--i:3">
                <h2 class="flex items-center gap-2 font-bold">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-400/15 text-xs text-emerald-300">3</span>
                    Simulasi biaya
                    <span class="text-xs font-medium text-slate-500">(opsional)</span>
                </h2>

                <div class="mt-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400">Energi yang ingin diisi</span>
                        <b class="text-white" x-text="kwh + ' kWh'"></b>
                    </div>

                    <input
                        type="range"
                        min="5"
                        max="100"
                        step="5"
                        x-model.number="kwh"
                        class="mt-3 w-full accent-emerald-400"
                    >

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-white/[0.04] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Estimasi biaya
                            </p>
                            <p class="mt-1 text-xl font-extrabold text-amber-300" x-text="rupiah(cost)"></p>
                        </div>

                        <div class="rounded-xl bg-white/[0.04] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Estimasi waktu
                            </p>
                            <p class="mt-1 text-xl font-extrabold text-white" x-text="eta"></p>
                        </div>

                        <div class="rounded-xl bg-white/[0.04] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Tarif
                            </p>
                            <p class="mt-1 text-xl font-extrabold text-white">
                                Rp {{ number_format($rate, 0, ',', '.') }}
                                <span class="text-xs text-slate-500">/kWh</span>
                            </p>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-slate-500">
                        Biaya sesungguhnya dihitung dari energi yang benar-benar terisi saat sesi selesai.
                    </p>
                </div>
            </section>
        </div>

        {{-- Ringkasan --}}
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <section class="card reveal overflow-hidden" style="--i:2">
                <div class="bg-gradient-to-br from-emerald-400/15 to-cyan-400/10 p-6">
                    <p class="eyebrow">Ringkasan</p>

                    <h3 class="mt-2 text-lg font-bold">
                        {{ $location->nama_lokasi }}
                    </h3>

                    <p class="mt-1 text-xs text-slate-400">
                        {{ $location->alamat }}
                    </p>
                </div>

                <dl class="divide-y divide-white/5 text-sm">
                    <div class="flex justify-between px-6 py-3">
                        <dt class="text-slate-500">Charger</dt>
                        <dd class="font-bold text-white">#{{ $charger->id_charger }}</dd>
                    </div>

                    <div class="flex justify-between px-6 py-3">
                        <dt class="text-slate-500">Konektor</dt>
                        <dd class="text-right font-bold text-white">{{ $charger->charger_type }}</dd>
                    </div>

                    <div class="flex justify-between px-6 py-3">
                        <dt class="text-slate-500">Daya</dt>
                        <dd class="font-bold text-white">{{ $power }} kW</dd>
                    </div>

                    <div class="flex justify-between px-6 py-3">
                        <dt class="text-slate-500">Selesai</dt>
                        <dd class="font-bold text-white" x-text="endLabel"></dd>
                    </div>
                </dl>

                <div class="space-y-3 p-6">
                    <div class="flex gap-3 rounded-xl border border-sky-400/20 bg-sky-400/[0.06] p-3 text-xs leading-relaxed text-sky-200">
                        <x-icon name="clock" class="h-4 w-4 shrink-0" />
                        <p>
                            Slot ditahan <b>{{ $holdHours }} jam</b>.
                            Konfirmasi reservasi sebelum waktu habis, atau slot dilepas otomatis.
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="btn-primary btn-lg btn-block"
                        :disabled="!vehicle"
                    >
                        Buat reservasi
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </button>

                    <a
                        href="{{ route('spklu.show', $location->id_location) }}"
                        class="btn-ghost btn-block"
                    >
                        Batal
                    </a>
                </div>
            </section>
        </aside>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('reservation', (cfg) => ({
                    ...cfg,
                    kwh: 20,

                    get cost() {
                        return Math.max(
                            this.kwh * this.rate + this.service,
                            this.minimum
                        );
                    },

                    get eta() {
                        const mins = Math.max(
                            1,
                            Math.round(this.kwh / this.power * 60)
                        );

                        return mins < 60
                            ? mins + ' mnt'
                            : Math.floor(mins / 60) + ' j ' + (mins % 60) + ' mnt';
                    },

                    get endLabel() {
                        const d = new Date(this.start);

                        if (isNaN(d)) return '—';

                        d.setHours(d.getHours() + Number(this.duration));

                        return d.toLocaleString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    },

                    setStart(min) {
                        const d = new Date(Date.now() + (min + 1) * 60000);
                        const p = n => String(n).padStart(2, '0');

                        this.start =
                            d.getFullYear() + '-' +
                            p(d.getMonth() + 1) + '-' +
                            p(d.getDate()) + 'T' +
                            p(d.getHours()) + ':' +
                            p(d.getMinutes());
                    },

                    init() {
                        const v = this.vehicles.find(x => x.id === this.vehicle);

                        if (v) {
                            this.kwh = Math.max(
                                5,
                                Math.min(100, Math.round(v.cap * 0.6 / 5) * 5)
                            );
                        }

                        this.$watch('vehicle', id => {
                            const x = this.vehicles.find(i => i.id === id);

                            if (x) {
                                this.kwh = Math.max(
                                    5,
                                    Math.min(100, Math.round(x.cap * 0.6 / 5) * 5)
                                );
                            }
                        });
                    }
                }));
            });
        </script>
    @endpush
</x-app-layout>
