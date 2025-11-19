<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SensorAlert extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $solution;
    public $severity; // 'critical', 'warning', 'info'
    public $icon;
    public $sensorType;
    public $value;
    public $threshold;
    public $deviceName;

    /**
     * Create a new notification instance.
     *
     * @param string $title The alert title
     * @param string $message The alert message
     * @param string $solution The actionable solution/advice
     * @param string $severity Severity level: critical, warning, info
     * @param string $icon Icon type: soil, temperature, health, general
     * @param array $metadata Additional metadata (sensorType, value, threshold, deviceName)
     */
    public function __construct(
        string $title,
        string $message,
        string $solution,
        string $severity = 'warning',
        string $icon = 'general',
        array $metadata = []
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->solution = $solution;
        $this->severity = $severity;
        $this->icon = $icon;
        $this->sensorType = $metadata['sensorType'] ?? null;
        $this->value = $metadata['value'] ?? null;
        $this->threshold = $metadata['threshold'] ?? null;
        $this->deviceName = $metadata['deviceName'] ?? 'Unknown Device';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'solution' => $this->solution,
            'severity' => $this->severity,
            'icon' => $this->icon,
            'sensor_type' => $this->sensorType,
            'value' => $this->value,
            'threshold' => $this->threshold,
            'device_name' => $this->deviceName,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get severity color classes for UI
     */
    public function getSeverityColor(): string
    {
        return match($this->severity) {
            'critical' => 'red',
            'warning' => 'amber',
            'info' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get icon SVG based on type
     */
    public function getIconSvg(): string
    {
        return match($this->icon) {
            'soil' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
            'temperature' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
            'health' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />',
        };
    }
}
