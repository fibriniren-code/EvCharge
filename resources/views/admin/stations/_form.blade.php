{{-- Variabel: $station (Location|null), $action, $method, $withTariff (bool) --}}
@php
    $s = $station ?? null;
    $fasilitas = old('fasilitas', $s ? implode(', ', $s->fasilitas_list) : '');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6"
      x-data="{ lat: '{{ old('latitude', $s->latitude ?? -7.7956) }}', lng: '{{ old('longitude', $s->longitude ?? 110.3695) }}' }">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <section class="card reveal p-6" style="--i:1">
        <h2 class="font-bold">Informasi stasiun</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2"><x-input-label for="nama_lokasi" value="Nama stasiun" /><x-text-input id="nama_lokasi" name="nama_lokasi" :value="old('nama_lokasi', $s->nama_lokasi ?? '')" required placeholder="SPKLU Malioboro" /><x-input-error :messages="$errors->get('nama_lokasi')" /></div>
            <div class="sm:col-span-2"><x-input-label for="alamat" value="Alamat" /><x-text-input id="alamat" name="alamat" :value="old('alamat', $s->alamat ?? '')" required /><x-input-error :messages="$errors->get('alamat')" /></div>
            <div><x-input-label for="detail_area" value="Detail area" /><x-text-input id="detail_area" name="detail_area" :value="old('detail_area', $s->detail_area ?? '')" placeholder="Basement lantai 1" /><x-input-error :messages="$errors->get('detail_area')" /></div>
            <div><x-input-label for="jam_operasional" value="Jam operasional" /><x-text-input id="jam_operasional" name="jam_operasional" :value="old('jam_operasional', $s->jam_operasional ?? '24 Jam')" /><x-input-error :messages="$errors->get('jam_operasional')" /></div>
            <div class="sm:col-span-2"><x-input-label for="fasilitas" value="Fasilitas (pisahkan dengan koma)" /><x-text-input id="fasilitas" name="fasilitas" :value="$fasilitas" placeholder="Toilet, Wifi, Cafe, Musholla" /><x-input-error :messages="$errors->get('fasilitas')" /></div>
            <div><x-input-label for="status" value="Status" /><select id="status" name="status" class="input"><option value="aktif" @selected(old('status', $s->status ?? 'aktif') === 'aktif')>Beroperasi</option><option value="tutup" @selected(old('status', $s->status ?? 'aktif') === 'tutup')>Ditutup sementara</option></select><x-input-error :messages="$errors->get('status')" /></div>
        </div>
    </section>

    <section class="card reveal p-6" style="--i:2">
        <div class="flex items-center justify-between"><h2 class="font-bold">Lokasi di peta</h2><span class="text-xs text-slate-500">Klik peta untuk memilih titik</span></div>
        <div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div><x-input-label for="latitude" value="Latitude" /><input id="latitude" name="latitude" x-model="lat" required class="input"><x-input-error :messages="$errors->get('latitude')" /></div>
            <div><x-input-label for="longitude" value="Longitude" /><input id="longitude" name="longitude" x-model="lng" required class="input"><x-input-error :messages="$errors->get('longitude')" /></div>
        </div>
        <div id="pick-map" class="mt-4 h-72 overflow-hidden rounded-2xl border border-white/10"></div>
    </section>

    @if ($withTariff)
        <section class="card reveal p-6" style="--i:3">
            <h2 class="font-bold">Tarif awal</h2>
            <p class="mt-1 text-xs text-slate-500">Dapat diubah kapan saja di menu Tarif &amp; Biaya.</p>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div><x-input-label for="harga_per_kwh" value="Harga per kWh (Rp)" /><x-text-input id="harga_per_kwh" type="number" name="harga_per_kwh" :value="old('harga_per_kwh', 2466)" required /><x-input-error :messages="$errors->get('harga_per_kwh')" /></div>
                <div><x-input-label for="biaya_layanan" value="Biaya layanan (Rp)" /><x-text-input id="biaya_layanan" type="number" name="biaya_layanan" :value="old('biaya_layanan', 0)" /></div>
                <div><x-input-label for="biaya_minimum" value="Biaya minimum (Rp)" /><x-text-input id="biaya_minimum" type="number" name="biaya_minimum" :value="old('biaya_minimum', 0)" /></div>
                <div><x-input-label for="overstay_fee_per_menit" value="Denda overstay (Rp/menit)" /><x-text-input id="overstay_fee_per_menit" type="number" name="overstay_fee_per_menit" :value="old('overstay_fee_per_menit', 0)" /></div>
            </div>
        </section>
    @endif

    <div class="flex justify-end gap-3"><a href="{{ route('admin.stations.index') }}" class="btn-secondary">Batal</a><button type="submit" class="btn-primary btn-lg">{{ $s ? 'Simpan perubahan' : 'Buat stasiun' }}</button></div>

    @push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('pick-map');
            if (!el || typeof L === 'undefined') return;
            const latEl = document.getElementById('latitude'), lngEl = document.getElementById('longitude');
            const start = [parseFloat(latEl.value) || -7.7956, parseFloat(lngEl.value) || 110.3695];
            const map = L.map(el).setView(start, 14);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { subdomains: 'abcd', maxZoom: 19, attribution: '&copy; OpenStreetMap &copy; CARTO' }).addTo(map);
            const marker = L.marker(start, { draggable: true, icon: L.divIcon({ className: 'ev-pin-wrap', html: '<div class="ev-pin"><span>⚡</span></div>', iconSize: [36, 36], iconAnchor: [18, 36] }) }).addTo(map);
            const set = (ll) => { latEl.value = ll.lat.toFixed(6); lngEl.value = ll.lng.toFixed(6); latEl.dispatchEvent(new Event('input')); lngEl.dispatchEvent(new Event('input')); };
            map.on('click', (e) => { marker.setLatLng(e.latlng); set(e.latlng); });
            marker.on('dragend', () => set(marker.getLatLng()));
            [latEl, lngEl].forEach(i => i.addEventListener('change', () => { const p = [parseFloat(latEl.value), parseFloat(lngEl.value)]; if (!p.some(isNaN)) { marker.setLatLng(p); map.panTo(p); } }));
        });
    </script>
    @endpush
</form>