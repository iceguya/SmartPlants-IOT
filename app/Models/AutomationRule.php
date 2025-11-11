<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    protected $fillable = [
        'device_id', 'enabled', 'condition_type', 'threshold_value',
        'action', 'action_duration', 'cooldown_minutes', 'last_triggered_at'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'threshold_value' => 'decimal:2',
        'last_triggered_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function canTrigger()
    {
        if (!$this->enabled) return false;
        if (!$this->last_triggered_at) return true;
        
        $cooldownEnds = $this->last_triggered_at->addMinutes($this->cooldown_minutes);
        return now()->greaterThan($cooldownEnds);
    }
}
