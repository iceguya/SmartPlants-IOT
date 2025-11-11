<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    private const DEVICE_STATUS_TTL = 60; // 1 minute
    private const ANALYTICS_TTL = 300; // 5 minutes

    /**
     * Cache device status
     */
    public function cacheDeviceStatus(Device $device, string $status): void
    {
        $key = "device.{$device->id}.status";
        Cache::put($key, $status, self::DEVICE_STATUS_TTL);
    }

    /**
     * Get cached device status
     */
    public function getCachedDeviceStatus(Device $device): ?string
    {
        return Cache::get("device.{$device->id}.status");
    }

    /**
     * Cache analytics data
     */
    public function cacheAnalytics(int $userId, array $data): void
    {
        $key = "analytics.user.{$userId}";
        Cache::put($key, $data, self::ANALYTICS_TTL);
    }

    /**
     * Get cached analytics
     */
    public function getCachedAnalytics(int $userId): ?array
    {
        return Cache::get("analytics.user.{$userId}");
    }

    /**
     * Clear device cache
     */
    public function clearDeviceCache(Device $device): void
    {
        Cache::forget("device.{$device->id}.status");
    }

    /**
     * Clear user analytics cache
     */
    public function clearUserAnalyticsCache(int $userId): void
    {
        Cache::forget("analytics.user.{$userId}");
    }
}
