<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Command;
use App\Services\AnalyticsService;

class DashboardController extends Controller
{
    public function index()
    {
        $devices = Device::where('user_id', auth()->id())
            ->withCount('sensors')
            ->orderBy('name')
            ->get();
        
        // Update status berdasarkan last_seen (jika > 5 menit = offline)
        foreach ($devices as $device) {
            if ($device->last_seen) {
                $isOnline = $device->last_seen->diffInMinutes(now()) < 5;
                if ($device->status !== ($isOnline ? 'online' : 'offline')) {
                    $device->update(['status' => $isOnline ? 'online' : 'offline']);
                    $device->refresh(); // Reload dari database
                }
            } else {
                // Jika belum pernah kirim data
                if ($device->status !== 'offline') {
                    $device->update(['status' => 'offline']);
                    $device->refresh(); // Reload dari database
                }
            }
        }
        
        // Hitung analytics untuk semua device
        $analytics = [
            'temperature' => ['avg' => 0],
            'humidity' => ['avg' => 0],
            'soil_moisture' => ['avg' => 0],
        ];
        
        // Ambil rata-rata dari semua sensor terbaru (24 jam terakhir) untuk user ini
        $tempReadings = \App\Models\SensorReading::whereHas('sensor', function($q) {
            $q->where('type', 'temp')->whereHas('device', function($qq) {
                $qq->where('user_id', auth()->id());
            });
        })->where('recorded_at', '>=', now()->subHours(24))->avg('value');
        
        $humReadings = \App\Models\SensorReading::whereHas('sensor', function($q) {
            $q->where('type', 'hum')->whereHas('device', function($qq) {
                $qq->where('user_id', auth()->id());
            });
        })->where('recorded_at', '>=', now()->subHours(24))->avg('value');
        
        $soilReadings = \App\Models\SensorReading::whereHas('sensor', function($q) {
            $q->where('type', 'soil')->whereHas('device', function($qq) {
                $qq->where('user_id', auth()->id());
            });
        })->where('recorded_at', '>=', now()->subHours(24))->avg('value');
        
        $analytics['temperature']['avg'] = $tempReadings ? round($tempReadings, 1) : '--';
        $analytics['humidity']['avg'] = $humReadings ? round($humReadings, 1) : '--';
        $analytics['soil_moisture']['avg'] = $soilReadings ? round($soilReadings, 1) : '--';
        
        return view('dashboard.index', compact('devices', 'analytics'));
    }

    public function device(Device $device)
    {
        // Pastikan device milik user yang sedang login
        if ($device->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to device');
        }

        // Update status real-time
        if ($device->last_seen) {
            $isOnline = $device->last_seen->diffInMinutes(now()) < 5;
            if ($device->status !== ($isOnline ? 'online' : 'offline')) {
                $device->update(['status' => $isOnline ? 'online' : 'offline']);
                $device->refresh(); // Reload dari database
            }
        }
        
        $device->load(['sensors' => function($q){
            $q->with(['readings' => function($qq){
                $qq->orderBy('recorded_at','desc')->limit(50);
            }]);
        }]);

        $analytics = app(AnalyticsService::class)->summariesForDevice($device);

        return view('dashboard.device', compact('device','analytics'));
    }

    public function waterOn(Device $device, Request $req)
    {
        // Pastikan device milik user yang sedang login
        if ($device->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to device');
        }

        $dur = (int)($req->input('duration_sec',5));
        Command::create([
            'device_id'=>$device->id,
            'command'=>'water_on',
            'params'=>['duration_sec'=>$dur],
        ]);
        return back()->with('status',"Command water_on {$dur}s dikirim (pending).");
    }
}

