<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Device;

class ValidateDeviceOwnership
{
    /**
     * Handle an incoming request.
     * Validates that the device making the request belongs to the correct user.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get device credentials from headers
        $deviceId = $request->header('X-Device-Id');
        $apiKey = $request->header('X-Api-Key');

        if (!$deviceId || !$apiKey) {
            \Log::warning('API request missing device credentials', [
                'url' => $request->url(),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'message' => 'Missing device credentials',
                'error' => 'MISSING_CREDENTIALS',
            ], 401);
        }

        // Find device
        $device = Device::find($deviceId);

        if (!$device) {
            \Log::warning('API request from unknown device', [
                'device_id' => $deviceId,
                'url' => $request->url(),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'message' => 'Device not found',
                'error' => 'DEVICE_NOT_FOUND',
            ], 404);
        }

        // Validate API key
        if ($device->api_key !== $apiKey) {
            \Log::warning('API request with invalid API key', [
                'device_id' => $deviceId,
                'url' => $request->url(),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'message' => 'Invalid API key',
                'error' => 'INVALID_API_KEY',
                'hint' => 'API key may have changed. Please re-provision device.',
            ], 403);
        }

        // Check if device is orphaned (no owner)
        if ($device->isOrphaned()) {
            \Log::warning('API request from orphaned device', [
                'device_id' => $deviceId,
                'url' => $request->url(),
            ]);
            
            return response()->json([
                'message' => 'Device has no owner',
                'error' => 'ORPHANED_DEVICE',
                'hint' => 'Device must be claimed by a user before use.',
            ], 403);
        }

        // Attach device to request for use in controllers
        $request->attributes->set('device', $device);

        return $next($request);
    }
}
