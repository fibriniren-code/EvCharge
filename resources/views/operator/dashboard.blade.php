@php
    use App\Models\Charger;
    use App\Models\ChargingSession;
    use App\Models\Location;
    use App\Models\Review;

    // ---------- DATA ----------
    $stations = Location::with(['chargers.runningSession.user'])->orderBy('nama_lokasi')->get();

    $raw = Charger::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
    $counts = [];
    foreach (['available', 'reserved', 'occupied', 'faulted', 'unavailable'] as $s) {
        $counts[$s] = (int) ($raw[$s] ?? 0);
    }
    $counts['total'] = array_sum($counts);

    $running = ChargingSession::with(['user', 'charger.location'])->running()->latest('id_session')->get();

    $todayDone = ChargingSession::whereIn('status', ['completed', 'interrupted'])
        ->where('waktu_selesai', '>=', now()->startOfDay());
    $todaySessions = (clone $todayDone)->count();
    $todayKwh = (float) (clone $todayDone)->sum('energi_kwh');

    $complaints = Review::with(['user', 'location'])
        ->whereNull('balasan_operator')
        ->where(fn ($q) => $q->where('rating_alat', '<=', 2)->orWhere('rating_tempat', '<=', 2))
        ->latest('id_review')
        ->take(4)
        ->get();

    // ---------- TAMPILAN ----------
    $dur = fn (int $s) => sprintf('%02d:%02d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60);

    $statusInfo = [
        'available'   => ['Tersedia',  '#34D399', 'rgba(52,211,153,0.10)'],
        'reserved'    => ['Dipesan',   '#FBBF24', 'rgba(251,191,36,0.10)'],
        'occupied'    => ['Digunakan', '#38BDF8', 'rgba(56,189,248,0.10)'],
        'faulted'     => ['Rusak',     '#FB7185', 'rgba(251,113,133,0.10)'],
        'unavailable' => ['Nonaktif',  '#94A3B8', 'rgba(148,163,184,0.08)'],
    ];

    $card = 'background-color:#1E293B; border:1px solid #334155; border-radius:1.25rem;';
    $stats = [
        ['Total Port',       $counts['total'],                          '#E2E8F0'],
        ['Tersedia',         $counts['available'],                      '#34D399'],
        ['Digunakan',        $counts['occupied'],                       '#38BDF8'],
        ['Dipesan',          $counts['reserved'],                       '#FBBF24'],
        ['Rusak / Nonaktif', $counts['faulted'] + $counts['unavailable'], '#FB7185'],
        ['Energi Hari Ini',  number_format($todayKwh, 1, ',', '.') . ' kWh', '#A78BFA'],
    ];
@endphp

<x-app-layout>
    {{-- ===== KARTU SAMBUTAN (tidak diubah) ===== --}}
    <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem 1.5rem 1rem 1.5rem;">
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.75rem;">
            <span style="background-color: rgba(59,130,246,0.2); color: #93C5FD; border: 1px solid rgba(147,197,253,0.3); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                Operator Dashboard
            </span>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0.75rem 0 0 0;">Selamat Datang, {{ Auth::user()->name }}!</h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.35rem; margin-bottom: 0;">
                Halaman operator sedang dikembangkan: pemantauan port real-time, peringatan gangguan,
                kendali status port/stasiun, dan penanganan keluhan pengemudi.
            </p>
        </div>
    </div>

    {{-- ===== TAMBAHAN: ISI MONITORING ===== --}}
    <div style="max-width: 80rem; margin: 0 auto; padding: 0 1.5rem 3rem 1.5rem;">

        {{-- Ringkasan angka --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:1.25rem;">
            @foreach ($stats as [$label, $value, $color])
                <div style="{{ $card }} padding:1.1rem 1.25rem;">
                    <p style="margin:0; font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#94A3B8;">{{ $label }}</p>
                    <p style="margin:0.5rem 0 0 0; font-size:1.75rem; font-weight:800; color:{{ $color }};">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:1.25rem; align-items:start;">

            {{-- Status port per stasiun --}}
            <div style="grid-column: span 2; min-width:0;">
                @forelse ($stations as $st)
                    <div style="{{ $card }} margin-bottom:1.25rem; overflow:hidden;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 1.25rem; border-bottom:1px solid #334155;">
                            <div style="min-width:0;">
                                <p style="margin:0; font-weight:800; color:#FFFFFF; font-size:0.95rem;">{{ $st->nama_lokasi }}</p>
                                <p style="margin:0.15rem 0 0 0; font-size:0.75rem; color:#94A3B8;">{{ $st->alamat }}</p>
                            </div>
                            <span style="font-size:0.7rem; font-weight:700; padding:0.25rem 0.7rem; border-radius:9999px; white-space:nowrap;
                                {{ $st->status === 'aktif' ? 'color:#34D399; background:rgba(52,211,153,0.12);' : 'color:#FB7185; background:rgba(251,113,133,0.12);' }}">
                                {{ $st->status === 'aktif' ? 'Beroperasi' : 'Ditutup' }}
                            </span>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:0.75rem; padding:1.25rem;">
                            @forelse ($st->chargers as $c)
                                @php
                                    [$label, $color, $bg] = $statusInfo[$c->status] ?? $statusInfo['unavailable'];
                                    $run = $c->runningSession;
                                @endphp
                                <div style="background:{{ $bg }}; border:1px solid {{ $color }}55; border-radius:0.9rem; padding:0.8rem;">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-weight:800; color:#FFFFFF; font-size:0.85rem;">#{{ $c->id_charger }}</span>
                                        <span style="font-size:0.65rem; font-weight:800; color:{{ $color }};">● {{ $label }}</span>
                                    </div>
                                    <p style="margin:0.5rem 0 0 0; font-size:0.7rem; color:#94A3B8;">{{ $c->charger_type }} · {{ (float) $c->power_kw }} kW</p>
                                    @if ($run)
                                        <p style="margin:0.5rem 0 0 0; font-size:0.75rem; font-weight:700; color:#FFFFFF;">{{ $run->user->name ?? 'Pengemudi' }}</p>
                                        <p style="margin:0; font-size:0.7rem; color:#38BDF8;">{{ $dur($run->durationSeconds()) }}</p>
                                    @endif
                                </div>
                            @empty
                                <p style="margin:0; font-size:0.8rem; color:#64748B; grid-column:1/-1;">Belum ada port di stasiun ini.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div style="{{ $card }} padding:2rem; text-align:center;">
                        <p style="margin:0; font-weight:800; color:#FFFFFF;">Belum ada data stasiun</p>
                        <p style="margin:0.4rem 0 0 0; font-size:0.85rem; color:#94A3B8;">Stasiun dan port dibuat oleh administrator. Setelah ada, statusnya tampil di sini.</p>
                    </div>
                @endforelse
            </div>

            {{-- Keluhan terbaru --}}
            <div style="{{ $card }} overflow:hidden; min-width:0;">
                <div style="padding:1rem 1.25rem; border-bottom:1px solid #334155; display:flex; justify-content:space-between; align-items:center;">
                    <p style="margin:0; font-weight:800; color:#FFFFFF; font-size:0.95rem;">Keluhan Terbaru</p>
                    <a href="{{ route('operator.reviews.index', ['filter' => 'complaints']) }}" style="font-size:0.75rem; font-weight:700; color:#34D399; text-decoration:none;">Lihat semua</a>
                </div>
                @forelse ($complaints as $r)
                    <div style="padding:0.9rem 1.25rem; border-bottom:1px solid #334155;">
                        <div style="display:flex; justify-content:space-between; gap:0.5rem;">
                            <span style="font-weight:700; color:#FFFFFF; font-size:0.85rem;">{{ $r->user->name ?? 'Pengguna' }}</span>
                            <span style="color:#FBBF24; font-size:0.75rem;">★ {{ min($r->rating_alat, $r->rating_tempat) }}/5</span>
                        </div>
                        <p style="margin:0.15rem 0 0 0; font-size:0.7rem; color:#64748B;">{{ $r->location->nama_lokasi ?? '—' }} · {{ $r->created_at->diffForHumans() }}</p>
                        <p style="margin:0.35rem 0 0 0; font-size:0.78rem; color:#94A3B8;">{{ $r->ulasan_alat ?: ($r->ulasan_tempat ?: '—') }}</p>
                    </div>
                @empty
                    <p style="margin:0; padding:1.5rem 1.25rem; font-size:0.85rem; color:#94A3B8;">Tidak ada keluhan yang belum dibalas.</p>
                @endforelse
            </div>
        </div>

        {{-- Sesi charging berjalan --}}
        <div style="{{ $card }} margin-top:1.25rem; overflow:hidden;">
            <div style="padding:1rem 1.25rem; border-bottom:1px solid #334155; display:flex; justify-content:space-between; align-items:center;">
                <p style="margin:0; font-weight:800; color:#FFFFFF; font-size:0.95rem;">Sesi Charging Berjalan</p>
                <span style="font-size:0.7rem; font-weight:700; color:#38BDF8; background:rgba(56,189,248,0.12); padding:0.25rem 0.7rem; border-radius:9999px;">{{ $running->count() }} aktif</span>
            </div>

            @if ($running->count())
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.82rem; text-align:left;">
                        <thead>
                            <tr style="color:#64748B; font-size:0.65rem; text-transform:uppercase; letter-spacing:0.06em;">
                                <th style="padding:0.75rem 1.25rem;">Sesi</th>
                                <th style="padding:0.75rem 1rem;">Pengemudi</th>
                                <th style="padding:0.75rem 1rem;">Stasiun · Port</th>
                                <th style="padding:0.75rem 1rem;">Durasi</th>
                                <th style="padding:0.75rem 1rem;">Energi</th>
                                <th style="padding:0.75rem 1rem;">Estimasi Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($running as $s)
                                @php $bill = $s->computeBill(); @endphp
                                <tr style="border-top:1px solid #334155; color:#CBD5E1;">
                                    <td style="padding:0.8rem 1.25rem; font-weight:800; color:#FFFFFF;">#{{ $s->id_session }}</td>
                                    <td style="padding:0.8rem 1rem;">{{ $s->user->name ?? '—' }}</td>
                                    <td style="padding:0.8rem 1rem;">{{ $s->charger->location->nama_lokasi ?? '—' }} · #{{ $s->id_charger }}</td>
                                    <td style="padding:0.8rem 1rem; color:#38BDF8; font-family:monospace;">{{ $dur($s->durationSeconds()) }}</td>
                                    <td style="padding:0.8rem 1rem;">{{ number_format($bill['kwh'], 2, ',', '.') }} kWh</td>
                                    <td style="padding:0.8rem 1rem;">Rp {{ number_format($bill['total'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="margin:0; padding:1.75rem 1.25rem; font-size:0.85rem; color:#94A3B8;">Tidak ada sesi yang sedang berjalan.</p>
            @endif
        </div>
    </div>

    {{-- Segarkan halaman otomatis tiap 15 detik --}}
    @push('scripts')
        <script>setTimeout(() => location.reload(), 15000);</script>
    @endpush
</x-app-layout>