<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Traits\HasSensorQueries;
use App\Services\SensorDataService;

class SensorController extends Controller
{
    use HasSensorQueries;

    protected $sensorDataService;

    public function __construct(SensorDataService $sensorDataService)
    {
        $this->sensorDataService = $sensorDataService;
    }

    /**
     * Environment Sensors Page (Temperature & Humidity)
     */
    public function environment(Request $request)
    {
        $user = auth()->user();
        $timeRange = $request->get('range', '1h');
        
        // Validate time range
        if (!$this->sensorDataService->isValidTimeRange($timeRange)) {
            $timeRange = '1h';
        }
        
        $startTime = $this->sensorDataService->getStartTime($timeRange);
        
        // Get latest temperature reading
        $latestTemp = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'temp')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->orderBy('recorded_at', 'desc')
        ->first();

        // Get latest humidity reading
        $latestHumidity = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'hum')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->orderBy('recorded_at', 'desc')
        ->first();

        // Get filtered temperature readings
        $tempReadings = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'temp')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->where('recorded_at', '>=', $startTime)
        ->with(['sensor.device'])
        ->orderBy('recorded_at', 'desc')
        ->get();

        // Get filtered humidity readings
        $humidityReadings = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'hum')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->where('recorded_at', '>=', $startTime)
        ->with(['sensor.device'])
        ->orderBy('recorded_at', 'desc')
        ->get();

        // Prepare chart data
        $tempChartData = $this->sensorDataService->prepareChartData($tempReadings, $timeRange);
        $humidityChartData = $this->sensorDataService->prepareChartData($humidityReadings, $timeRange);

        // Paginate logs
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $allLogs = $tempReadings->merge($humidityReadings)->sortByDesc('recorded_at');
        $tempLogs = $allLogs->forPage($currentPage, $perPage);
        $totalLogs = $allLogs->count();

        return view('sensors.environment', compact(
            'latestTemp',
            'latestHumidity',
            'tempLogs',
            'tempChartData',
            'humidityChartData',
            'timeRange',
            'currentPage',
            'perPage',
            'totalLogs'
        ))->with('timeRanges', $this->sensorDataService->getAllTimeRanges());
    }

    /**
     * Soil Moisture Sensors Page
     */
    public function soil(Request $request)
    {
        $user = auth()->user();
        $timeRange = $request->get('range', '1h');
        
        if (!$this->sensorDataService->isValidTimeRange($timeRange)) {
            $timeRange = '1h';
        }
        
        $startTime = $this->sensorDataService->getStartTime($timeRange);
        
        // Get latest soil moisture reading
        $latestMoisture = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'soil')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->orderBy('recorded_at', 'desc')
        ->first();

        // Get filtered readings
        $moistureReadings = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'soil')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->where('recorded_at', '>=', $startTime)
        ->with(['sensor.device'])
        ->orderBy('recorded_at', 'desc')
        ->get();

        // Prepare chart data
        $moistureChartData = $this->sensorDataService->prepareChartData($moistureReadings, $timeRange);

        // Paginate logs
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $moistureLogs = $moistureReadings->forPage($currentPage, $perPage);
        $totalLogs = $moistureReadings->count();

        return view('sensors.soil', compact(
            'latestMoisture',
            'moistureLogs',
            'moistureChartData',
            'timeRange',
            'currentPage',
            'perPage',
            'totalLogs'
        ))->with('timeRanges', $this->sensorDataService->getAllTimeRanges());
    }

    /**
     * Plant Health Sensors Page (RGB/Color Detection)
     */
    public function health(Request $request)
    {
        $user = auth()->user();
        $timeRange = $request->get('range', '1h');
        
        if (!$this->sensorDataService->isValidTimeRange($timeRange)) {
            $timeRange = '1h';
        }
        
        $startTime = $this->sensorDataService->getStartTime($timeRange);
        
        // Get latest RGB component readings (color_r, color_g, color_b)
        $latestRed = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'color_r')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->orderBy('recorded_at', 'desc')
        ->first();

        $latestGreen = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'color_g')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->orderBy('recorded_at', 'desc')
        ->first();

        $latestBlue = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'color_b')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->orderBy('recorded_at', 'desc')
        ->first();

        // Get filtered RGB readings for charts
        $redReadings = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'color_r')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->where('recorded_at', '>=', $startTime)
        ->orderBy('recorded_at', 'asc')
        ->get();

        $greenReadings = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'color_g')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->where('recorded_at', '>=', $startTime)
        ->orderBy('recorded_at', 'asc')
        ->get();

        $blueReadings = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->where('type', 'color_b')
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->where('recorded_at', '>=', $startTime)
        ->orderBy('recorded_at', 'asc')
        ->get();

        // Prepare chart data
        $redChartData = $this->sensorDataService->prepareChartData($redReadings, $timeRange);
        $greenChartData = $this->sensorDataService->prepareChartData($greenReadings, $timeRange);
        $blueChartData = $this->sensorDataService->prepareChartData($blueReadings, $timeRange);

        // Build current RGB values
        $rgbValues = $this->buildRgbValues($latestRed, $latestGreen, $latestBlue);
        
        // Generate HEX color
        $hexColor = $this->rgbToHex($rgbValues['r'], $rgbValues['g'], $rgbValues['b']);
        
        // Interpret the color (plant health status)
        $colorInterpretation = $this->interpretColor($rgbValues);

        // Get combined logs for table (group by timestamp)
        $allReadings = SensorReading::whereHas('sensor', function($q) use ($user) {
            $q->whereIn('type', ['color_r', 'color_g', 'color_b'])
              ->whereHas('device', fn($q) => $q->where('user_id', $user->id));
        })
        ->where('recorded_at', '>=', $startTime)
        ->with(['sensor.device'])
        ->orderBy('recorded_at', 'desc')
        ->get();

        // Group readings by timestamp (within 1 second tolerance)
        $groupedLogs = $this->groupColorReadings($allReadings);

        // Paginate grouped logs
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $rgbLogs = collect($groupedLogs)->forPage($currentPage, $perPage);
        $totalLogs = count($groupedLogs);

        return view('sensors.health', compact(
            'latestRed',
            'latestGreen',
            'latestBlue',
            'rgbValues',
            'hexColor',
            'colorInterpretation',
            'rgbLogs',
            'redChartData',
            'greenChartData',
            'blueChartData',
            'timeRange',
            'currentPage',
            'perPage',
            'totalLogs'
        ))->with('timeRanges', $this->sensorDataService->getAllTimeRanges());
    }

    /**
     * Build RGB values array from individual sensor readings
     */
    private function buildRgbValues($redReading, $greenReading, $blueReading): array
    {
        return [
            'r' => $redReading ? (int) $redReading->value : 0,
            'g' => $greenReading ? (int) $greenReading->value : 0,
            'b' => $blueReading ? (int) $blueReading->value : 0,
        ];
    }

    /**
     * Convert RGB values to HEX color string
     */
    private function rgbToHex(int $r, int $g, int $b): string
    {
        // Clamp values to 0-255 range
        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));
        
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    /**
     * Interpret RGB color to determine plant health status
     */
    private function interpretColor(array $rgb): array
    {
        $r = $rgb['r'];
        $g = $rgb['g'];
        $b = $rgb['b'];
        
        // No data case
        if ($r === 0 && $g === 0 && $b === 0) {
            return [
                'status' => 'No Data',
                'message' => 'No color sensor data available',
                'color' => 'gray',
                'icon' => 'question'
            ];
        }

        // Calculate dominance
        $max = max($r, $g, $b);
        $total = $r + $g + $b;
        
        // Avoid division by zero
        if ($total === 0) {
            return [
                'status' => 'Unknown',
                'message' => 'Unable to determine color',
                'color' => 'gray',
                'icon' => 'question'
            ];
        }

        $rPercent = ($r / $total) * 100;
        $gPercent = ($g / $total) * 100;
        $bPercent = ($b / $total) * 100;

        // Green dominant - Healthy plant
        if ($g === $max && $gPercent > 40) {
            return [
                'status' => 'Healthy',
                'message' => 'Plant appears healthy (Green dominant)',
                'color' => 'green',
                'icon' => 'check'
            ];
        }

        // Brown-ish (R and G similar, low B) - Soil/Stem/Dying leaves
        if (abs($r - $g) < 30 && $r > $b && $g > $b && $b < 100) {
            return [
                'status' => 'Soil/Stem Detected',
                'message' => 'Brown/Tan color detected - possible soil or woody stem',
                'color' => 'amber',
                'icon' => 'alert'
            ];
        }

        // Red dominant - Dying/Stressed
        if ($r === $max && $rPercent > 40) {
            return [
                'status' => 'Alert',
                'message' => 'Red dominant - plant may be stressed or unhealthy',
                'color' => 'red',
                'icon' => 'warning'
            ];
        }

        // Blue dominant - Unusual
        if ($b === $max && $bPercent > 40) {
            return [
                'status' => 'Unusual',
                'message' => 'Blue dominant - unusual reading, check sensor',
                'color' => 'blue',
                'icon' => 'info'
            ];
        }

        // Balanced colors - Mixed/Unknown
        return [
            'status' => 'Mixed',
            'message' => 'Mixed color detected - multiple plant parts or objects',
            'color' => 'purple',
            'icon' => 'info'
        ];
    }

    /**
     * Group color readings by timestamp (group R, G, B readings that occurred together)
     */
    private function groupColorReadings($readings): array
    {
        $grouped = [];
        
        foreach ($readings as $reading) {
            // Use timestamp rounded to nearest second as grouping key
            $timestamp = $reading->recorded_at->format('Y-m-d H:i:s');
            
            if (!isset($grouped[$timestamp])) {
                $grouped[$timestamp] = [
                    'timestamp' => $reading->recorded_at,
                    'device' => $reading->sensor->device,
                    'r' => 0,
                    'g' => 0,
                    'b' => 0,
                ];
            }
            
            // Assign value based on sensor type
            if ($reading->sensor->type === 'color_r') {
                $grouped[$timestamp]['r'] = (int) $reading->value;
            } elseif ($reading->sensor->type === 'color_g') {
                $grouped[$timestamp]['g'] = (int) $reading->value;
            } elseif ($reading->sensor->type === 'color_b') {
                $grouped[$timestamp]['b'] = (int) $reading->value;
            }
        }
        
        return array_values($grouped);
    }

    /**
     * Parse RGB value from sensor reading (legacy support)
     */
    private function parseRgbValue($reading): array
    {
        if (!$reading) {
            return ['r' => 0, 'g' => 0, 'b' => 0];
        }

        // If stored as JSON
        if (is_string($reading->value)) {
            $decoded = json_decode($reading->value, true);
            if ($decoded && isset($decoded['r'], $decoded['g'], $decoded['b'])) {
                return $decoded;
            }
        }

        // Default return
        return ['r' => 0, 'g' => 0, 'b' => 0];
    }
}
