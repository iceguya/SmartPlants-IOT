<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProvisioningController extends Controller
{
    public function claim(Request $req)
    {
        $data = $req->validate([
            'token' => 'required|string',
            'device_id' => 'required|string',
            'name' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        // Validate token
        $pt = \App\Models\ProvisioningToken::where('token', $data['token'])->first();
        if (!$pt) {
            \Log::warning('Provisioning failed: Invalid token', ['token' => $data['token']]);
            return response()->json(['message' => 'Invalid provisioning token'], 404);
        }
        
        if ($pt->claimed) {
            \Log::warning('Provisioning failed: Token already used', [
                'token' => $pt->token,
                'claimed_at' => $pt->claimed_at,
                'claimed_device_id' => $pt->claimed_device_id
            ]);
            return response()->json(['message' => 'Token already claimed'], 409);
        }
        
        if ($pt->expires_at->isPast()) {
            \Log::warning('Provisioning failed: Token expired', [
                'token' => $pt->token,
                'expired_at' => $pt->expires_at
            ]);
            return response()->json(['message' => 'Token expired'], 410);
        }

        // Check if device already exists
        $device = \App\Models\Device::find($data['device_id']);
        
        if (!$device) {
            // NEW DEVICE: Create and assign to token owner
            $device = \App\Models\Device::create([
                'id' => $data['device_id'],
                'name' => $data['name'] ?? $pt->name_hint ?? 'ESP8266 SmartPlant',
                'location' => $data['location'] ?? $pt->location_hint ?? 'Home',
                'api_key' => Str::random(40),
                'status' => 'offline',
                'user_id' => $pt->user_id,
            ]);

            // Create default sensors
            $defaultSensors = [
                ['type' => 'soil', 'unit' => '%', 'label' => 'Soil Moisture'],
                ['type' => 'temp', 'unit' => 'C', 'label' => 'Temperature'],
                ['type' => 'hum', 'unit' => '%', 'label' => 'Humidity'],
                ['type' => 'color_r', 'unit' => 'au', 'label' => 'Leaf Red'],
                ['type' => 'color_g', 'unit' => 'au', 'label' => 'Leaf Green'],
                ['type' => 'color_b', 'unit' => 'au', 'label' => 'Leaf Blue'],
            ];
            
            foreach ($defaultSensors as $sensorDef) {
                \App\Models\Sensor::firstOrCreate(
                    array_merge($sensorDef, ['device_id' => $device->id]),
                    $sensorDef
                );
            }

            \Log::info('New device provisioned', [
                'device_id' => $device->id,
                'user_id' => $pt->user_id,
                'user_email' => $pt->user->email ?? 'N/A',
                'token' => $pt->token,
            ]);
            
        } else {
            // EXISTING DEVICE: Strict ownership validation
            
            // Case 1: Device already owned by different user - BLOCK
            if ($device->user_id !== null && $device->user_id !== $pt->user_id) {
                \Log::error('Provisioning blocked: Device ownership conflict', [
                    'device_id' => $device->id,
                    'current_owner_id' => $device->user_id,
                    'attempted_owner_id' => $pt->user_id,
                    'token' => $pt->token,
                ]);
                
                return response()->json([
                    'message' => 'Device already registered to another user',
                    'error' => 'DEVICE_OWNERSHIP_CONFLICT',
                    'hint' => 'This device is already claimed. To transfer ownership, clear device EEPROM and provision with a new token.',
                ], 409);
            }
            
            // Case 2: Device owned by same user - RE-PROVISION (renew API key)
            if ($device->user_id === $pt->user_id) {
                $oldApiKey = $device->api_key;
                $device->update([
                    'api_key' => Str::random(40),
                    'name' => $data['name'] ?? $device->name,
                    'location' => $data['location'] ?? $device->location,
                    'status' => 'offline',
                ]);
                
                \Log::info('Device re-provisioned by same user', [
                    'device_id' => $device->id,
                    'user_id' => $pt->user_id,
                    'old_api_key' => substr($oldApiKey, 0, 8) . '...',
                    'new_api_key' => substr($device->api_key, 0, 8) . '...',
                    'token' => $pt->token,
                ]);
            }
            
            // Case 3: Device exists but no owner (orphaned) - CLAIM
            if ($device->user_id === null) {
                $device->update([
                    'user_id' => $pt->user_id,
                    'api_key' => Str::random(40),
                    'name' => $data['name'] ?? $device->name,
                    'location' => $data['location'] ?? $device->location,
                    'status' => 'offline',
                ]);
                
                \Log::info('Orphaned device claimed', [
                    'device_id' => $device->id,
                    'new_user_id' => $pt->user_id,
                    'token' => $pt->token,
                ]);
            }
        }

        // Mark token as claimed
        $pt->update([
            'claimed' => true,
            'claimed_device_id' => $device->id,
            'claimed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Device provisioned successfully',
            'device_id' => $device->id,
            'api_key' => $device->api_key,
            'owner' => $device->user->email ?? 'N/A',
        ]);
    }
}
