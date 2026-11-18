<x-app-layout title="Cari SPKLU">
    @php
        $stations = $locations->map(function ($l) {
            $tariff = $l->tariffs->where('periode_berlaku', '<=', now())->sortByDesc('periode_berlaku')->first();
            $open = $l->isOpen();

            return [
                'id' => $l->id_location,
                'nama' => $l->nama_lokasi,
                'alamat' => $l->alamat,
                'lat' => (float) $l->latitude,
                'lng' => (float) $l->longitude,
                'open' => $open,
                'jam' => $l->jam_operasional,
                'tersedia' => $open ? $l->chargers->where('status', 'available')->count() : 0,
                'total' => $l->chargers->count(),
                'maxKw' => (float) $l->chargers->max('power_kw'),
                'types' => $l->chargers->pluck('charger_type')->unique()->values()->all(),
                'rate' => $tariff ? (float) $tariff->harga_per_kwh : null,
                'url' => route('spklu.show', $l->id_location),
            ];
        })->values();
    @endphp

    <x-page-header eyebrow="Temukan" title="Cari SPKLU" subtitle="Lihat stasiun pengisian terdekat, cek ketersediaan charger, dan buat rute langsung dari lokasi Anda.">
        <x-slot name="actions">
            <button type="button" id="btn-locate" class="btn-secondary"><x-icon name="navigation" class="h-4 w-4" /> Lokasi saya</button>
            <a href="{{ route('driver.scan') }}" class="btn-primary"><x-icon name="scan" class="h-4 w-4" /> Scan &amp; Charge</a>
        </x-slot>
    </x-page-header>

    {{-- Pencarian & filter --}}
    <div class="card reveal mb-5 p-4" style="--i:1">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                <input id="search" type="search" value="{{ $search }}" placeholder="Cari nama stasiun, alamat, atau jenis charger…" class="input input-icon" autocomplete="off">
            </div>
            <div class="flex flex-wrap gap-2" id="filters">
                <button type="button" class="chip chip-active" data-filter="all">Semua</button>
                <button type="button" class="chip" data-filter="available"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Tersedia</button>
                <button type="button" class="chip" data-filter="fast"><x-icon name="bolt" class="h-3.5 w-3.5" />Fast ≥ 50 kW</button>
                <button type="button" class="chip" data-filter="ac">AC / Type 2</button>
            </div>
        </div>
        <p id="locate-status" class="mt-3 text-xs text-slate-500"><span id="result-count">{{ $stations->count() }}</span> stasiun ditemukan · aktifkan lokasi untuk melihat jarak &amp; stasiun terdekat.</p>
    </div>

    <div x-data="{ view: 'list' }" x-init="$watch('view', v => v === 'map' && setTimeout(() => window.evMap && window.evMap.invalidateSize(), 60))">
        {{-- Toggle mobile --}}
        <div class="mb-4 grid grid-cols-2 gap-1 rounded-xl border border-white/10 bg-white/[0.04] p-1 lg:hidden">
            <button type="button" @click="view = 'list'" :class="view === 'list' ? 'bg-white/10 text-white' : 'text-slate-400'" class="rounded-lg py-2 text-sm font-semibold transition">Daftar</button>
            <button type="button" @click="view = 'map'" :class="view === 'map' ? 'bg-white/10 text-white' : 'text-slate-400'" class="rounded-lg py-2 text-sm font-semibold transition">Peta</button>
        </div>

        <div class="grid gap-5 lg:grid-cols-5">
            {{-- Daftar --}}
            <div :class="view === 'map' ? 'hidden lg:block' : ''" class="lg:col-span-2">
                <div id="station-list" class="space-y-4 lg:max-h-[calc(100vh-15rem)] lg:overflow-y-auto lg:pr-2">
                    @foreach ($locations as $location)
                        @php $st = $stations[$loop->index]; @endphp
                        <article data-station-card="{{ $location->id_location }}" class="card card-hover reveal cursor-pointer p-5" style="--i: {{ min($loop->index, 8) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-bold text-white">{{ $location->nama_lokasi }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-400">{{ $location->alamat }}</p>
                                </div>
                                <x-status-badge :status="$location->status" type="station" />
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                <span data-distance-for="{{ $location->id_location }}" class="badge-sky hidden"></span>
                                <span data-route-for="{{ $location->id_location }}" class="badge-amber hidden"></span>
                                <span class="badge-slate"><x-icon name="clock" class="h-3 w-3" />{{ $location->jam_operasional ?? '—' }}</span>
                                @if ($st['rate'])<span class="badge-slate">Rp {{ number_format($st['rate'], 0, ',', '.') }}/kWh</span>@endif
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @forelse ($location->chargers as $charger)
                                    @php $free = $st['open'] && $charger->status === 'available'; @endphp
                                    @if ($free)
                                        <a href="{{ route('reservations.create', ['location' => $location->id_location, 'charger' => $charger->id_charger]) }}" onclick="event.stopPropagation()" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-400/30 bg-emerald-400/10 px-2.5 py-1.5 text-[11px] font-bold text-emerald-300 transition hover:bg-emerald-400/20" title="Pesan slot">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>#{{ $charger->id_charger }} · {{ (float) $charger->power_kw }} kW
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/[0.03] px-2.5 py-1.5 text-[11px] font-bold text-slate-500" title="{{ ucfirst($charger->status) }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ ['reserved' => 'bg-amber-400', 'occupied' => 'bg-sky-400', 'faulted' => 'bg-rose-400'][$charger->status] ?? 'bg-slate-500' }}"></span>#{{ $charger->id_charger }} · {{ (float) $charger->power_kw }} kW
                                        </span>
                                    @endif
                                @empty
                                    <span class="text-xs text-slate-500">Belum ada charger.</span>
                                @endforelse
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-2 border-t border-white/5 pt-4">
                                <p class="text-xs text-slate-400"><span class="font-extrabold {{ $st['tersedia'] > 0 ? 'text-emerald-300' : 'text-slate-400' }}">{{ $st['tersedia'] }}</span>/{{ $st['total'] }} charger tersedia</p>
                                <div class="flex gap-2">
                                    <button type="button" data-route-station="{{ $location->id_location }}" onclick="event.stopPropagation()" class="btn-secondary btn-sm"><x-icon name="navigation" class="h-3.5 w-3.5" />Rute</button>
                                    <a href="{{ route('spklu.show', $location->id_location) }}" onclick="event.stopPropagation()" class="btn-primary btn-sm">Detail</a>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    <div id="no-result" class="card hidden"><x-empty icon="search" title="Tidak ada stasiun yang cocok" text="Coba ubah kata kunci atau hapus filter." /></div>
                </div>
            </div>

            {{-- Peta --}}
            <div :class="view === 'list' ? 'hidden lg:block' : ''" class="lg:col-span-3">
                <div class="lg:sticky lg:top-24">
                    <div class="card overflow-hidden">
                        <div id="spklu-map" class="h-[62vh] w-full lg:h-[calc(100vh-21rem)] lg:min-h-[420px]"></div>
                    </div>
                    <div id="route-info" class="card mt-4 p-4 text-sm text-slate-400">Pilih tombol <strong class="text-slate-200">Rute</strong> pada stasiun untuk melihat jarak dan estimasi waktu berkendara.</div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {
        const stations = @json($stations).filter(s => Number.isFinite(s.lat) && Number.isFinite(s.lng));
        const mapEl = document.getElementById('spklu-map');
        const statusEl = document.getElementById('locate-status');
        const infoEl = document.getElementById('route-info');
        const locateBtn = document.getElementById('btn-locate');
        const searchEl = document.getElementById('search');

        if (typeof L === 'undefined') {
            mapEl.innerHTML = '<div class="p-6 text-sm text-rose-300">Peta gagal dimuat. Periksa koneksi internet Anda.</div>';
            return;
        }

        const OSRM = 'https://router.project-osrm.org/route/v1/driving';
        const byId = {};
        stations.forEach(s => { byId[s.id] = s; });

        // ---------- Helper ----------
        const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const rad = Math.PI / 180;
        function haversine(a, b, c, d) {
            const dLat = (c - a) * rad, dLng = (d - b) * rad;
            const x = Math.sin(dLat / 2) ** 2 + Math.cos(a * rad) * Math.cos(c * rad) * Math.sin(dLng / 2) ** 2;
            return 6371000 * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
        }
        function fmtDist(m) {
            if (Math.round(m) < 1000) return Math.round(m) + ' m';
            const dg = m < 10000 ? 1 : 0;
            return (m / 1000).toLocaleString('id-ID', { minimumFractionDigits: dg, maximumFractionDigits: dg }) + ' km';
        }
        function fmtDur(sec) {
            const min = Math.round(sec / 60);
            if (min < 1) return '< 1 menit';
            if (min < 60) return min + ' menit';
            const h = Math.floor(min / 60), m = min % 60;
            return h + ' jam' + (m ? ' ' + m + ' menit' : '');
        }
        function setStatus(msg, err) {
            statusEl.textContent = msg;
            statusEl.className = 'mt-3 text-xs ' + (err ? 'text-rose-300' : 'text-slate-500');
        }

        // ---------- Peta ----------
        const map = L.map(mapEl, { scrollWheelZoom: false, zoomControl: true }).setView([-2.5, 118], 5);
        window.evMap = map;
        map.on('focus', () => map.scrollWheelZoom.enable());
        map.on('blur', () => map.scrollWheelZoom.disable());

       L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            className: 'map-tiles-dark',
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const pinIcon = cls => L.divIcon({ className: 'ev-pin-wrap', html: '<div class="ev-pin ' + cls + '"><span>⚡</span></div>', iconSize: [36, 36], iconAnchor: [18, 36], popupAnchor: [0, -32] });
        const iconFor = (s, nearest) => pinIcon(!s.open ? 'closed' : (nearest ? 'nearest' : (s.tersedia < 1 ? 'busy' : '')));
        const meIcon = L.divIcon({ className: 'ev-me-wrap', html: '<div class="ev-me"></div>', iconSize: [18, 18], iconAnchor: [9, 9] });

        let me = null, meMarker = null, meCircle = null, routeLayer = null, activeId = null, nearestId = null;

        const gmaps = s => me
            ? 'https://www.google.com/maps/dir/?api=1&origin=' + me.lat + ',' + me.lng + '&destination=' + s.lat + ',' + s.lng + '&travelmode=driving'
            : 'https://www.google.com/maps/search/?api=1&query=' + s.lat + ',' + s.lng;

        function popupHtml(s) {
            const lurus = s.distance != null ? '<div style="margin-top:.4rem;font-weight:700;color:#7dd3fc">📏 ' + fmtDist(s.distance) + ' (garis lurus)</div>' : '';
            const jalan = s.route ? '<div style="font-weight:700;color:#fcd34d">🛣️ ' + fmtDist(s.route.distance) + ' · ± ' + fmtDur(s.route.duration) + '</div>' : '';
            const btn = 'border:none;border-radius:.5rem;padding:.4rem .7rem;font-size:.72rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block;';
            return '<div style="min-width:220px">'
                + '<strong style="color:#fff;font-size:.95rem">' + esc(s.nama) + '</strong>'
                + '<div style="font-size:.78rem;color:#94a3b8;margin-top:.15rem">' + esc(s.alamat) + '</div>'
                + '<div style="font-size:.8rem;margin-top:.45rem">⚡ Tersedia: <b style="color:#6ee7b7">' + s.tersedia + '/' + s.total + '</b>' + (s.rate ? ' · Rp ' + Math.round(s.rate).toLocaleString('id-ID') + '/kWh' : '') + '</div>'
                + lurus + jalan
                + '<div style="display:flex;gap:.4rem;margin-top:.7rem;flex-wrap:wrap">'
                + '<button type="button" data-route-station="' + s.id + '" style="' + btn + 'background:linear-gradient(90deg,#34d399,#22d3ee);color:#050810">🧭 Rute</button>'
                + '<a href="' + s.url + '" style="' + btn + 'background:rgba(255,255,255,.1);color:#fff">Detail</a>'
                + '<a href="' + gmaps(s) + '" target="_blank" rel="noopener" style="' + btn + 'background:rgba(255,255,255,.1);color:#fff">Google Maps ↗</a>'
                + '</div></div>';
        }

        stations.forEach(s => {
            s.card = document.querySelector('[data-station-card="' + s.id + '"]');
            s.marker = L.marker([s.lat, s.lng], { icon: iconFor(s, false), title: s.nama }).bindPopup(() => popupHtml(s));
        });

        // ---------- Filter & pencarian ----------
        const state = { q: (searchEl.value || '').trim().toLowerCase(), filter: 'all' };

        function matches(s) {
            const text = (s.nama + ' ' + s.alamat + ' ' + s.types.join(' ')).toLowerCase();
            if (state.q && !text.includes(state.q)) return false;
            if (state.filter === 'available') return s.tersedia > 0;
            if (state.filter === 'fast') return s.maxKw >= 50;
            if (state.filter === 'ac') return s.types.some(t => /type 2|\bac\b/i.test(t));
            return true;
        }

        function applyFilter(fit) {
            let shown = 0;
            const pts = [];
            stations.forEach(s => {
                const ok = matches(s);
                if (s.card) s.card.classList.toggle('hidden', !ok);
                if (ok) { if (!map.hasLayer(s.marker)) s.marker.addTo(map); shown++; pts.push([s.lat, s.lng]); }
                else if (map.hasLayer(s.marker)) map.removeLayer(s.marker);
            });
            document.getElementById('result-count').textContent = shown;
            document.getElementById('no-result').classList.toggle('hidden', shown > 0);
            if (fit && pts.length) map.fitBounds(L.latLngBounds(pts), { padding: [50, 50], maxZoom: 15 });
        }

        searchEl.addEventListener('input', () => { state.q = searchEl.value.trim().toLowerCase(); applyFilter(true); });
        document.getElementById('filters').addEventListener('click', e => {
            const b = e.target.closest('[data-filter]');
            if (!b) return;
            state.filter = b.dataset.filter;
            document.querySelectorAll('#filters [data-filter]').forEach(x => x.classList.toggle('chip-active', x === b));
            applyFilter(true);
        });

        // ---------- Jarak di kartu ----------
        function updateCards() {
            stations.forEach(s => {
                const d = document.querySelector('[data-distance-for="' + s.id + '"]');
                const r = document.querySelector('[data-route-for="' + s.id + '"]');
                if (d && s.distance != null) { d.textContent = '📏 ' + fmtDist(s.distance); d.classList.remove('hidden'); }
                if (r) {
                    if (s.route) { r.textContent = '🛣️ ' + fmtDist(s.route.distance) + ' · ' + fmtDur(s.route.duration); r.classList.remove('hidden'); }
                    else r.classList.add('hidden');
                }
            });
            const list = document.getElementById('station-list');
            const nores = document.getElementById('no-result');
            Array.from(list.querySelectorAll('[data-station-card]'))
                .sort((a, b) => (byId[a.dataset.stationCard].distance ?? 1e12) - (byId[b.dataset.stationCard].distance ?? 1e12))
                .forEach(c => list.insertBefore(c, nores));
        }

        // ---------- Rute ----------
        function clearRoute() { if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; } }

        function showInfo(s, viaRoad) {
            const act = '<div class="flex flex-wrap gap-2">'
                + '<a href="' + gmaps(s) + '" target="_blank" rel="noopener" class="btn-primary btn-sm">Buka Google Maps ↗</a>'
                + '<button type="button" id="btn-clear-route" class="btn-secondary btn-sm">✕ Hapus rute</button></div>';
            const main = viaRoad
                ? '<div class="mt-1 text-2xl font-extrabold text-sky-300">' + fmtDist(s.route.distance) + ' <span class="text-sm font-semibold text-slate-400">· ± ' + fmtDur(s.route.duration) + ' berkendara</span></div><div class="text-xs text-slate-500">Garis lurus: ' + fmtDist(s.distance) + '</div>'
                : '<div class="mt-1 text-2xl font-extrabold text-amber-300">' + fmtDist(s.distance) + ' <span class="text-sm font-semibold text-slate-400">· garis lurus</span></div><div class="text-xs text-rose-300">Rute jalan tidak dapat dimuat, ditampilkan garis putus-putus.</div>';
            infoEl.innerHTML = '<div class="flex flex-wrap items-start justify-between gap-3"><div><div class="text-xs text-slate-500">🧭 Rute ke</div><div class="font-bold text-white">' + esc(s.nama) + '</div>' + main + '</div>' + act + '</div>';
            document.getElementById('btn-clear-route').addEventListener('click', () => {
                activeId = null; clearRoute();
                infoEl.textContent = 'Rute dihapus. Pilih tombol Rute pada stasiun lain.';
            });
        }

        async function drawRoute(s) {
            if (!me) { locate(() => drawRoute(s)); return; }
            activeId = s.id;
            clearRoute();
            infoEl.textContent = 'Menghitung rute ke ' + s.nama + '…';
            try {
                const res = await fetch(OSRM + '/' + me.lng + ',' + me.lat + ';' + s.lng + ',' + s.lat + '?overview=full&geometries=geojson');
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (data.code !== 'Ok' || !data.routes || !data.routes.length) throw new Error(data.code || 'no route');
                if (activeId !== s.id) return;
                const r = data.routes[0];
                s.route = { distance: r.distance, duration: r.duration };
                routeLayer = L.geoJSON(r.geometry, { style: { color: '#22d3ee', weight: 6, opacity: 0.9 } }).addTo(map);
                map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                updateCards();
                showInfo(s, true);
            } catch (err) {
                if (activeId !== s.id) return;
                routeLayer = L.polyline([[me.lat, me.lng], [s.lat, s.lng]], { color: '#fbbf24', weight: 4, dashArray: '8 8' }).addTo(map);
                map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                showInfo(s, false);
            }
        }

        // ---------- Lokasi saya ----------
        function onLocated(drawNearest) {
            if (meMarker) map.removeLayer(meMarker);
            if (meCircle) map.removeLayer(meCircle);
            meMarker = L.marker([me.lat, me.lng], { icon: meIcon, zIndexOffset: 1000 }).addTo(map)
                .bindPopup('<strong>📍 Lokasi Anda</strong><div style="font-size:.8rem;color:#94a3b8">Akurasi ± ' + fmtDist(me.acc) + '</div>');
            meCircle = L.circle([me.lat, me.lng], { radius: me.acc, color: '#38bdf8', weight: 1, fillOpacity: 0.1 }).addTo(map);

            stations.forEach(s => { s.distance = haversine(me.lat, me.lng, s.lat, s.lng); s.route = null; });
            const open = stations.filter(s => s.open);
            const nearest = (open.length ? open : stations).slice().sort((a, b) => a.distance - b.distance)[0];
            nearestId = nearest ? nearest.id : null;
            stations.forEach(s => s.marker.setIcon(iconFor(s, s.id === nearestId)));
            updateCards();

            let msg = 'Lokasi ditemukan (akurasi ± ' + fmtDist(me.acc) + '). Daftar diurutkan dari yang terdekat.';
            if (me.acc > 1000) msg += ' Akurasi rendah: perangkat ini mungkin tidak punya GPS.';
            setStatus(msg, me.acc > 1000);

            if (drawNearest && nearest) drawRoute(nearest);
            else if (!nearest) map.setView([me.lat, me.lng], 15);
        }

        function locate(afterFix) {
            if (!('geolocation' in navigator)) { setStatus('Browser ini tidak mendukung lokasi.', true); return; }
            if (!window.isSecureContext) { setStatus('Lokasi hanya bisa dipakai lewat HTTPS atau localhost.', true); return; }
            locateBtn.disabled = true;
            setStatus('Mencari lokasi Anda…');
            navigator.geolocation.getCurrentPosition(pos => {
                locateBtn.disabled = false;
                me = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
                onLocated(!afterFix);
                if (afterFix) afterFix();
            }, err => {
                locateBtn.disabled = false;
                setStatus({
                    1: 'Izin lokasi ditolak. Klik ikon gembok di address bar, izinkan "Lokasi", lalu klik Lokasi saya lagi.',
                    2: 'Lokasi tidak dapat ditentukan. Pastikan GPS atau Wi-Fi aktif.',
                    3: 'Waktu pencarian lokasi habis. Coba lagi.'
                }[err.code] || 'Gagal mengambil lokasi.', true);
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
        }

        // ---------- Event ----------
        locateBtn.addEventListener('click', () => locate());

        document.addEventListener('click', e => {
            const btn = e.target.closest('[data-route-station]');
            if (btn) {
                const s = byId[btn.getAttribute('data-route-station')];
                if (!s) return;
                map.closePopup();
                mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                drawRoute(s);
                return;
            }
            const card = e.target.closest('[data-station-card]');
            if (card) {
                const s = byId[card.dataset.stationCard];
                if (s) { map.flyTo([s.lat, s.lng], 16, { duration: 0.8 }); s.marker.openPopup(); }
            }
        });

        applyFilter(true);
        locate();
    })();
    </script>
    @endpush
</x-app-layout>