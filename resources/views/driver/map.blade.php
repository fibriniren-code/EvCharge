<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar SPKLU & Stasiun Pengisian') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Form Pencarian (Filter) -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <form id="search-form" onsubmit="handleSearch(event)">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Cari SPKLU / Kota</label>
                <div class="flex gap-4">
                    <input type="text" id="search-input" name="search" placeholder="Contoh: SPKLU Voltron, Yogyakarta" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2 rounded-md text-sm font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari di Peta
                    </button>
                    <button type="button" onclick="resetSearch()" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Reset</button>
                </div>
            </form>
        </div>

        <!-- Container Peta Leaflet -->
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Peta SPKLU Terdekat
            </h3>
            <div id="map" style="height: 400px; width: 100%;" class="rounded-lg border z-0"></div>
        </div>

        <!-- Daftar Stasiun Charging Tersedia -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <h3 class="font-bold text-gray-800 text-lg mb-4">Daftar Stasiun Charging Tersedia</h3>
            
            <!-- Grid Card SPKLU -->
            <div id="station-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="col-span-full text-center py-6 text-gray-500">
                    Memuat data lokasi SPKLU...
                </div>
            </div>
        </div>

    </div>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let map, userMarker;
        let markersGroup = L.layerGroup();
        let userLat = null, userLng = null;

        function initMap() {
            // Default center Yogyakarta
            map = L.map('map').setView([-7.797068, 110.370529], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            markersGroup.addTo(map);

            getUserLocation();
        }

        function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        userLat = position.coords.latitude;
                        userLng = position.coords.longitude;

                        map.setView([userLat, userLng], 12);

                        if (userMarker) map.removeLayer(userMarker);
                        userMarker = L.marker([userLat, userLng], {
                            title: "Lokasi Anda"
                        }).addTo(map).bindPopup("<b>Lokasi Anda Sekarang</b>").openPopup();

                        fetchNearbySPKLU(userLat, userLng);
                    },
                    (error) => {
                        console.warn("Geolocation tidak diizinkan. Memuat SPKLU default.");
                        fetchNearbySPKLU();
                    }
                );
            } else {
                fetchNearbySPKLU();
            }
        }

        function fetchNearbySPKLU(lat = null, lng = null, search = '') {
            let url = new URL('/api/locations', window.location.origin);
            
            if (lat && lng) {
                url.searchParams.append('latitude', lat);
                url.searchParams.append('longitude', lng);
            }
            if (search) {
                url.searchParams.append('search', search);
            }

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        renderMarkers(res.data);
                        renderStationList(res.data);
                    }
                })
                .catch(err => {
                    console.error("Gagal mengambil data SPKLU:", err);
                    document.getElementById('station-list').innerHTML = `
                        <div class="col-span-full text-center text-red-500 py-4">
                            Gagal memuat data dari database. Pastikan koneksi backend berjalan lancar.
                        </div>
                    `;
                });
        }

        function renderMarkers(locations) {
            markersGroup.clearLayers();

            locations.forEach(loc => {
                let distanceText = loc.distance ? `<br><b>Jarak:</b> ${parseFloat(loc.distance).toFixed(2)} km` : '';
                
                // Menggunakan loc.nama_lokasi & loc.alamat
                L.marker([loc.latitude, loc.longitude])
                    .addTo(markersGroup)
                    .bindPopup(`
                        <div class="p-1 min-w-[180px]">
                            <b class="text-sm">${loc.nama_lokasi}</b>
                            <p class="text-xs text-gray-600 mt-1">${loc.alamat}</p>
                            ${distanceText}
                        </div>
                    `);
            });
        }

        function renderStationList(locations) {
            const listContainer = document.getElementById('station-list');
            listContainer.innerHTML = '';

            if (!locations || locations.length === 0) {
                listContainer.innerHTML = `
                    <div class="col-span-full p-6 text-center text-gray-500">
                        Tidak ada lokasi SPKLU yang ditemukan di database.
                    </div>
                `;
                return;
            }

            locations.forEach(loc => {
                let distanceBadge = loc.distance 
                    ? `<span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-semibold">${parseFloat(loc.distance).toFixed(2)} km</span>` 
                    : '';

                let chargersHtml = '';
                if (loc.chargers && loc.chargers.length > 0) {
                    loc.chargers.forEach(charger => {
                        // Menggunakan loc.id_location
                        let reserveUrl = `/location/${loc.id_location}/charger/${charger.id}/reserve`;
                        chargersHtml += `
                            <div class="flex items-center justify-between p-2 mb-2 bg-gray-50 rounded border">
                                <div>
                                    <p class="text-xs font-bold text-gray-800">${charger.type}</p>
                                    <p class="text-[11px] text-gray-500">${charger.power_kw} kW</p>
                                </div>
                                <a href="${reserveUrl}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition">
                                    Reservasi Slot
                                </a>
                            </div>
                        `;
                    });
                } else {
                    chargersHtml = `<p class="text-xs text-gray-500 italic">Slot charger belum tersedia.</p>`;
                }

                // Menggunakan loc.nama_lokasi & loc.alamat
                let card = `
                    <div class="bg-white rounded-lg border p-4 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h4 class="font-bold text-gray-900 text-sm">${loc.nama_lokasi}</h4>
                                ${distanceBadge}
                            </div>
                            <p class="text-xs text-gray-600 mb-3">${loc.alamat}</p>
                            <hr class="mb-3">
                            <p class="text-xs font-bold text-gray-700 mb-2">Pilih Slot Charger:</p>
                            ${chargersHtml}
                        </div>
                    </div>
                `;

                listContainer.innerHTML += card;
            });
        }

        function handleSearch(e) {
            e.preventDefault();
            const searchValue = document.getElementById('search-input').value;
            fetchNearbySPKLU(userLat, userLng, searchValue);
        }

        function resetSearch() {
            document.getElementById('search-input').value = '';
            fetchNearbySPKLU(userLat, userLng);
        }

        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</x-app-layout>