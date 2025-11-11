<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Command;
use App\Models\AutomationRule;
use Illuminate\Support\Facades\Log;

class AutomationService
{
    private const SENSOR_READING_WINDOW_MINUTES = 5;

    /**
     * Check and trigger automation rules for a device
     */
    public function checkAndTriggerRules(Device $device): void
    {
        $device->automationRules()
            ->where('enabled', true)
            ->get()
            ->filter(fn($rule) => $rule->canTrigger())
            ->filter(fn($rule) => $this->evaluateCondition($device, $rule))
            ->each(fn($rule) => $this->executeRule($device, $rule));
    }

    /**
     * Evaluate if rule condition is met
     */
    private function evaluateCondition(Device $device, AutomationRule $rule): bool
    {
        [$sensorType, $operator] = $this->parseConditionType($rule->condition_type);
        
        if (!$sensorType) {
            return false;
        }

        return $this->checkSensorValue($device, $sensorType, $operator, $rule->threshold_value);
    }

    /**
     * Parse condition type into sensor type and operator
     */
    private function parseConditionType(string $conditionType): array
    {
        $conditions = [
            'soil_low' => ['soil', '<'],
            'soil_high' => ['soil', '>'],
            'temp_low' => ['temp', '<'],
            'temp_high' => ['temp', '>'],
            'hum_low' => ['hum', '<'],
            'hum_high' => ['hum', '>'],
        ];

        return $conditions[$conditionType] ?? [null, null];
    }

    /**
     * Check latest sensor reading value
     */
    private function checkSensorValue(Device $device, string $sensorType, string $operator, float $threshold): bool
    {
        $latestValue = $device->sensors()
            ->where('type', $sensorType)
            ->first()
            ?->readings()
            ->where('recorded_at', '>=', now()->subMinutes(self::SENSOR_READING_WINDOW_MINUTES))
            ->latest('recorded_at')
            ->value('value');

        if ($latestValue === null) {
            return false;
        }

        return match($operator) {
            '<' => $latestValue < $threshold,
            '>' => $latestValue > $threshold,
            '<=' => $latestValue <= $threshold,
            '>=' => $latestValue >= $threshold,
            '==' => $latestValue == $threshold,
            default => false,
        };
    }

    /**
     * Execute the automation rule
     */
    private function executeRule(Device $device, AutomationRule $rule): void
    {
        Command::create([
            'device_id' => $device->id,
            'command' => $rule->action,
            'params' => ['duration_sec' => $rule->action_duration],
            'status' => 'pending',
        ]);

        $rule->update(['last_triggered_at' => now()]);

        Log::info("Automation triggered", [
            'device' => $device->name,
            'rule' => $rule->condition_type,
            'threshold' => $rule->threshold_value,
            'action' => $rule->action,
        ]);
    }
}
