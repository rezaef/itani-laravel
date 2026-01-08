<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SensorNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorController extends Controller
{
    public function __construct(private readonly SensorNotificationService $notif)
    {
    }

    public function latest()
    {
        $row = DB::table('sensor_readings')->orderByDesc('reading_time')->first();

        if (!$row) return response()->json(['exists' => false]);
        $thresholds = [
        'temp' => ['label'=>'Suhu',         'min'=>24,  'max'=>30,  'near_low'=>23,  'near_high'=>31,  'unit'=>'°C'],
        'humi' => ['label'=>'Kelembapan',   'min'=>40,  'max'=>70,  'near_low'=>39,  'near_high'=>75,  'unit'=>'%'],
        'ph'   => ['label'=>'pH Tanah',     'min'=>5.5, 'max'=>7.0, 'near_low'=>5.4, 'near_high'=>7.3, 'unit'=>''],

        'ec'   => ['label'=>'EC',           'min'=>1000,'max'=>2500,'near_low'=>900, 'near_high'=>2700,'unit'=>'µS/cm'],
        'n'    => ['label'=>'Nitrogen (N)', 'min'=>80,  'max'=>150, 'near_low'=>70,  'near_high'=>170, 'unit'=>'mg/kg'],
        'p'    => ['label'=>'Fosfor (P)',   'min'=>35,  'max'=>60,  'near_low'=>30,  'near_high'=>75,  'unit'=>'mg/kg'],
        'k'    => ['label'=>'Kalium (K)',   'min'=>60,  'max'=>120, 'near_low'=>55,  'near_high'=>150, 'unit'=>'mg/kg'],
        ];


        return response()->json([
            'exists' => true,
            'ph'   => $row->ph !== null ? (float)$row->ph : null,
            'humi' => $row->soil_moisture !== null ? (float)$row->soil_moisture : null,
            'temp' => $row->soil_temp !== null ? (float)$row->soil_temp : null,
            'ec'   => $row->ec !== null ? (float)$row->ec : null,
            'n'    => $row->n !== null ? (int)$row->n : null,
            'p'    => $row->p !== null ? (int)$row->p : null,
            'k'    => $row->k !== null ? (int)$row->k : null,
            'time' => $row->reading_time,
        ]);
    }

    

    public function history(Request $request)
    {
        $limit = (int) $request->query('limit', 20);
        if ($limit <= 0) $limit = 20;
        if ($limit > 50) $limit = 50;

        $rows = DB::table('sensor_readings')
            ->orderByDesc('reading_time')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['exists' => false, 'items' => []]);
        }

        // balikkan jadi urutan lama -> baru
        $items = $rows->reverse()->values()->map(function ($row) {
            return [
                'ph'   => $row->ph !== null ? (float)$row->ph : null,
                'humi' => $row->soil_moisture !== null ? (float)$row->soil_moisture : null,
                'temp' => $row->soil_temp !== null ? (float)$row->soil_temp : null,
                'ec'   => $row->ec !== null ? (float)$row->ec : null,
                'n'    => $row->n !== null ? (int)$row->n : null,
                'p'    => $row->p !== null ? (int)$row->p : null,
                'k'    => $row->k !== null ? (int)$row->k : null,
                'time' => $row->reading_time,
            ];
        });

        return response()->json([
            'exists' => true,
            'limit'  => $limit,
            'items'  => $items,
        ]);
    }

public function insert(Request $request)
    {
        $input = $request->json()->all();
        if (!is_array($input)) {
            return response()->json(['success' => false, 'error' => 'Invalid JSON body'], 400);
        }

        $ph = array_key_exists('ph', $input) && $input['ph'] !== null ? (float)$input['ph'] : null;

        // humi mapping
        $humi = null;
        if (array_key_exists('humi', $input)) $humi = $input['humi'] !== null ? (float)$input['humi'] : null;
        elseif (array_key_exists('moisture', $input)) $humi = $input['moisture'] !== null ? (float)$input['moisture'] : null;
        elseif (array_key_exists('soil_moisture', $input)) $humi = $input['soil_moisture'] !== null ? (float)$input['soil_moisture'] : null;

        // temp mapping
        $temp = null;
        if (array_key_exists('temp', $input)) $temp = $input['temp'] !== null ? (float)$input['temp'] : null;
        elseif (array_key_exists('temperature', $input)) $temp = $input['temperature'] !== null ? (float)$input['temperature'] : null;
        elseif (array_key_exists('soil_temp', $input)) $temp = $input['soil_temp'] !== null ? (float)$input['soil_temp'] : null;

        $ec = array_key_exists('ec', $input) && $input['ec'] !== null ? (float)$input['ec'] : null;
        $n  = array_key_exists('n',  $input) && $input['n']  !== null ? (int)$input['n'] : null;
        $p  = array_key_exists('p',  $input) && $input['p']  !== null ? (int)$input['p'] : null;
        $k  = array_key_exists('k',  $input) && $input['k']  !== null ? (int)$input['k'] : null;

        try {
            DB::table('sensor_readings')->insert([
                'ph' => $ph,
                'soil_moisture' => $humi,
                'soil_temp' => $temp,
                'ec' => $ec,
                'n' => $n,
                'p' => $p,
                'k' => $k,
                'reading_time' => now(),
            ]);

            // Buat notifikasi jika nilai melewati / hampir mencapai ambang batas
            $this->notif->evaluateAndCreate([
                'ph' => $ph,
                'soil_moisture' => $humi,
                'soil_temp' => $temp,
                'ec' => $ec,
                'n' => $n,
                'p' => $p,
                'k' => $k,
            ]);

            return response()->json([
                'success' => true,
                'saved' => compact('ph','humi','temp','ec','n','p','k'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
