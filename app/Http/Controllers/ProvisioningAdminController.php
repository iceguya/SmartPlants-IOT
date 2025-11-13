<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProvisioningToken;
use App\Models\Device;
use Illuminate\Support\Str;

class ProvisioningAdminController extends Controller
{
    public function index()
    {
        $tokens = ProvisioningToken::where('user_id', auth()->id())
            ->latest()
            ->limit(20)
            ->get();
            
        return view('provisioning.index', compact('tokens'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'planned_device_id' => 'nullable|string|max:255',
            'name_hint' => 'nullable|string|max:255',
            'location_hint' => 'nullable|string|max:255',
            'ttl_hours' => 'nullable|integer|min:1|max:168',
        ]);

        $token = ProvisioningToken::create([
            'token' => Str::random(36),
            'planned_device_id' => $validated['planned_device_id'] ?? null,
            'name_hint' => $validated['name_hint'] ?? null,
            'location_hint' => $validated['location_hint'] ?? null,
            'expires_at' => now()->addHours((int)($validated['ttl_hours'] ?? 12)),
            'claimed' => false,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('provisioning.index')
            ->with('status', "Token created: {$token->token}");
    }

    public function destroy($id)
    {
        $token = ProvisioningToken::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $message = "Token deleted: {$token->token}";

        // Delete associated device if claimed
        if ($token->claimed && $token->claimed_device_id) {
            $device = Device::find($token->claimed_device_id);
            
            if ($device) {
                $deviceName = $device->name;
                $device->delete(); // Cascade delete sensors, readings, commands
                $message = "Token and device '{$deviceName}' deleted with all associated data.";
            }
        }

        $token->delete();

        return redirect()->route('provisioning.index')->with('status', $message);
    }
}
