<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Collection;

class DeviceStatusService
{
    private const ONLINE_THRESHOLD_MINUTES = 5;

    /**
     * Update status for multiple devices efficiently
     */
    public function updateDevicesStatus(Collection $devices): void
    {
        $devices->each(fn($device) => $this->updateDeviceStatus($device));
    }

    /**
     * Update single device status
     */
    public function updateDeviceStatus(Device $device): void
    {
        $newStatus = $this->determineStatus($device);
        
        if ($device->status !== $newStatus) {
            $device->update(['status' => $newStatus]);
        }
    }

    /**
     * Determine device status based on last_seen
     */
    private function determineStatus(Device $device): string
    {
        if (!$device->last_seen) {
            return 'offline';
        }

        $minutesAgo = $device->last_seen->diffInMinutes(now());
        return $minutesAgo < self::ONLINE_THRESHOLD_MINUTES ? 'online' : 'offline';
    }

    /**
     * Check if device is online
     */
    public function isOnline(Device $device): bool
    {
        return $this->determineStatus($device) === 'online';
    }
}
