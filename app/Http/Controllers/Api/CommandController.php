<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    public function next(Request $req)
    {
        // Device object is attached by ValidateDeviceOwnership middleware
        $device = $req->attributes->get('device');

        // Additional ownership verification for security
        if ($device->isOrphaned()) {
            \Log::warning('Command polling from orphaned device', [
                'device_id' => $device->id,
            ]);
            
            return response()->json([
                'message' => 'Device has no owner',
                'error' => 'ORPHANED_DEVICE',
            ], 403);
        }

        \Log::info('Command polling from device', [
            'device_id' => $device->id,
            'owner_id' => $device->user_id,
        ]);

        // Only fetch commands for this specific device
        $cmd = \App\Models\Command::where('device_id', $device->id)
            ->where('status', 'pending')
            ->orderBy('id')
            ->first();

        if (!$cmd) {
            \Log::info('No pending commands', ['device_id' => $device->id]);
            return response()->json(['command' => null]);
        }

        \Log::info('Sending command to device', [
            'device_id' => $device->id,
            'command_id' => $cmd->id,
            'command' => $cmd->command,
            'params' => $cmd->params,
        ]);

        $cmd->update(['status' => 'sent', 'sent_at' => now()]);
        
        return response()->json([
            'id' => $cmd->id,
            'command' => $cmd->command,
            'params' => $cmd->params,
        ]);
    }

    public function ack(Request $req, $id)
    {
        // Device object is attached by ValidateDeviceOwnership middleware
        $device = $req->attributes->get('device');
        
        // Only allow ACK for commands belonging to this device
        $cmd = \App\Models\Command::where('device_id', $device->id)->findOrFail($id);
        
        $cmd->update(['status' => 'ack', 'ack_at' => now()]);
        
        \Log::info('Command acknowledged', [
            'device_id' => $device->id,
            'command_id' => $cmd->id,
            'command' => $cmd->command,
        ]);
        
        return response()->json(['message' => 'ACK received']);
    }

}
