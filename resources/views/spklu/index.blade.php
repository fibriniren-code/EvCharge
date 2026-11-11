<x-app-layout>
    <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem 1.5rem;">
        
        <!-- Header Halaman -->
        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0; tracking-tight: -0.025em;">
                Daftar SPKLU & Stasiun Pengisian
            </h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.25rem; margin-bottom: 0;">
                Cari stasiun pengisian kendaraan listrik terdekat, cek ketersediaan slot charger real-time, dan lakukan reservasi.
            </p>
        </div>

        <!-- Filter / Form Pencarian Kota -->
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);">
            <form action="{{ route('spklu.index') }}" method="GET">
                <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                    CARI SPKLU / KOTA
                </label>
                
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Ketik nama kota (misal: Yogyakarta, Jakarta, Bandung...)" 
                           style="flex: 1; min-width: 250px; background-color: #0F172A; border: 1px solid #334155; color: #FFFFFF; padding: 0.65rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; outline: none;"
                           required>
                    
                    <button type="submit" style="background-color: #2563EB; color: #FFFFFF; font-weight: 700; border: none; padding: 0.65rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">
                        🔍 Cari di Peta
                    </button>
                    
                    <a href="{{ route('spklu.index') }}" style="background-color: #334155; color: #CBD5E1; font-weight: 600; text-decoration: none; padding: 0.65rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; display: flex; align-items: center;">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        @if(!request('search'))
            <!-- Tampilan Awal / Empty State Sebelum Pencarian -->
            <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 4rem 2rem; text-align: center; color: #94A3B8;">
                <div style="font-size: 4rem; line-height: 1; margin-bottom: 1rem; opacity: 0.5;">🔍</div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.5rem;">Silakan Masukkan Nama Kota</h3>
                <p style="font-size: 0.85rem; color: #94A3B8; max-width: 28rem; margin: 0 auto;">
                    Ketik kota tujuan Anda pada kolom di atas lalu klik "Cari di Peta" untuk menampilkan lokasi SPKLU terdekat.
                </p>
            </div>
        @else
            @php
                $mapStations = $locations->getCollection()->map(fn ($l) => [
                    'id'       => $l->id_location,
                    'nama'     => $l->nama_lokasi,
                    'alamat'   => $l->alamat,
                    'lat'      => (float) $l->latitude,
                    'lng'      => (float) $l->longitude,
                    'tersedia' => $l->chargers->where('status', 'available')->count(),
                    'total'    => $l->chargers->count(),
                ])->values();
            @endphp

            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
            <style>
                #spklu-map { position: relative; z-index: 0; }
                .ev-pin-wrap, .ev-me-wrap { background: transparent; border: none; }
                .ev-pin { width: 34px; height: 34px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); background: #10B981; border: 3px solid #FFFFFF; box-shadow: 0 2px 8px rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; }
                .ev-pin.nearest { background: #F59E0B; }
                .ev-pin span { transform: rotate(45deg); font-size: 15px; line-height: 1; }
                .ev-me { width: 18px; height: 18px; border-radius: 50%; background: #2563EB; border: 3px solid #FFFFFF; box-shadow: 0 0 0 0 rgba(37,99,235,0.6); animation: ev-pulse 2s infinite; }
                @@keyframes ev-pulse { 0% { box-shadow: 0 0 0 0 rgba(37,99,235,0.6); } 70% { box-shadow: 0 0 0 14px rgba(37,99,235,0); } 100% { box-shadow: 0 0 0 0 rgba(37,99,235,0); } }
            </style>

            <!-- Peta SPKLU: lokasi saya, jarak, dan rute -->
            <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.25rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: #60A5FA; display: flex; align-items: center; gap: 0.35rem;">
                        📍 Peta SPKLU: <span style="color: #FFFFFF;">{{ request('search') }}</span>
                    </span>

                    <button type="button" id="btn-locate"
                            style="background-color: #2563EB; color: #FFFFFF; font-weight: 700; border: none; padding: 0.45rem 0.9rem; border-radius: 0.6rem; font-size: 0.78rem; cursor: pointer;">
                        📍 Lokasi Saya
                    </button>
                </div>

                <div id="locate-status" style="font-size: 0.75rem; color: #94A3B8; margin-bottom: 0.6rem;">Meminta izin lokasi…</div>

                <div id="spklu-map" style="height: 420px; border-radius: 0.75rem; overflow: hidden; border: 1px solid #334155; background-color: #0F172A;"></div>

                <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.7rem; color: #94A3B8; margin-top: 0.6rem;">
                    <span>🔵 Posisi Anda</span>
                    <span>🟡 Stasiun terdekat</span>
                    <span>🟢 Stasiun lain</span>
                </div>

                <div id="route-info" style="margin-top: 0.75rem; background-color: #0F172A; border: 1px solid #334155; border-radius: 0.75rem; padding: 0.85rem 1rem; font-size: 0.8rem; color: #CBD5E1;">
                    Klik <strong>Lokasi Saya</strong> untuk melihat posisi Anda, jarak, dan rute ke stasiun terdekat.
                </div>
            </div>

            <!-- Banner Quick Action Scan QR Tepat Setelah Peta -->
            <div style="background-color: #1E293B; border: 1px solid #10B981; border-radius: 1.25rem; padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 4px 12px rgba(16,185,129,0.15);">
                <div>
                    <h4 style="font-size: 1rem; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span>⚡</span> Sudah Sampai di Lokasi Mesin Charger?
                    </h4>
                    <p style="font-size: 0.8rem; color: #94A3B8; margin: 0.25rem 0 0 0;">
                        Pindai kode QR pada fisik mesin SPKLU untuk langsung mengaktifkan sesi pengisian daya.
                    </p>
                </div>
                <a href="{{ route('driver.scan') }}" style="background-color: #10B981; color: #FFFFFF; font-weight: 700; padding: 0.65rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                    📷 Scan QR Mesin Charger
                </a>
            </div>

            <!-- List Detail Hasil SPKLU -->
            <div style="margin-bottom: 1rem;">
                <h3 style="font-size: 0.875rem; font-weight: 800; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                    DETAIL SPKLU DIKEMBANGKAN
                </h3>

                <div id="station-list" style="display: flex; flex-direction: column; gap: 1rem;">
                    @forelse($locations as $location)
                        <div data-station-card="{{ $location->id_location }}" style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);">
                            <div style="margin-bottom: 1rem; border-bottom: 1px solid #334155; padding-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; flex-wrap: wrap;">
                                <div>
                                    <h4 style="font-size: 1.125rem; font-weight: 800; color: #FFFFFF; margin: 0 0 0.25rem 0;">
                                        <a href="{{ route('spklu.show', $location->id_location) }}" style="color: inherit; text-decoration: none;">
                                            {{ $location->nama_lokasi }}
                                        </a>
                                    </h4>
                                    <p style="font-size: 0.8rem; color: #94A3B8; margin: 0;">
                                        📍 {{ $location->alamat }}
                                    </p>
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <span data-distance-for="{{ $location->id_location }}" style="display: none; align-items: center; background-color: rgba(96,165,250,0.15); color: #93C5FD; border: 1px solid rgba(96,165,250,0.3); font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px;"></span>
                                    <span data-route-for="{{ $location->id_location }}" style="display: none; align-items: center; background-color: rgba(245,158,11,0.15); color: #FCD34D; border: 1px solid rgba(245,158,11,0.3); font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px;"></span>
                                    <button type="button" data-route-station="{{ $location->id_location }}"
                                            style="background-color: #334155; color: #FFFFFF; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 0.5rem; border: none; cursor: pointer;">
                                        🧭 Rute
                                    </button>
                                </div>
                            </div>

                            <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #60A5FA; text-transform: uppercase; tracking-wider: 0.05em; margin-bottom: 0.75rem;">
                                SLOT CHARGER TERSEDIA:
                            </span>

                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @forelse($location->chargers as $charger)
                                    <div style="background-color: #0F172A; border: 1px solid #2A3859; border-radius: 0.75rem; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                                        <div>
                                            <span style="display: block; font-size: 0.875rem; font-weight: 700; color: #FFFFFF;">
                                                Charger #{{ $charger->id_charger }} ({{ $charger->charger_type }})
                                            </span>
                                            <span style="font-size: 0.75rem; color: #94A3B8;">
                                                Daya: {{ (float) $charger->power_kw }} kW
                                            </span>
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            @if(($charger->status ?? 'available') === 'available')
                                                <span style="color: #34D399; font-size: 0.75rem; font-weight: 700; background-color: rgba(52,211,153,0.15); border: 1px solid rgba(52,211,153,0.3); padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                                    ● Tersedia
                                                </span>
                                                
                                                <!-- Tombol Scan QR Langsung pada Slot Charger -->
                                                <a href="{{ route('driver.scan') }}" 
                                                   style="background-color: #10B981; color: #FFFFFF; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 0.5rem; text-decoration: none;">
                                                    📷 Scan QR
                                                </a>

                                                <a href="{{ route('reservations.create', ['location' => $location->id_location, 'charger' => $charger->id_charger]) }}" 
                                                   style="background-color: #2563EB; color: #FFFFFF; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 1rem; border-radius: 0.5rem; text-decoration: none; box-shadow: 0 2px 8px rgba(37,99,235,0.3);">
                                                    Pesan
                                                </a>
                                            @else
                                                <span style="color: #F87171; font-size: 0.75rem; font-weight: 700; background-color: rgba(248,113,113,0.15); border: 1px solid rgba(248,113,113,0.3); padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                                    ● Terpakai / Disewa
                                                </span>
                                                <button disabled style="background-color: #334155; color: #64748B; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 1rem; border-radius: 0.5rem; border: none; cursor: not-allowed;">
                                                    Tidak Tersedia
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p style="font-size: 0.8rem; color: #64748B; margin: 0;">Tidak ada slot charger terdaftar di stasiun ini.</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 2rem; text-align: center; color: #94A3B8;">
                            Tidak ditemukan lokasi SPKLU untuk kata kunci <strong>"{{ request('search') }}"</strong>.
                        </div>
                    @endforelse
                </div>
            </div>

            @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
            (function () {
                const stations = @json($mapStations).filter(s => Number.isFinite(s.lat) && Number.isFinite(s.lng));
                const mapEl = document.getElementById('spklu-map');
                if (!mapEl) return;

                if (typeof L === 'undefined') {
                    mapEl.innerHTML = '<div style="padding:1.5rem;color:#FCA5A5;font-size:0.85rem;">Peta gagal dimuat. Periksa koneksi internet Anda.</div>';
                    return;
                }

                const OSRM_URL = 'https://router.project-osrm.org/route/v1/driving';
                const statusEl = document.getElementById('locate-status');
                const infoEl = document.getElementById('route-info');
                const locateBtn = document.getElementById('btn-locate');
                const byId = {};
                stations.forEach(s => { byId[s.id] = s; });

                // ---------- Helper ----------
                function esc(v) {
                    return String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                }

                // Jarak garis lurus (meter) dengan Haversine
                function haversine(lat1, lng1, lat2, lng2) {
                    const R = 6371000, rad = Math.PI / 180;
                    const dLat = (lat2 - lat1) * rad, dLng = (lng2 - lng1) * rad;
                    const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * rad) * Math.cos(lat2 * rad) * Math.sin(dLng / 2) ** 2;
                    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                }

                // < 1 km tampil dalam meter, selebihnya dalam km
                function fmtDist(m) {
                    if (Math.round(m) < 1000) return Math.round(m) + ' m';
                    const digits = m < 10000 ? 1 : 0;
                    return (m / 1000).toLocaleString('id-ID', { minimumFractionDigits: digits, maximumFractionDigits: digits }) + ' km';
                }

                function fmtDur(sec) {
                    const min = Math.round(sec / 60);
                    if (min < 1) return '< 1 menit';
                    if (min < 60) return min + ' menit';
                    const h = Math.floor(min / 60), m = min % 60;
                    return h + ' jam' + (m ? ' ' + m + ' menit' : '');
                }

                function setStatus(msg, isError) {
                    statusEl.textContent = msg;
                    statusEl.style.color = isError ? '#FCA5A5' : '#94A3B8';
                }

                // ---------- Peta & ikon ----------
                const map = L.map(mapEl, { scrollWheelZoom: false }).setView([-2.5, 118], 5);
                map.on('focus', () => map.scrollWheelZoom.enable());
                map.on('blur', () => map.scrollWheelZoom.disable());

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                const pinIcon = (extra) => L.divIcon({
                    className: 'ev-pin-wrap',
                    html: '<div class="ev-pin ' + extra + '"><span>⚡</span></div>',
                    iconSize: [34, 34], iconAnchor: [17, 34], popupAnchor: [0, -30]
                });
                const stationIcon = pinIcon('');
                const nearestIcon = pinIcon('nearest');
                const meIcon = L.divIcon({ className: 'ev-me-wrap', html: '<div class="ev-me"></div>', iconSize: [18, 18], iconAnchor: [9, 9] });

                let me = null, meMarker = null, meCircle = null, routeLayer = null, activeId = null;

                function gmapsLink(s) {
                    return me
                        ? 'https://www.google.com/maps/dir/?api=1&origin=' + me.lat + ',' + me.lng + '&destination=' + s.lat + ',' + s.lng + '&travelmode=driving'
                        : 'https://www.google.com/maps/search/?api=1&query=' + s.lat + ',' + s.lng;
                }

                function popupHtml(s) {
                    const lurus = s.distance != null
                        ? '<div style="margin-top:.35rem;font-weight:700;color:#2563EB;">📏 ' + fmtDist(s.distance) + ' (garis lurus)</div>' : '';
                    const jalan = s.route
                        ? '<div style="font-weight:700;color:#B45309;">🛣️ ' + fmtDist(s.route.distance) + ' · ± ' + fmtDur(s.route.duration) + ' lewat jalan</div>' : '';
                    return '<div style="min-width:210px;">'
                        + '<strong>' + esc(s.nama) + '</strong>'
                        + '<div style="font-size:.8rem;color:#475569;">' + esc(s.alamat) + '</div>'
                        + '<div style="font-size:.8rem;margin-top:.25rem;">⚡ Charger tersedia: ' + s.tersedia + '/' + s.total + '</div>'
                        + lurus + jalan
                        + '<div style="display:flex;gap:.4rem;margin-top:.6rem;flex-wrap:wrap;">'
                        + '<button type="button" data-route-station="' + s.id + '" style="background:#2563EB;color:#fff;border:none;border-radius:.4rem;padding:.3rem .6rem;font-size:.75rem;font-weight:700;cursor:pointer;">🧭 Rute ke sini</button>'
                        + '<a href="' + gmapsLink(s) + '" target="_blank" rel="noopener" style="background:#E2E8F0;color:#0F172A;border-radius:.4rem;padding:.3rem .6rem;font-size:.75rem;font-weight:700;text-decoration:none;">Google Maps ↗</a>'
                        + '</div></div>';
                }

                stations.forEach(s => {
                    s.marker = L.marker([s.lat, s.lng], { icon: stationIcon, title: s.nama })
                        .addTo(map)
                        .bindPopup(() => popupHtml(s));
                });

                if (stations.length) {
                    map.fitBounds(L.latLngBounds(stations.map(s => [s.lat, s.lng])), { padding: [40, 40], maxZoom: 15 });
                }

                // ---------- Jarak di kartu daftar ----------
                function updateCards() {
                    stations.forEach(s => {
                        document.querySelectorAll('[data-distance-for="' + s.id + '"]').forEach(el => {
                            el.textContent = '📏 ' + fmtDist(s.distance);
                            el.style.display = 'inline-flex';
                        });
                        document.querySelectorAll('[data-route-for="' + s.id + '"]').forEach(el => {
                            if (s.route) {
                                el.textContent = '🛣️ ' + fmtDist(s.route.distance) + ' · ' + fmtDur(s.route.duration);
                                el.style.display = 'inline-flex';
                            } else {
                                el.style.display = 'none';
                            }
                        });
                    });

                    // Urutkan kartu dari yang terdekat
                    const list = document.getElementById('station-list');
                    if (!list) return;
                    Array.from(list.querySelectorAll('[data-station-card]'))
                        .sort((a, b) => byId[a.dataset.stationCard].distance - byId[b.dataset.stationCard].distance)
                        .forEach(card => list.appendChild(card));
                }

                // ---------- Rute ----------
                function clearRoute() {
                    if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
                }

                function showInfo(s, viaRoad) {
                    let html = '<div style="display:flex;justify-content:space-between;gap:.75rem;flex-wrap:wrap;align-items:flex-start;">'
                        + '<div>'
                        + '<div style="font-size:.72rem;color:#94A3B8;">🧭 Rute ke</div>'
                        + '<div style="font-size:.95rem;font-weight:800;color:#FFFFFF;">' + esc(s.nama) + '</div>';

                    if (viaRoad) {
                        html += '<div style="margin-top:.4rem;font-size:1.25rem;font-weight:800;color:#60A5FA;">' + fmtDist(s.route.distance)
                            + ' <span style="font-size:.8rem;font-weight:600;color:#CBD5E1;">· ± ' + fmtDur(s.route.duration) + ' berkendara</span></div>'
                            + '<div style="font-size:.72rem;color:#94A3B8;">Jarak garis lurus: ' + fmtDist(s.distance) + '</div>';
                    } else {
                        html += '<div style="margin-top:.4rem;font-size:1.25rem;font-weight:800;color:#FCD34D;">' + fmtDist(s.distance)
                            + ' <span style="font-size:.8rem;font-weight:600;color:#CBD5E1;">· garis lurus</span></div>'
                            + '<div style="font-size:.72rem;color:#FCA5A5;">Rute jalan tidak dapat dimuat saat ini, ditampilkan garis lurus putus-putus.</div>';
                    }

                    html += '</div><div style="display:flex;gap:.5rem;flex-wrap:wrap;">'
                        + '<a href="' + gmapsLink(s) + '" target="_blank" rel="noopener" style="background:#2563EB;color:#fff;border-radius:.5rem;padding:.4rem .8rem;font-size:.75rem;font-weight:700;text-decoration:none;">Buka navigasi Google Maps ↗</a>'
                        + '<button type="button" id="btn-clear-route" style="background:#334155;color:#fff;border:none;border-radius:.5rem;padding:.4rem .8rem;font-size:.75rem;font-weight:700;cursor:pointer;">✕ Hapus rute</button>'
                        + '</div></div>';

                    infoEl.innerHTML = html;
                    document.getElementById('btn-clear-route').addEventListener('click', () => {
                        activeId = null;
                        clearRoute();
                        infoEl.textContent = 'Rute dihapus. Pilih tombol Rute pada stasiun lain.';
                    });
                }

                async function drawRoute(s) {
                    if (!me) { locate(() => drawRoute(s)); return; }

                    activeId = s.id;
                    clearRoute();
                    infoEl.textContent = 'Menghitung rute ke ' + s.nama + '…';

                    try {
                        const url = OSRM_URL + '/' + me.lng + ',' + me.lat + ';' + s.lng + ',' + s.lat + '?overview=full&geometries=geojson';
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const data = await res.json();
                        if (data.code !== 'Ok' || !data.routes || !data.routes.length) throw new Error(data.code || 'no route');
                        if (activeId !== s.id) return;

                        const r = data.routes[0];
                        s.route = { distance: r.distance, duration: r.duration };
                        routeLayer = L.geoJSON(r.geometry, { style: { color: '#2563EB', weight: 6, opacity: 0.85 } }).addTo(map);
                        map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                        updateCards();
                        showInfo(s, true);
                    } catch (err) {
                        if (activeId !== s.id) return;
                        routeLayer = L.polyline([[me.lat, me.lng], [s.lat, s.lng]], { color: '#F59E0B', weight: 4, dashArray: '8 8' }).addTo(map);
                        map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                        showInfo(s, false);
                    }
                }

                // ---------- Lokasi saya ----------
                function onLocated(drawNearest) {
                    if (meMarker) map.removeLayer(meMarker);
                    if (meCircle) map.removeLayer(meCircle);

                    meMarker = L.marker([me.lat, me.lng], { icon: meIcon, zIndexOffset: 1000 })
                        .addTo(map)
                        .bindPopup('<strong>📍 Lokasi Anda</strong><div style="font-size:.8rem;">Akurasi ± ' + fmtDist(me.acc) + '</div>');
                    meCircle = L.circle([me.lat, me.lng], { radius: me.acc, color: '#2563EB', weight: 1, fillOpacity: 0.12 }).addTo(map);

                    stations.forEach(s => { s.distance = haversine(me.lat, me.lng, s.lat, s.lng); s.route = null; });
                    const nearest = stations.slice().sort((a, b) => a.distance - b.distance)[0];
                    stations.forEach(s => s.marker.setIcon(s === nearest ? nearestIcon : stationIcon));
                    updateCards();

                    let msg = 'Lokasi ditemukan (akurasi ± ' + fmtDist(me.acc) + ').';
                    if (me.acc > 1000) msg += ' Akurasi rendah: perangkat ini mungkin tidak punya GPS, posisi bisa meleset.';
                    setStatus(msg, me.acc > 1000);

                    if (drawNearest && nearest) {
                        drawRoute(nearest);          // otomatis: rute ke stasiun terdekat
                    } else if (!nearest) {
                        map.setView([me.lat, me.lng], 15);
                    }
                }

                function locate(afterFix) {
                    if (!('geolocation' in navigator)) { setStatus('Browser ini tidak mendukung lokasi.', true); return; }
                    if (!window.isSecureContext) { setStatus('Lokasi hanya bisa dipakai lewat HTTPS atau localhost.', true); return; }

                    locateBtn.disabled = true;
                    setStatus('Mencari lokasi Anda…');

                    navigator.geolocation.getCurrentPosition(function (pos) {
                        locateBtn.disabled = false;
                        me = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
                        onLocated(!afterFix);        // jika dipicu tombol Rute, rute ke stasiun pilihan pengguna
                        if (afterFix) afterFix();
                    }, function (err) {
                        locateBtn.disabled = false;
                        const pesan = {
                            1: 'Izin lokasi ditolak. Klik ikon gembok di address bar, izinkan "Lokasi", lalu klik Lokasi Saya lagi.',
                            2: 'Lokasi tidak dapat ditentukan. Pastikan GPS atau Wi-Fi aktif.',
                            3: 'Waktu pencarian lokasi habis. Coba lagi.'
                        }[err.code] || 'Gagal mengambil lokasi.';
                        setStatus(pesan, true);
                    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
                }

                // ---------- Event ----------
                locateBtn.addEventListener('click', () => locate());

                document.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-route-station]');
                    if (!btn) return;
                    const s = byId[btn.getAttribute('data-route-station')];
                    if (!s) return;
                    map.closePopup();
                    mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    drawRoute(s);
                });

                locate();
            })();
            </script>
            @endpush
        @endif

    </div>
</x-app-layout>