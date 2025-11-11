<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AutomationService;

class DeviceIngestController extends Controller
{
    public function store(Request $req)
{
    $device = $req->attributes->get('device');

    $data = $req->validate([
        'readings' => 'required|array|min:1',
        'readings.*.type' => 'required|in:soil,temp,hum,color_r,color_g,color_b',
        'readings.*.value' => 'required|numeric',
        'timestamp' => 'nullable|date',
    ]);

    $ts = $data['timestamp'] ?? now();

    // map sensors by type
    $sensors = $device->sensors()->get()->keyBy('type');
    foreach ($data['readings'] as $r) {
        $sensor = $sensors->get($r['type'])
            ?? \App\Models\Sensor::create([
                'device_id'=>$device->id,
                'type'=>$r['type'], 'unit'=>null, 'label'=>strtoupper($r['type']),
            ]);

        \App\Models\SensorReading::create([
            'sensor_id' => $sensor->id,
            'value' => $r['value'],
            'recorded_at' => $ts,
        ]);
    }

    $device->update(['last_seen'=>now(),'status'=>'online']);

    // Check automation rules after sensor data is received
    app(AutomationService::class)->checkAndTriggerRules($device);

    return response()->json(['message'=>'OK']);
}

}
