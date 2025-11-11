<?php

namespace App\Traits;

trait HasDeviceAuthorization
{
    /**
     * Ensure the device belongs to the authenticated user
     */
    protected function authorizeDevice($device): void
    {
        if ($device->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to device');
        }
    }

    /**
     * Get devices for authenticated user with optional eager loading
     */
    protected function getUserDevices(array $with = [])
    {
        return auth()->user()
            ->devices()
            ->when(!empty($with), fn($q) => $q->with($with))
            ->get();
    }
}
