<?php

namespace App\Traits;

use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Support\Facades\DB;

trait HasSensorQueriesAlternative
{
    /**
     * ALTERNATIVE 1: Average per device, then average again
     * 
     * Example:
     * Device 1 average temp: 28.5°C
     * Device 2 average temp: 30.0°C
     * Final average: (28.5 + 30.0) / 2 = 29.25°C
     */
    protected function getAverageSensorReadingsPerDevice(int $hoursAgo = 24): array
    {
        $userId = auth()->id();
        
        return [
            'temperature' => $this->getAverageByDevice($userId, 'temp', $hoursAgo),
            'humidity' => $this->getAverageByDevice($userId, 'hum', $hoursAgo),
            'soil_moisture' => $this->getAverageByDevice($userId, 'soil', $hoursAgo),
        ];
    }

    private function getAverageByDevice(int $userId, string $type, int $hoursAgo): ?float
    {
        // Step 1: Get average per device
        $deviceAverages = SensorReading::select(
                'sensors.device_id',
                DB::raw('AVG(sensor_readings.value) as device_avg')
            )
            ->join('sensors', 'sensor_readings.sensor_id', '=', 'sensors.id')
            ->join('devices', 'sensors.device_id', '=', 'devices.id')
            ->where('devices.user_id', $userId)
            ->where('sensors.type', $type)
            ->where('sensor_readings.recorded_at', '>=', now()->subHours($hoursAgo))
            ->groupBy('sensors.device_id')
            ->get();

        if ($deviceAverages->isEmpty()) {
            return null;
        }

        // Step 2: Average of averages
        $finalAvg = $deviceAverages->avg('device_avg');
        
        return $finalAvg ? round($finalAvg, 1) : null;
    }

    /**
     * ALTERNATIVE 2: Weighted average (consider number of readings per device)
     * 
     * Example:
     * Device 1: 10 readings, avg 28.5°C
     * Device 2: 5 readings, avg 30.0°C
     * Weighted: (28.5*10 + 30.0*5) / (10+5) = 29.0°C
     */
    protected function getWeightedAverageSensorReadings(int $hoursAgo = 24): array
    {
        $userId = auth()->id();
        
        return [
            'temperature' => $this->getWeightedAverage($userId, 'temp', $hoursAgo),
            'humidity' => $this->getWeightedAverage($userId, 'hum', $hoursAgo),
            'soil_moisture' => $this->getWeightedAverage($userId, 'soil', $hoursAgo),
        ];
    }

    private function getWeightedAverage(int $userId, string $type, int $hoursAgo): ?float
    {
        $result = SensorReading::select(
                DB::raw('SUM(sensor_readings.value) as total_sum'),
                DB::raw('COUNT(sensor_readings.value) as total_count')
            )
            ->join('sensors', 'sensor_readings.sensor_id', '=', 'sensors.id')
            ->join('devices', 'sensors.device_id', '=', 'devices.id')
            ->where('devices.user_id', $userId)
            ->where('sensors.type', $type)
            ->where('sensor_readings.recorded_at', '>=', now()->subHours($hoursAgo))
            ->first();

        if (!$result || $result->total_count == 0) {
            return null;
        }

        $avg = $result->total_sum / $result->total_count;
        return round($avg, 1);
    }

    /**
     * ALTERNATIVE 3: Latest reading per device, then average
     * 
     * Example:
     * Device 1 latest: 29.0°C
     * Device 2 latest: 30.5°C
     * Average: (29.0 + 30.5) / 2 = 29.75°C
     */
    protected function getAverageOfLatestReadings(int $minutesAgo = 30): array
    {
        $userId = auth()->id();
        
        return [
            'temperature' => $this->getLatestAverage($userId, 'temp', $minutesAgo),
            'humidity' => $this->getLatestAverage($userId, 'hum', $minutesAgo),
            'soil_moisture' => $this->getLatestAverage($userId, 'soil', $minutesAgo),
        ];
    }

    private function getLatestAverage(int $userId, string $type, int $minutesAgo): ?float
    {
        // Get latest reading per device
        $latestReadings = DB::table('sensor_readings')
            ->select('sensors.device_id', 'sensor_readings.value')
            ->join('sensors', 'sensor_readings.sensor_id', '=', 'sensors.id')
            ->join('devices', 'sensors.device_id', '=', 'devices.id')
            ->where('devices.user_id', $userId)
            ->where('sensors.type', $type)
            ->where('sensor_readings.recorded_at', '>=', now()->subMinutes($minutesAgo))
            ->whereIn('sensor_readings.id', function($q) use ($userId, $type) {
                $q->select(DB::raw('MAX(sr.id)'))
                  ->from('sensor_readings as sr')
                  ->join('sensors as s', 'sr.sensor_id', '=', 's.id')
                  ->join('devices as d', 's.device_id', '=', 'd.id')
                  ->where('d.user_id', $userId)
                  ->where('s.type', $type)
                  ->groupBy('s.device_id');
            })
            ->get();

        if ($latestReadings->isEmpty()) {
            return null;
        }

        $avg = $latestReadings->avg('value');
        return $avg ? round($avg, 1) : null;
    }
}
