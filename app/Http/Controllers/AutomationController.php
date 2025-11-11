<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\AutomationRule;
use App\Traits\HasDeviceAuthorization;
use App\Http\Requests\AutomationRuleRequest;

class AutomationController extends Controller
{
    use HasDeviceAuthorization;

    public function index(Device $device)
    {
        $this->authorizeDevice($device);

        $rules = $device->automationRules()->orderBy('created_at', 'desc')->get();
        
        return view('automation.index', compact('device', 'rules'));
    }

    public function store(AutomationRuleRequest $request, Device $device)
    {
        $this->authorizeDevice($device);

        $device->automationRules()->create([
            ...$request->validated(),
            'enabled' => true,
            'action' => 'water_on',
        ]);

        return back()->with('status', 'Automation rule created successfully!');
    }

    public function toggle(Device $device, AutomationRule $rule)
    {
        $this->authorizeDeviceAndRule($device, $rule);

        $rule->update(['enabled' => !$rule->enabled]);
        
        return back()->with('status', $rule->enabled ? 'Rule enabled' : 'Rule disabled');
    }

    public function destroy(Device $device, AutomationRule $rule)
    {
        $this->authorizeDeviceAndRule($device, $rule);

        $rule->delete();
        
        return back()->with('status', 'Automation rule deleted');
    }

    /**
     * Authorize device and rule ownership
     */
    private function authorizeDeviceAndRule(Device $device, AutomationRule $rule): void
    {
        $this->authorizeDevice($device);

        if ($rule->device_id !== $device->id) {
            abort(403, 'Rule does not belong to this device');
        }
    }
}

