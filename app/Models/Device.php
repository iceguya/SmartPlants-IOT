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

    // Ownership Management Methods
    
    /**
     * Check if device is owned by a specific user
     */
    public function isOwnedBy(?int $userId): bool
    {
        if ($this->user_id === null) {
            return false; // Orphaned device
        }
        return $this->user_id === $userId;
    }

    /**
     * Check if device can be claimed by a user
     */
    public function canBeClaimedBy(int $userId): bool
    {
        // Device can be claimed if:
        // 1. It has no owner (orphaned), OR
        // 2. It's already owned by the same user (re-provision)
        return $this->user_id === null || $this->user_id === $userId;
    }

    /**
     * Check if device is orphaned (no owner)
     */
    public function isOrphaned(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Reassign device to a new user (admin only)
     * This should only be used by admins for manual transfers
     */
    public function reassignTo(int $newUserId, string $reason = null): bool
    {
        $oldUserId = $this->user_id;
        
        $this->update([
            'user_id' => $newUserId,
            'api_key' => \Illuminate\Support\Str::random(40), // New API key for security
        ]);

        \Log::info('Device ownership transferred', [
            'device_id' => $this->id,
            'old_user_id' => $oldUserId,
            'new_user_id' => $newUserId,
            'reason' => $reason ?? 'Manual transfer',
        ]);

        return true;
    }

    /**
     * Release device ownership (make it orphaned)
     */
    public function releaseOwnership(string $reason = null): bool
    {
        $oldUserId = $this->user_id;
        
        $this->update([
            'user_id' => null,
            'status' => 'offline',
        ]);

        \Log::info('Device ownership released', [
            'device_id' => $this->id,
            'old_user_id' => $oldUserId,
            'reason' => $reason ?? 'Manual release',
        ]);

        return true;
    }
}

