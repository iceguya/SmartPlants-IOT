<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Command;
use App\Services\AnalyticsService;
use App\Services\DeviceStatusService;
use App\Traits\HasDeviceAuthorization;
use App\Traits\HasSensorQueries;

class DashboardController extends Controller
{
    use HasDeviceAuthorization, HasSensorQueries;

    public function __construct(
        private DeviceStatusService $deviceStatus,
        private AnalyticsService $analytics
    ) {}

    public function index()
    {
        $devices = Device::where('user_id', auth()->id())
            ->withCount('sensors')
            ->orderBy('name')
            ->get();
        
        // Update all device statuses efficiently
        $this->deviceStatus->updateDevicesStatus($devices);
        
        // Get analytics using trait
        $analytics = $this->formatAnalytics(
            $this->getAverageSensorReadings(24)
        );
        
        return view('dashboard.index', compact('devices', 'analytics'));
    }

    public function device(Device $device)
    {
        $this->authorizeDevice($device);
        
        // Update status
        $this->deviceStatus->updateDeviceStatus($device);
        
        // Eager load relationships efficiently
        $device->load(['sensors.readings' => fn($q) => 
            $q->orderBy('recorded_at', 'desc')->limit(50)
        ]);

        $analytics = $this->analytics->summariesForDevice($device);

        return view('dashboard.device', compact('device', 'analytics'));
    }

    public function waterOn(Device $device, Request $request)
    {
        $this->authorizeDevice($device);

        $validated = $request->validate([
            'duration_sec' => 'nullable|integer|min:1|max:60'
        ]);

        $duration = $validated['duration_sec'] ?? 5;

        Command::create([
            'device_id' => $device->id,
            'command' => 'water_on',
            'params' => ['duration_sec' => $duration],
        ]);

        return back()->with('status', "Watering command sent ({$duration}s)");
    }

    /**
     * Format analytics data
     */
    private function formatAnalytics(array $data): array
    {
        return [
            'temperature' => ['avg' => $data['temperature'] ?? '--'],
            'humidity' => ['avg' => $data['humidity'] ?? '--'],
            'soil_moisture' => ['avg' => $data['soil_moisture'] ?? '--'],
        ];
    }
}
