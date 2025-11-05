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
        'device_id' => 'required|string', // UID dari ESP, atau nama unik
        'name' => 'nullable|string',
        'location' => 'nullable|string',
    ]);

    $pt = \App\Models\ProvisioningToken::where('token',$data['token'])->first();
    if (!$pt) return response()->json(['message'=>'Invalid token'], 404);
    if ($pt->claimed) return response()->json(['message'=>'Token already claimed'], 409);
    if ($pt->expires_at->isPast()) return response()->json(['message'=>'Token expired'], 410);

    $device = \App\Models\Device::find($data['device_id']);
    if (!$device) {
        $device = \App\Models\Device::create([
            'id' => $data['device_id'],
            'name' => $data['name'] ?? $pt->name_hint ?? $data['device_id'],
            'location' => $data['location'] ?? $pt->location_hint,
            'api_key' => Str::random(40),
            'status' => 'offline',
        ]);

        // siapkan sensors default sesuai kebutuhan
        $defs = [
            ['type'=>'soil','unit'=>'%','label'=>'Soil moisture'],
            ['type'=>'temp','unit'=>'C','label'=>'Air temperature'],
            ['type'=>'hum','unit'=>'%','label'=>'Air humidity'],
            ['type'=>'color_r','unit'=>'au','label'=>'Leaf R'],
            ['type'=>'color_g','unit'=>'au','label'=>'Leaf G'],
            ['type'=>'color_b','unit'=>'au','label'=>'Leaf B'],
        ];
        foreach ($defs as $s) \App\Models\Sensor::firstOrCreate($s+['device_id'=>$device->id], $s);
    }

    $pt->update([
        'claimed' => true,
        'claimed_device_id' => $device->id,
        'claimed_at' => now(),
    ]);

    return response()->json([
        'message' => 'Provisioned',
        'device_id' => $device->id,
        'api_key' => $device->api_key,
    ]);
}
}
