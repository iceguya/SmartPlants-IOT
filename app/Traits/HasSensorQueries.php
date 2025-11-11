<?php

namespace App\Traits;

use App\Models\Sensor;
use App\Models\SensorReading;

trait HasSensorQueries
{
    /**
     * Get latest sensor reading value for a device
     */
    protected function getLatestSensorValue($device, string $sensorType, int $minutesAgo = 5): ?float
    {
        return SensorReading::whereHas('sensor', function($q) use ($device, $sensorType) {
            $q->where('device_id', $device->id)->where('type', $sensorType);
        })
        ->where('recorded_at', '>=', now()->subMinutes($minutesAgo))
        ->orderBy('recorded_at', 'desc')
        ->value('value');
    }

    /**
     * Get average sensor readings for user's devices
     */
    protected function getAverageSensorReadings(int $hoursAgo = 24): array
    {
        $userId = auth()->id();
        
        return [
            'temperature' => $this->getAverageBySensorType($userId, 'temp', $hoursAgo),
            'humidity' => $this->getAverageBySensorType($userId, 'hum', $hoursAgo),
            'soil_moisture' => $this->getAverageBySensorType($userId, 'soil', $hoursAgo),
        ];
    }

    /**
     * Get average reading by sensor type
     */
    private function getAverageBySensorType(int $userId, string $type, int $hoursAgo): ?float
    {
        $avg = SensorReading::whereHas('sensor', function($q) use ($userId, $type) {
            $q->where('type', $type)
              ->whereHas('device', fn($qq) => $qq->where('user_id', $userId));
        })
        ->where('recorded_at', '>=', now()->subHours($hoursAgo))
        ->avg('value');

        return $avg ? round($avg, 1) : null;
    }
}
