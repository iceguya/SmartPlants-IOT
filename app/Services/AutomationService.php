<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Command;
use App\Models\AutomationRule;
use App\Models\SensorReading;

class AutomationService
{
    /**
     * Check and trigger automation rules for a device
     * Called after new sensor data is received
     */
    public function checkAndTriggerRules(Device $device)
    {
        $rules = $device->automationRules()->where('enabled', true)->get();
        
        foreach ($rules as $rule) {
            if (!$rule->canTrigger()) {
                continue; // Still in cooldown period
            }

            $shouldTrigger = $this->evaluateCondition($device, $rule);
            
            if ($shouldTrigger) {
                $this->triggerAction($device, $rule);
                $rule->update(['last_triggered_at' => now()]);
            }
        }
    }

    /**
     * Evaluate if rule condition is met
     */
    private function evaluateCondition(Device $device, AutomationRule $rule)
    {
        switch ($rule->condition_type) {
            case 'soil_low':
                return $this->checkSensorValue($device, 'soil', '<', $rule->threshold_value);
            
            case 'soil_high':
                return $this->checkSensorValue($device, 'soil', '>', $rule->threshold_value);
            
            case 'temp_high':
                return $this->checkSensorValue($device, 'temp', '>', $rule->threshold_value);
            
            case 'temp_low':
                return $this->checkSensorValue($device, 'temp', '<', $rule->threshold_value);
            
            case 'hum_low':
                return $this->checkSensorValue($device, 'hum', '<', $rule->threshold_value);
            
            case 'hum_high':
                return $this->checkSensorValue($device, 'hum', '>', $rule->threshold_value);
            
            default:
                return false;
        }
    }

    /**
     * Check latest sensor reading value
     */
    private function checkSensorValue(Device $device, string $sensorType, string $operator, float $threshold)
    {
        $sensor = $device->sensors()->where('type', $sensorType)->first();
        if (!$sensor) return false;

        $latestReading = $sensor->readings()
            ->where('recorded_at', '>=', now()->subMinutes(5))
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (!$latestReading) return false;

        switch ($operator) {
            case '<':
                return $latestReading->value < $threshold;
            case '>':
                return $latestReading->value > $threshold;
            case '<=':
                return $latestReading->value <= $threshold;
            case '>=':
                return $latestReading->value >= $threshold;
            case '==':
                return $latestReading->value == $threshold;
            default:
                return false;
        }
    }

    /**
     * Execute the automation action
     */
    private function triggerAction(Device $device, AutomationRule $rule)
    {
        switch ($rule->action) {
            case 'water_on':
                Command::create([
                    'device_id' => $device->id,
                    'command' => 'water_on',
                    'params' => ['duration_sec' => $rule->action_duration],
                    'status' => 'pending',
                ]);
                
                \Log::info("Automation triggered: water_on for {$device->name} ({$rule->condition_type} = {$rule->threshold_value})");
                break;
        }
    }
}
