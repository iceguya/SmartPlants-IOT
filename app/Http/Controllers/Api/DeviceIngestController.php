<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AutomationService;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Notifications\SensorAlert;
use Illuminate\Support\Facades\Log;

class DeviceIngestController extends Controller
{
    public function __construct(
        private AutomationService $automation
    ) {}

    public function store(Request $request)
    {
        // Device object is attached by ValidateDeviceOwnership middleware
        $device = $request->attributes->get('device');
        
        // Additional safety check (should never happen with middleware)
        if (!$device) {
            \Log::error('Ingest request missing device from middleware');
            return response()->json([
                'message' => 'Device validation failed',
                'error' => 'MIDDLEWARE_ERROR',
            ], 500);
        }

        // Verify device ownership (double-check for security)
        if ($device->isOrphaned()) {
            \Log::warning('Attempted data ingestion from orphaned device', [
                'device_id' => $device->id,
            ]);
            
            return response()->json([
                'message' => 'Device has no owner',
                'error' => 'ORPHANED_DEVICE',
            ], 403);
        }

        $validated = $request->validate([
            'readings' => 'required|array|min:1',
            'readings.*.type' => 'required|in:soil,temp,hum,color_r,color_g,color_b',
            'readings.*.value' => 'required|numeric',
            'timestamp' => 'nullable|date|after_or_equal:-1 hour',
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

        // Check sensor thresholds and send alerts
        $this->checkSensorThresholds($device, $validated['readings']);

        // Update device status based on data timestamp
        $this->updateDeviceStatus($device, $timestamp);

        // Trigger automation checks only if device is actually online
        if ($device->fresh()->status === 'online') {
            $this->automation->checkAndTriggerRules($device);
        }

        return response()->json(['message' => 'Data received successfully']);
    }

    /**
     * Check sensor values against thresholds and trigger alerts
     */
    private function checkSensorThresholds($device, array $readings): void
    {
        $user = $device->user;
        
        if (!$user) {
            return;
        }

        foreach ($readings as $reading) {
            $type = $reading['type'];
            $value = $reading['value'];

            // Soil Moisture Alerts
            if ($type === 'soil') {
                if ($value < 30) {
                    $this->sendAlert($user, [
                        'title' => 'Critical: Soil Moisture Too Low',
                        'message' => "Soil moisture is {$value}% (Threshold: 30%)",
                        'solution' => 'Water the plant immediately with approximately 200-300ml of water. Check again in 30 minutes.',
                        'severity' => 'critical',
                        'icon' => 'soil',
                        'metadata' => [
                            'sensorType' => 'Soil Moisture',
                            'value' => $value,
                            'threshold' => 30,
                            'deviceName' => $device->name,
                        ],
                    ]);
                } elseif ($value > 80) {
                    $this->sendAlert($user, [
                        'title' => 'Warning: Soil Too Wet',
                        'message' => "Soil moisture is {$value}% (Threshold: 80%)",
                        'solution' => 'Stop watering. Ensure proper drainage. Overwatering can cause root rot.',
                        'severity' => 'warning',
                        'icon' => 'soil',
                        'metadata' => [
                            'sensorType' => 'Soil Moisture',
                            'value' => $value,
                            'threshold' => 80,
                            'deviceName' => $device->name,
                        ],
                    ]);
                }
            }

            // Temperature Alerts
            if ($type === 'temp') {
                if ($value > 35) {
                    $this->sendAlert($user, [
                        'title' => 'Warning: High Temperature',
                        'message' => "Temperature is {$value}°C (Threshold: 35°C)",
                        'solution' => 'Move plant to a cooler, shaded area. Increase ventilation. Consider misting leaves to cool down.',
                        'severity' => 'warning',
                        'icon' => 'temperature',
                        'metadata' => [
                            'sensorType' => 'Temperature',
                            'value' => $value,
                            'threshold' => 35,
                            'deviceName' => $device->name,
                        ],
                    ]);
                } elseif ($value < 10) {
                    $this->sendAlert($user, [
                        'title' => 'Critical: Temperature Too Low',
                        'message' => "Temperature is {$value}°C (Threshold: 10°C)",
                        'solution' => 'Move plant indoors or to a warmer location immediately. Cold stress can damage plant cells.',
                        'severity' => 'critical',
                        'icon' => 'temperature',
                        'metadata' => [
                            'sensorType' => 'Temperature',
                            'value' => $value,
                            'threshold' => 10,
                            'deviceName' => $device->name,
                        ],
                    ]);
                }
            }

            // Humidity Alerts
            if ($type === 'hum') {
                if ($value < 30) {
                    $this->sendAlert($user, [
                        'title' => 'Warning: Low Humidity',
                        'message' => "Humidity is {$value}% (Threshold: 30%)",
                        'solution' => 'Increase humidity by misting leaves, using a humidity tray, or placing a humidifier nearby.',
                        'severity' => 'warning',
                        'icon' => 'general',
                        'metadata' => [
                            'sensorType' => 'Humidity',
                            'value' => $value,
                            'threshold' => 30,
                            'deviceName' => $device->name,
                        ],
                    ]);
                }
            }
        }

        // Check RGB color sensor for plant health (requires all 3 components)
        $this->checkPlantHealthColor($device, $readings, $user);
    }

    /**
     * Check RGB color readings for plant health alerts
     */
    private function checkPlantHealthColor($device, array $readings, $user): void
    {
        // Group readings by type
        $colorReadings = collect($readings)->whereIn('type', ['color_r', 'color_g', 'color_b']);
        
        if ($colorReadings->count() === 3) {
            $r = $colorReadings->firstWhere('type', 'color_r')['value'] ?? 0;
            $g = $colorReadings->firstWhere('type', 'color_g')['value'] ?? 0;
            $b = $colorReadings->firstWhere('type', 'color_b')['value'] ?? 0;

            $total = $r + $g + $b;
            
            if ($total === 0) {
                return; // No data
            }

            $gPercent = ($g / $total) * 100;
            $rPercent = ($r / $total) * 100;

            // Brown/Dark detection (potential disease or dead leaves)
            if (abs($r - $g) < 30 && $r > $b && $g > $b && $b < 100) {
                $this->sendAlert($user, [
                    'title' => 'Health Alert: Brown/Dark Color Detected',
                    'message' => "RGB: ({$r}, {$g}, {$b}) - Plant may have brown or dying leaves",
                    'solution' => 'Inspect plant for dead leaves, disease, or nutrient deficiency. Remove dead foliage and check soil condition.',
                    'severity' => 'warning',
                    'icon' => 'health',
                    'metadata' => [
                        'sensorType' => 'Plant Health (RGB)',
                        'value' => "RGB({$r},{$g},{$b})",
                        'threshold' => 'Brown/Dark Pattern',
                        'deviceName' => $device->name,
                    ],
                ]);
            }
            // Red dominant (stressed plant)
            elseif ($rPercent > 45 && $r > $g) {
                $this->sendAlert($user, [
                    'title' => 'Health Alert: Plant Stress Detected',
                    'message' => "RGB: ({$r}, {$g}, {$b}) - Red dominant color indicates stress",
                    'solution' => 'Check for overwatering, nutrient burn, or pest damage. Adjust care routine accordingly.',
                    'severity' => 'warning',
                    'icon' => 'health',
                    'metadata' => [
                        'sensorType' => 'Plant Health (RGB)',
                        'value' => "RGB({$r},{$g},{$b})",
                        'threshold' => 'Red Dominant',
                        'deviceName' => $device->name,
                    ],
                ]);
            }
        }
    }

    /**
     * Send notification to user with throttling to prevent spam
     */
    private function sendAlert($user, array $alertData): void
    {
        try {
            // Throttle: Only send same alert type every 30 minutes
            $recentAlert = $user->notifications()
                ->where('type', SensorAlert::class)
                ->where('created_at', '>', now()->subMinutes(30))
                ->where('data->title', $alertData['title'])
                ->where('data->device_name', $alertData['metadata']['deviceName'])
                ->first();

            if ($recentAlert) {
                // Alert already sent recently, skip
                return;
            }

            // Send notification
            $user->notify(new SensorAlert(
                $alertData['title'],
                $alertData['message'],
                $alertData['solution'],
                $alertData['severity'],
                $alertData['icon'],
                $alertData['metadata']
            ));

            Log::info('Sensor alert sent', [
                'user_id' => $user->id,
                'title' => $alertData['title'],
                'device' => $alertData['metadata']['deviceName'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send sensor alert', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
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
