<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check()) return redirect('/login.html');

        $mqtt = [
            'host' => env('MQTT_HOST', '172.0.0.1'),
            'port' => (int) env('MQTT_PORT', 15675),
            'path' => env('MQTT_PATH', '/ws'),
            'username' => env('MQTT_USERNAME', 'okra'),
            'password' => env('MQTT_PASSWORD', 'okra123'),
        ];

        $topics = [
            'sensor' => env('MQTT_TOPIC_SENSOR', 'okra/sensor'),
            'pump_cmd' => env('MQTT_TOPIC_PUMP_CMD', 'okra/pump/cmd'),
            'pump_status' => env('MQTT_TOPIC_PUMP_STATUS', 'okra/pump/status'),
            'auto_mode' => env('MQTT_TOPIC_AUTO_MODE', 'okra/pump/autoMode'),
        ];

        // Chart history limit: user minta maksimal 10 atau 20
        $limit = (int) $request->query('chartLimit', 20);
        if (!in_array($limit, [10, 20], true)) $limit = 20;

        // Server-side initial history (seperti versi JSP)
        $history = [];
        try {
            $rows = DB::table('sensor_readings')
                ->orderByDesc('reading_time')
                ->limit($limit)
                ->get();

            $history = $rows->reverse()->values()->map(function ($row) {
                return [
                    'time' => $row->reading_time,
                    'ph'   => $row->ph !== null ? (float) $row->ph : null,
                    'humi' => $row->soil_moisture !== null ? (float) $row->soil_moisture : null,
                    'temp' => $row->soil_temp !== null ? (float) $row->soil_temp : null,
                    'ec'   => $row->ec !== null ? (float) $row->ec : null,
                    'n'    => $row->n !== null ? (int) $row->n : null,
                    'p'    => $row->p !== null ? (int) $row->p : null,
                    'k'    => $row->k !== null ? (int) $row->k : null,
                ];
            })->all();
        } catch (\Throwable $e) {
            // jika tabel belum ada / error, dashboard tetap jalan
            $history = [];
        }

        return view('dashboard', compact('mqtt', 'topics', 'history', 'limit'));
    }
}
