<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check()) return redirect('/login.html');

        $mqtt = [
            'host' => env('MQTT_HOST', '172.20.10.5'),
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

        return view('dashboard', compact('mqtt', 'topics'));
    }
}
