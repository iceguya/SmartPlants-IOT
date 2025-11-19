<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class SensorDataService
{
    /**
     * Available time ranges for filtering
     */
    const TIME_RANGES = [
        '10m' => ['minutes' => 10, 'label' => '10 Minutes'],
        '30m' => ['minutes' => 30, 'label' => '30 Minutes'],
        '1h' => ['hours' => 1, 'label' => '1 Hour'],
        '3h' => ['hours' => 3, 'label' => '3 Hours'],
        '7h' => ['hours' => 7, 'label' => '7 Hours'],
        '24h' => ['hours' => 24, 'label' => '24 Hours'],
        '3d' => ['days' => 3, 'label' => '3 Days'],
        '7d' => ['days' => 7, 'label' => '7 Days'],
        '30d' => ['days' => 30, 'label' => '30 Days'],
    ];

    /**
     * Get Carbon instance for time range start
     */
    public function getStartTime(string $timeRange): Carbon
    {
        $config = self::TIME_RANGES[$timeRange] ?? self::TIME_RANGES['1h'];
        
        if (isset($config['minutes'])) {
            return now()->subMinutes($config['minutes']);
        } elseif (isset($config['hours'])) {
            return now()->subHours($config['hours']);
        } elseif (isset($config['days'])) {
            return now()->subDays($config['days']);
        }
        
        return now()->subHour(); // Default fallback
    }

    /**
     * Determine appropriate data grouping interval based on time range
     */
    public function getGroupingInterval(string $timeRange): string
    {
        return match($timeRange) {
            '10m', '30m' => 'minute',    // Group by minute
            '1h', '3h' => '5minutes',     // Group by 5 minutes
            '7h', '24h' => '30minutes',   // Group by 30 minutes
            '3d', '7d' => 'hour',         // Group by hour
            '30d' => 'day',               // Group by day
            default => '5minutes',
        };
    }

    /**
     * Get pagination limit based on time range
     */
    public function getPaginationLimit(string $timeRange): int
    {
        return match($timeRange) {
            '10m', '30m' => 50,
            '1h', '3h' => 100,
            '7h', '24h' => 200,
            '3d', '7d' => 500,
            '30d' => 1000,
            default => 100,
        };
    }

    /**
     * Prepare chart data from readings with smart aggregation
     */
    public function prepareChartData(Collection $readings, string $timeRange): array
    {
        if ($readings->isEmpty()) {
            return [];
        }

        $interval = $this->getGroupingInterval($timeRange);
        $grouped = [];

        foreach ($readings as $reading) {
            $timestamp = $this->getGroupKey($reading->recorded_at, $interval);
            
            if (!isset($grouped[$timestamp])) {
                $grouped[$timestamp] = [
                    'timestamp' => $timestamp,
                    'sum' => 0,
                    'count' => 0,
                    'min' => $reading->value,
                    'max' => $reading->value,
                ];
            }
            
            $grouped[$timestamp]['sum'] += $reading->value;
            $grouped[$timestamp]['count']++;
            $grouped[$timestamp]['min'] = min($grouped[$timestamp]['min'], $reading->value);
            $grouped[$timestamp]['max'] = max($grouped[$timestamp]['max'], $reading->value);
        }

        // Calculate averages and format for Chart.js
        return collect($grouped)->map(function($data) {
            return [
                'time' => $data['timestamp'],
                'value' => round($data['sum'] / $data['count'], 2),
                'min' => round($data['min'], 2),
                'max' => round($data['max'], 2),
            ];
        })->values()->toArray();
    }

    /**
     * Get grouping key based on interval
     */
    private function getGroupKey(Carbon $timestamp, string $interval): string
    {
        return match($interval) {
            'minute' => $timestamp->format('Y-m-d H:i'),
            '5minutes' => $timestamp->format('Y-m-d H:') . 
                          (floor($timestamp->minute / 5) * 5),
            '30minutes' => $timestamp->format('Y-m-d H:') . 
                           (floor($timestamp->minute / 30) * 30),
            'hour' => $timestamp->format('Y-m-d H:00'),
            'day' => $timestamp->format('Y-m-d'),
            default => $timestamp->format('Y-m-d H:i'),
        };
    }

    /**
     * Format timestamp for display based on time range
     */
    public function formatTimestamp(Carbon $timestamp, string $timeRange): string
    {
        return match($timeRange) {
            '10m', '30m', '1h' => $timestamp->format('H:i'),
            '3h', '7h' => $timestamp->format('H:i'),
            '24h' => $timestamp->format('H:i'),
            '3d', '7d' => $timestamp->format('M d, H:i'),
            '30d' => $timestamp->format('M d'),
            default => $timestamp->format('M d, H:i'),
        };
    }

    /**
     * Get Chart.js configuration for time axis
     */
    public function getTimeAxisConfig(string $timeRange): array
    {
        $format = match($timeRange) {
            '10m', '30m', '1h', '3h', '7h', '24h' => 'HH:mm',
            '3d', '7d' => 'MMM DD, HH:mm',
            '30d' => 'MMM DD',
            default => 'MMM DD, HH:mm',
        };

        return [
            'type' => 'time',
            'time' => [
                'displayFormats' => [
                    'hour' => $format,
                    'minute' => $format,
                    'day' => 'MMM DD',
                ],
                'tooltipFormat' => 'MMM DD, YYYY HH:mm',
            ],
            'title' => [
                'display' => true,
                'text' => 'Time',
            ],
        ];
    }

    /**
     * Validate time range
     */
    public function isValidTimeRange(string $timeRange): bool
    {
        return array_key_exists($timeRange, self::TIME_RANGES);
    }

    /**
     * Get all time ranges for UI
     */
    public function getAllTimeRanges(): array
    {
        return self::TIME_RANGES;
    }
}
