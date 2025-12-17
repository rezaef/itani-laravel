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
        // Default ambang batas mengikuti label UI di dashboard
        $limits = [
            'soil_moisture' => [
                'min' => (float) env('NOTIF_HUMI_MIN', 40),
                'max' => (float) env('NOTIF_HUMI_MAX', 70),
                // dekat batas dalam persen dari range
                'near_pct' => (float) env('NOTIF_HUMI_NEAR_PCT', 10),
                'label' => 'Kelembapan Tanah',
                'unit' => '%',
            ],
            'soil_temp' => [
                'min' => (float) env('NOTIF_TEMP_MIN', 24),
                'max' => (float) env('NOTIF_TEMP_MAX', 30),
                // dekat batas dalam derajat
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
        ];

        // Anti-spam: notif jenis yang sama maksimal 1x per 5 menit
        $cooldownSec = (int) env('NOTIF_COOLDOWN_SEC', 300);

        foreach ($limits as $key => $cfg) {
            if (!array_key_exists($key, $reading) || $reading[$key] === null) continue;

            $val = (float) $reading[$key];
            $min = $cfg['min'];
            $max = $cfg['max'];

            // out-of-range => danger
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

                $this->createOnce("danger:$key:$direction", $title, $message, $cooldownSec);
                continue;
            }

            // near-boundary => warning ("hampir")
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
