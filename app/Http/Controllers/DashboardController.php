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
        $devices = Device::withCount('sensors')->orderBy('name')->get();
        return view('dashboard.index', compact('devices'));
    }

    public function device(Device $device)
    {
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
        $dur = (int)($req->input('duration_sec',5));
        Command::create([
            'device_id'=>$device->id,
            'command'=>'water_on',
            'params'=>['duration_sec'=>$dur],
        ]);
        return back()->with('status',"Command water_on {$dur}s dikirim (pending).");
    }
}

