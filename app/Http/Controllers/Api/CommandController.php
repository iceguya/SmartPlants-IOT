<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    public function next(Request $req)
{
    $device = $req->attributes->get('device');

    \Log::info('Command polling from device', ['device_id' => $device->id]);

    $cmd = \App\Models\Command::where('device_id',$device->id)
        ->where('status','pending')->orderBy('id')->first();

    if (!$cmd) {
        \Log::info('No pending commands', ['device_id' => $device->id]);
        return response()->json(['command'=>null]);
    }

    \Log::info('Sending command to device', [
        'device_id' => $device->id,
        'command_id' => $cmd->id,
        'command' => $cmd->command,
        'params' => $cmd->params
    ]);

    $cmd->update(['status'=>'sent','sent_at'=>now()]);
    return response()->json([
        'id'      => $cmd->id,
        'command' => $cmd->command,
        'params'  => $cmd->params,
    ]);
}

public function ack(Request $req, $id)
{
    $device = $req->attributes->get('device');
    $cmd = \App\Models\Command::where('device_id',$device->id)->findOrFail($id);
    $cmd->update(['status'=>'ack','ack_at'=>now()]);
    return response()->json(['message'=>'ACK received']);
}

}
