<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    public function next(Request $req)
{
    $device = $req->attributes->get('device');

    $cmd = \App\Models\Command::where('device_id',$device->id)
        ->where('status','pending')->orderBy('id')->first();

    if (!$cmd) return response()->json(['command'=>null]);

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
