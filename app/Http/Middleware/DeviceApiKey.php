<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceApiKey
{
public function handle($request, Closure $next)
{
    $id = $request->header('X-Device-Id');
    $key = $request->header('X-Api-Key');

    if (!$id || !$key) return response()->json(['message'=>'Missing headers'], 401);

    $device = \App\Models\Device::find($id);
    if (!$device || $device->api_key !== $key) {
        return response()->json(['message'=>'Invalid device credentials'], 401);
    }

    $request->attributes->set('device', $device);
    return $next($request);
}

}
