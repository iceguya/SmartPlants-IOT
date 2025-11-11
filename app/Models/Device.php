<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Device extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id', 'name', 'location', 'api_key', 'status', 'last_seen', 'user_id'
    ];
    
    protected $casts = [
        'last_seen' => 'datetime',
    ];

    // Relationships
    public function user() { return $this->belongsTo(User::class); }
    public function sensors() { return $this->hasMany(Sensor::class); }
    public function commands() { return $this->hasMany(Command::class); }
    public function automationRules() { return $this->hasMany(AutomationRule::class); }

    // Scopes
    public function scopeForUser(Builder $query, ?int $userId = null): Builder
    {
        return $query->where('user_id', $userId ?? auth()->id());
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('status', 'online')
            ->where('last_seen', '>=', now()->subMinutes(5));
    }

    public function scopeOffline(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->where('status', 'offline')
              ->orWhere('last_seen', '<', now()->subMinutes(5))
              ->orWhereNull('last_seen');
        });
    }

    // Accessors
    public function getIsOnlineAttribute(): bool
    {
        if (!$this->last_seen) {
            return false;
        }
        return $this->last_seen->diffInMinutes(now()) < 5;
    }
}

