<?php

namespace App\Services;

use App\Models\SensorNotification;
use Illuminate\Support\Facades\Cache;

class SensorNotificationService
{
    /**
     * Evaluasi data sensor dan buat notifikasi jika:
     * - Melewati ambang batas (danger)
     * - Hampir mencapai ambang batas (warning)
     */
    public function evaluateAndCreate(array $reading): void
{
    // Normalisasi key: dukung key baru & lama
    // humi: bisa datang sebagai humi / soil_moisture / moisture / soil_moisture
    $humi = $reading['humi']
        ?? $reading['soil_moisture']
        ?? $reading['moisture']
        ?? $reading['soilMoisture']
        ?? null;

    // temp: bisa datang sebagai temp / soil_temp / temperature
    $temp = $reading['temp']
        ?? $reading['soil_temp']
        ?? $reading['temperature']
        ?? $reading['soilTemp']
        ?? null;

    $ph = $reading['ph'] ?? null;

    // tambahan sensor
    $ec = $reading['ec'] ?? null; // µS/cm
    $n  = $reading['n']  ?? null;
    $p  = $reading['p']  ?? null;
    $k  = $reading['k']  ?? null;

    $normalized = [
        'humi' => $humi !== null ? (float)$humi : null,
        'temp' => $temp !== null ? (float)$temp : null,
        'ph'   => $ph   !== null ? (float)$ph   : null,

        'ec'   => $ec   !== null ? (float)$ec   : null,
        'n'    => $n    !== null ? (float)$n    : null,
        'p'    => $p    !== null ? (float)$p    : null,
        'k'    => $k    !== null ? (float)$k    : null,
    ];

    // Ambang batas (default aman walau .env belum diisi)
    $limits = [
        'humi' => [
            'min' => (float) env('NOTIF_HUMI_MIN', 40),
            'max' => (float) env('NOTIF_HUMI_MAX', 70),
            'near_pct' => (float) env('NOTIF_HUMI_NEAR_PCT', 10),
            'label' => 'Kelembapan Tanah',
            'unit' => '%',
        ],
        'temp' => [
            'min' => (float) env('NOTIF_TEMP_MIN', 24),
            'max' => (float) env('NOTIF_TEMP_MAX', 30),
            'near_abs' => (float) env('NOTIF_TEMP_NEAR', 1.0),
            'label' => 'Suhu Tanah',
            'unit' => '°C',
        ],
        'ph' => [
            'min' => (float) env('NOTIF_PH_MIN', 5.5),
            'max' => (float) env('NOTIF_PH_MAX', 7.0),
            'near_abs' => (float) env('NOTIF_PH_NEAR', 0.2),
            'label' => 'pH Tanah',
            'unit' => '',
        ],

        // EC (µS/cm)
        'ec' => [
            'min' => (float) env('NOTIF_EC_MIN', 100),
            'max' => (float) env('NOTIF_EC_MAX', 250),
            'near_abs' => (float) env('NOTIF_EC_NEAR', 20), // jarak 20 µS/cm dari batas
            'label' => 'EC',
            'unit' => 'µS/cm',
        ],

        // NPK (ppm/mg/kg) — pakai near_pct biar fleksibel
        'n' => [
            'min' => (float) env('NOTIF_N_MIN', 80),
            'max' => (float) env('NOTIF_N_MAX', 150),
            'near_pct' => (float) env('NOTIF_N_NEAR_PCT', 10),
            'label' => 'Nitrogen (N)',
            'unit' => 'mg/kg',
        ],
        'p' => [
            'min' => (float) env('NOTIF_P_MIN', 35),
            'max' => (float) env('NOTIF_P_MAX', 60),
            'near_pct' => (float) env('NOTIF_P_NEAR_PCT', 10),
            'label' => 'Fosfor (P)',
            'unit' => 'mg/kg',
        ],
        'k' => [
            'min' => (float) env('NOTIF_K_MIN', 60),
            'max' => (float) env('NOTIF_K_MAX', 120),
            'near_pct' => (float) env('NOTIF_K_NEAR_PCT', 10),
            'label' => 'Kalium (K)',
            'unit' => 'mg/kg',
        ],
    ];

    $cooldownSec = (int) env('NOTIF_COOLDOWN_SEC', 300);

    foreach ($limits as $key => $cfg) {
        if (!array_key_exists($key, $normalized) || $normalized[$key] === null) continue;

        $val = (float) $normalized[$key];
        $min = (float) $cfg['min'];
        $max = (float) $cfg['max'];

        // DANGER dulu
        if ($val < $min || $val > $max) {
            $direction = $val < $min ? 'di bawah' : 'di atas';

            $title = 'Peringatan Ambang Batas';
            $message = sprintf(
                '%s %s ambang normal (%s–%s%s). Nilai sekarang: %s%s.',
                $cfg['label'],
                $direction,
                $this->fmt($min),
                $this->fmt($max),
                $cfg['unit'],
                $this->fmt($val),
                $cfg['unit']
            );

            // dedupe: sensor+level+direction
            $this->createOnce("danger:$key:$direction", $title, $message, $cooldownSec);
            continue;
        }

        // WARNING (near boundary)
        $near = null;
        if (isset($cfg['near_abs'])) {
            $near = (float) $cfg['near_abs'];
        } elseif (isset($cfg['near_pct'])) {
            $near = (($max - $min) * ((float) $cfg['near_pct'] / 100.0));
        }

        if ($near !== null) {
            if (($val - $min) <= $near) {
                $title = 'Notifikasi Ambang Batas';
                $message = sprintf(
                    '%s hampir mencapai batas minimum (%s%s). Nilai sekarang: %s%s.',
                    $cfg['label'],
                    $this->fmt($min),
                    $cfg['unit'],
                    $this->fmt($val),
                    $cfg['unit']
                );
                $this->createOnce("warn:$key:min", $title, $message, $cooldownSec);
            } elseif (($max - $val) <= $near) {
                $title = 'Notifikasi Ambang Batas';
                $message = sprintf(
                    '%s hampir mencapai batas maksimum (%s%s). Nilai sekarang: %s%s.',
                    $cfg['label'],
                    $this->fmt($max),
                    $cfg['unit'],
                    $this->fmt($val),
                    $cfg['unit']
                );
                $this->createOnce("warn:$key:max", $title, $message, $cooldownSec);
            }
        }
    }
}


    private function createOnce(string $dedupeKey, string $title, string $message, int $cooldownSec): void
    {
        $cacheKey = 'notif_dedupe:' . $dedupeKey;
        if (Cache::has($cacheKey)) return;

        SensorNotification::create([
            'user_id' => null, // broadcast (nanti bisa dibuat per-user)
            'level' => str_starts_with($dedupeKey, 'danger:') ? 'danger' : 'warning',
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);

        Cache::put($cacheKey, true, $cooldownSec);
    }

    private function fmt(float $n): string
    {
        // tampilkan rapi (tanpa .0 kalau integer)
        if (abs($n - round($n)) < 0.00001) return (string) ((int) round($n));
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}
