<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AutomationService;
use App\Models\Sensor;
use App\Models\SensorReading;

class DeviceIngestController extends Controller
{
    public function __construct(
        private AutomationService $automation
    ) {}

    public function store(Request $request)
    {
        $device = $request->attributes->get('device');

        $validated = $request->validate([
            'readings' => 'required|array|min:1',
            'readings.*.type' => 'required|in:soil,temp,hum,color_r,color_g,color_b',
            'readings.*.value' => 'required|numeric',
            'timestamp' => 'nullable|date|after_or_equal:-1 hour', // Max 1 hour old
        ]);

        $timestamp = $validated['timestamp'] ?? now();
        
        // Get existing sensors efficiently
        $sensors = $device->sensors()->pluck('id', 'type');

        // Prepare batch inserts
        $readings = collect($validated['readings'])->map(function($reading) use ($device, $sensors, $timestamp) {
            $sensorId = $sensors->get($reading['type']) 
                ?? $this->createSensor($device->id, $reading['type']);

            return [
                'sensor_id' => $sensorId,
                'value' => $reading['value'],
                'recorded_at' => $timestamp,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Bulk insert readings
        SensorReading::insert($readings->toArray());

        // Update device status based on data timestamp
        $this->updateDeviceStatus($device, $timestamp);

        // Trigger automation checks only if device is actually online
        if ($device->fresh()->status === 'online') {
            $this->automation->checkAndTriggerRules($device);
        }

        return response()->json(['message' => 'Data received successfully']);
    }

    /**
     * Update device status based on data timestamp
     */
    private function updateDeviceStatus($device, $timestamp): void
    {
        $dataAge = now()->diffInMinutes($timestamp);
        
        // Only mark as online if data is recent (within 5 minutes)
        if ($dataAge < 5) {
            $device->update([
                'last_seen' => $timestamp,
                'status' => 'online'
            ]);
        } else {
            // Data is old, just record last_seen but keep offline
            $device->update([
                'last_seen' => $timestamp,
                'status' => 'offline'
            ]);
        }
    }

    /**
     * Create sensor if not exists
     */
    private function createSensor(string $deviceId, string $type): int
    {
        $sensor = Sensor::create([
            'device_id' => $deviceId,
            'type' => $type,
            'unit' => $this->getSensorUnit($type),
            'label' => $this->getSensorLabel($type),
        ]);

        return $sensor->id;
    }

    /**
     * Get sensor unit by type
     */
    private function getSensorUnit(string $type): string
    {
        return match($type) {
            'temp' => 'C',
            'hum', 'soil' => '%',
            default => 'au',
        };
    }

    /**
     * Get sensor label by type
     */
    private function getSensorLabel(string $type): string
    {
        return match($type) {
            'soil' => 'Soil Moisture',
            'temp' => 'Air Temperature',
            'hum' => 'Air Humidity',
            'color_r' => 'Leaf R',
            'color_g' => 'Leaf G',
            'color_b' => 'Leaf B',
            default => strtoupper($type),
        };
    }
}
