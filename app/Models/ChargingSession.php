<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChargingSession extends Model
{
    use HasFactory;

    public const FALLBACK_RATE = 2466; // tarif cadangan bila stasiun belum punya tarif (Rp/kWh)

    protected $table = 'charging_sessions';
    protected $primaryKey = 'id_session';
    protected $guarded = [];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'energi_kwh' => 'decimal:2',
        'estimasi_biaya' => 'decimal:2',
        'harga_per_kwh' => 'decimal:2',
        'biaya_energi' => 'decimal:2',
        'biaya_layanan' => 'decimal:2',
        'biaya_overstay' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function charger()
    {
        return $this->belongsTo(Charger::class, 'id_charger', 'id_charger');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'id_reservation', 'id_reservation');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function durationSeconds(?Carbon $until = null): int
    {
        $until ??= $this->waktu_selesai ?? now();

        return max(0, (int) $this->waktu_mulai->diffInSeconds($until, true));
    }

    // Hitung tagihan dari tarif stasiun. Energi disimulasikan: daya charger x durasi.
    public function computeBill(?Carbon $at = null): array
    {
        $at ??= now();
        $this->loadMissing('charger.location', 'reservation');

        $seconds = max(1, $this->durationSeconds($at));
        $power = (float) ($this->charger->power_kw ?? 22);
        $kwh = round($power / 3600 * $seconds, 2);

        $tariff = $this->charger?->location?->currentTariff();
        $rate = (float) ($tariff->harga_per_kwh ?? self::FALLBACK_RATE);
        $service = (float) ($tariff->biaya_layanan ?? 0);
        $minimum = (float) ($tariff->biaya_minimum ?? 0);
        $overstayRate = (float) ($tariff->overstay_fee_per_menit ?? 0);

        $energyCost = round($kwh * $rate);

        $overstayMinutes = 0;
        $reservedUntil = $this->reservation?->waktu_selesai;
        if ($reservedUntil && $at->greaterThan($reservedUntil)) {
            $overstayMinutes = (int) ceil($reservedUntil->diffInSeconds($at, true) / 60);
        }
        $overstayCost = round($overstayMinutes * $overstayRate);

        $total = max($energyCost + $service, $minimum) + $overstayCost;

        return [
            'seconds' => $seconds,
            'power_kw' => $power,
            'kwh' => $kwh,
            'rate' => $rate,
            'energy_cost' => $energyCost,
            'service_fee' => $service,
            'overstay_minutes' => $overstayMinutes,
            'overstay_cost' => $overstayCost,
            'total' => $total,
        ];
    }

    // Akhiri sesi: hitung tagihan final, potong saldo, catat pembayaran, bebaskan charger dan reservasi.
    // Kekurangan saldo dicatat sebagai Payment "pending". Mengembalikan null bila sesi sudah tidak berjalan.
    public function settle(string $finalStatus = 'completed'): ?array
    {
        return DB::transaction(function () use ($finalStatus) {
            $session = static::whereKey($this->getKey())->lockForUpdate()->first();

            if (! $session || $session->status !== 'running') {
                return null;
            }

            $session->load(['charger.location', 'reservation']);

            $end = now();
            $bill = $session->computeBill($end);

            $user = User::whereKey($session->user_id)->lockForUpdate()->firstOrFail();
            $paid = min((float) $user->balance, (float) $bill['total']);
            $debt = (float) $bill['total'] - $paid;

            if ($paid > 0) {
                $user->decrement('balance', $paid);
            }

            $session->update([
                'waktu_selesai' => $end,
                'energi_kwh' => $bill['kwh'],
                'estimasi_biaya' => $bill['total'],
                'harga_per_kwh' => $bill['rate'],
                'biaya_energi' => $bill['energy_cost'],
                'biaya_layanan' => $bill['service_fee'],
                'biaya_overstay' => $bill['overstay_cost'],
                'status' => $finalStatus,
            ]);

            $session->charger?->update(['status' => 'available']);
            $session->reservation?->update(['status' => 'completed']);

            $ref = 'SES-' . str_pad((string) $session->id_session, 6, '0', STR_PAD_LEFT);

            if ($paid > 0) {
                Payment::create([
                    'user_id' => $user->id, 'id_session' => $session->id_session, 'metode' => 'Saldo',
                    'jumlah' => $paid, 'status' => 'success', 'waktu_pembayaran' => $end, 'referensi_gateway' => $ref,
                ]);
            }

            if ($debt > 0) {
                Payment::create([
                    'user_id' => $user->id, 'id_session' => $session->id_session, 'metode' => 'Saldo',
                    'jumlah' => $debt, 'status' => 'pending', 'referensi_gateway' => $ref,
                ]);
            }

            return $bill + ['paid' => $paid, 'debt' => $debt];
        });
    }
}