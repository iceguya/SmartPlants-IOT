<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\AutomationRule;

class AutomationController extends Controller
{
    public function index(Device $device)
    {
        // Ensure device belongs to authenticated user
        if ($device->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to device');
        }

        $rules = $device->automationRules()->get();
        return view('automation.index', compact('device', 'rules'));
    }

    public function store(Request $request, Device $device)
    {
        if ($device->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'condition_type' => 'required|in:soil_low,soil_high,temp_low,temp_high,hum_low,hum_high',
            'threshold_value' => 'required|numeric|min:0|max:100',
            'action_duration' => 'required|integer|min:1|max:60',
            'cooldown_minutes' => 'required|integer|min:5|max:1440',
        ]);

        $device->automationRules()->create([
            'enabled' => true,
            'condition_type' => $data['condition_type'],
            'threshold_value' => $data['threshold_value'],
            'action' => 'water_on',
            'action_duration' => $data['action_duration'],
            'cooldown_minutes' => $data['cooldown_minutes'],
        ]);

        return back()->with('status', 'Automation rule created successfully!');
    }

    public function toggle(Device $device, AutomationRule $rule)
    {
        if ($device->user_id !== auth()->id() || $rule->device_id !== $device->id) {
            abort(403);
        }

        $rule->update(['enabled' => !$rule->enabled]);
        return back()->with('status', $rule->enabled ? 'Rule enabled' : 'Rule disabled');
    }

    public function destroy(Device $device, AutomationRule $rule)
    {
        if ($device->user_id !== auth()->id() || $rule->device_id !== $device->id) {
            abort(403);
        }

        $rule->delete();
        return back()->with('status', 'Automation rule deleted');
    }
}

