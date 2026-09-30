```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Peta SPKLU - EVCharge</title>

    {{-- Leaflet CSS --}}
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fa;
        }

        .header {
            background: #111827;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            background: #374151;
            padding: 8px 15px;
            border-radius: 6px;
        }

        #map {
            width: 100%;
            height: calc(100vh - 73px);
        }

        .popup-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .free {
            color: #16a34a;
            font-weight: bold;
        }

        .busy {
            color: #dc2626;
            font-weight: bold;
        }

        .unknown {
            color: #6b7280;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>📍 Peta SPKLU EVCharge</h2>

        <a href="{{ route('spklu.index') }}" class="back-btn">
            ← Daftar SPKLU
        </a>
    </div>

    <div id="map"></div>

    {{-- Leaflet JS --}}
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
    </script>

    <script>
        const locations = @json($locations);

        // Posisi awal peta
        const map = L.map('map').setView([-7.7956, 110.3695], 13);

        // OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);


        locations.forEach(location => {

            if (!location.latitude || !location.longitude) {
                return;
            }

            const chargers = location.chargers || [];

            // Hitung charger berdasarkan status
            const freeChargers = chargers.filter(
                charger => charger.status === 'free'
            );

            const totalChargers = chargers.length;

            let statusText = '';
            let statusClass = '';

            if (freeChargers.length > 0) {
                statusText = `🟢 ${freeChargers.length} charger FREE`;
                statusClass = 'free';
            } else if (totalChargers > 0) {
                statusText = '🔴 Semua charger sedang digunakan';
                statusClass = 'busy';
            } else {
                statusText = '⚪ Tidak ada data charger';
                statusClass = 'unknown';
            }

            const popup = `
                <div>
                    <div class="popup-title">
                        ${location.name}
                    </div>

                    <div>
                        ${location.address ?? ''}
                    </div>

                    <hr>

                    <div>
                        Total charger:
                        <strong>${totalChargers}</strong>
                    </div>

                    <div class="${statusClass}">
                        ${statusText}
                    </div>
                </div>
            `;

            L.marker([
                parseFloat(location.latitude),
                parseFloat(location.longitude)
            ])
            .addTo(map)
            .bindPopup(popup);
        });
    </script>

</body>
</html>
```
