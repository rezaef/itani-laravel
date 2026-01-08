<?php

namespace App\Services;

use App\Models\SensorNotification;
use Illuminate\Support\Carbon;

class SensorNotificationService
{
    /**
     * Anti-spam (1 notifikasi per jenis sensor) + state machine.
     *
     * - Tidak membuat baris baru berulang-ulang.
     * - Baris yang sama akan di-update (nilai terbaru), tanpa membuat badge meledak.
     * - Jika status berubah (misal warning -> danger), notifikasi jadi UNREAD lagi + occurrences++.
     * - Jika kembali normal, notifikasi diset normal+read (dan di UI bisa disembunyikan).
     */
    public function evaluateAndCreate(array $reading): void
    {
        // Normalisasi key: dukung key baru & lama
        $humi = $reading['humi']
            ?? $reading['soil_moisture']
            ?? $reading['moisture']
            ?? $reading['soilMoisture']
            ?? null;

        $temp = $reading['temp']
            ?? $reading['soil_temp']
            ?? $reading['temperature']
            ?? $reading['soilTemp']
            ?? null;

        $ph = $reading['ph'] ?? null;
        $ec = $reading['ec'] ?? null; // µS/cm
        $n  = $reading['n']  ?? null;
        $p  = $reading['p']  ?? null;
        $k  = $reading['k']  ?? null;

        $normalized = [
            'humi' => $humi !== null ? (float) $humi : null,
            'temp' => $temp !== null ? (float) $temp : null,
            'ph'   => $ph   !== null ? (float) $ph   : null,
            'ec'   => $ec   !== null ? (float) $ec   : null,
            'n'    => $n    !== null ? (float) $n    : null,
            'p'    => $p    !== null ? (float) $p    : null,
            'k'    => $k    !== null ? (float) $k    : null,
        ];

        // Ambang batas (default aman walau .env belum diisi)
        $limits = [
            'temp' => [
                'label' => 'Suhu Tanah',
                'unit'  => '°C',
                'min'   => (float) env('NOTIF_TEMP_MIN', 24),
                'max'   => (float) env('NOTIF_TEMP_MAX', 30),
                'near_abs' => (float) env('NOTIF_TEMP_NEAR', 1.0),
            ],
            'humi' => [
                'label' => 'Kelembapan Tanah',
                'unit'  => '%',
                'min'   => (float) env('NOTIF_HUMI_MIN', 40),
                'max'   => (float) env('NOTIF_HUMI_MAX', 70),
                'near_pct' => (float) env('NOTIF_HUMI_NEAR_PCT', 10),
            ],
            'ph' => [
                'label' => 'pH Tanah',
                'unit'  => '',
                'min'   => (float) env('NOTIF_PH_MIN', 5.5),
                'max'   => (float) env('NOTIF_PH_MAX', 7.0),
                'near_abs' => (float) env('NOTIF_PH_NEAR', 0.2),
            ],
            'ec' => [
                'label' => 'EC',
                'unit'  => 'µS/cm',
                'min'   => (float) env('NOTIF_EC_MIN', 100),
                'max'   => (float) env('NOTIF_EC_MAX', 250),
                'near_abs' => (float) env('NOTIF_EC_NEAR', 20),
            ],
            'n' => [
                'label' => 'Nitrogen (N)',
                'unit'  => 'mg/kg',
                'min'   => (float) env('NOTIF_N_MIN', 80),
                'max'   => (float) env('NOTIF_N_MAX', 150),
                'near_pct' => (float) env('NOTIF_N_NEAR_PCT', 10),
            ],
            'p' => [
                'label' => 'Fosfor (P)',
                'unit'  => 'mg/kg',
                'min'   => (float) env('NOTIF_P_MIN', 35),
                'max'   => (float) env('NOTIF_P_MAX', 60),
                'near_pct' => (float) env('NOTIF_P_NEAR_PCT', 10),
            ],
            'k' => [
                'label' => 'Kalium (K)',
                'unit'  => 'mg/kg',
                'min'   => (float) env('NOTIF_K_MIN', 60),
                'max'   => (float) env('NOTIF_K_MAX', 120),
                'near_pct' => (float) env('NOTIF_K_NEAR_PCT', 10),
            ],
        ];

        $now = now();

        foreach ($limits as $metric => $cfg) {
            $val = $normalized[$metric] ?? null;
            if ($val === null) continue;

            $min = (float) $cfg['min'];
            $max = (float) $cfg['max'];

            // Tentukan state
            $state = 'normal';
            if ($val < $min) {
                $state = 'danger_low';
            } elseif ($val > $max) {
                $state = 'danger_high';
            } else {
                $near = null;
                if (isset($cfg['near_abs'])) {
                    $near = (float) $cfg['near_abs'];
                } elseif (isset($cfg['near_pct'])) {
                    $near = (($max - $min) * ((float) $cfg['near_pct'] / 100.0));
                }

                if ($near !== null) {
                    if (($val - $min) <= $near) $state = 'warning_low';
                    elseif (($max - $val) <= $near) $state = 'warning_high';
                }
            }

            $this->upsertMetricNotif(
                dedupeKey: $metric,
                state: $state,
                cfg: $cfg,
                val: (float) $val,
                min: $min,
                max: $max,
                now: $now
            );
        }
    }

    private function upsertMetricNotif(
        string $dedupeKey,
        string $state,
        array $cfg,
        float $val,
        float $min,
        float $max,
        \DateTimeInterface $now,
    ): void {
        $notif = SensorNotification::query()
            ->whereNull('user_id')
            ->where('dedupe_key', $dedupeKey)
            ->first();

        // Jika kembali normal: cukup update status normal+read (di UI bisa disembunyikan)
        if ($state === 'normal') {
            if ($notif && $notif->state !== 'normal') {
                $notif->fill([
                    'level' => 'info',
                    'title' => 'Kondisi Normal',
                    'message' => sprintf(
                        '%s kembali normal (%s–%s%s). Nilai sekarang: %s%s.',
                        $cfg['label'],
                        $this->fmt($min),
                        $this->fmt($max),
                        $cfg['unit'],
                        $this->fmt($val),
                        $cfg['unit']
                    ),
                    'state' => 'normal',
                    'is_read' => true,
                    'last_seen_at' => $now,
                ])->save();
            } elseif ($notif) {
                // keep last_seen_at up to date (optional)
                $notif->update(['last_seen_at' => $now]);
            }
            return;
        }

        // Compose pesan sesuai state
        [$level, $title, $message] = $this->composeMessage($state, $cfg, $val, $min, $max);

        if (!$notif) {
            SensorNotification::create([
                'user_id' => null,
                'dedupe_key' => $dedupeKey,
                'level' => $level,
                'title' => $title,
                'message' => $message,
                'state' => $state,
                'occurrences' => 1,
                'is_read' => false,
                'last_seen_at' => $now,
                'last_triggered_at' => $now,
            ]);
            return;
        }

        $changed = ($notif->state ?? 'normal') !== $state;

        $updates = [
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'state' => $state,
            'last_seen_at' => $now,
        ];

        // Hanya dianggap "kejadian baru" kalau status berubah
        if ($changed) {
            $updates['is_read'] = false;
            $updates['occurrences'] = (int) ($notif->occurrences ?? 1) + 1;
            $updates['last_triggered_at'] = $now;
        }

        $notif->fill($updates)->save();
    }

    private function composeMessage(string $state, array $cfg, float $val, float $min, float $max): array
    {
        $label = $cfg['label'];
        $unit  = $cfg['unit'];

        if ($state === 'danger_low') {
            return [
                'danger',
                'Peringatan Ambang Batas',
                sprintf(
                    '%s di bawah ambang normal (%s–%s%s). Nilai sekarang: %s%s.',
                    $label,
                    $this->fmt($min),
                    $this->fmt($max),
                    $unit,
                    $this->fmt($val),
                    $unit
                ),
            ];
        }

        if ($state === 'danger_high') {
            return [
                'danger',
                'Peringatan Ambang Batas',
                sprintf(
                    '%s di atas ambang normal (%s–%s%s). Nilai sekarang: %s%s.',
                    $label,
                    $this->fmt($min),
                    $this->fmt($max),
                    $unit,
                    $this->fmt($val),
                    $unit
                ),
            ];
        }

        if ($state === 'warning_low') {
            return [
                'warning',
                'Notifikasi Ambang Batas',
                sprintf(
                    '%s hampir mencapai batas minimum (%s%s). Nilai sekarang: %s%s.',
                    $label,
                    $this->fmt($min),
                    $unit,
                    $this->fmt($val),
                    $unit
                ),
            ];
        }

        // warning_high
        return [
            'warning',
            'Notifikasi Ambang Batas',
            sprintf(
                '%s hampir mencapai batas maksimum (%s%s). Nilai sekarang: %s%s.',
                $label,
                $this->fmt($max),
                $unit,
                $this->fmt($val),
                $unit
            ),
        ];
    }

    private function fmt(float $n): string
    {
        if (abs($n - round($n)) < 0.00001) return (string) ((int) round($n));
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}
