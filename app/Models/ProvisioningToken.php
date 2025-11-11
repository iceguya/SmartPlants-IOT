<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvisioningToken extends Model
{
    protected $fillable = [
        'token','planned_device_id','name_hint','location_hint',
        'expires_at','claimed','claimed_device_id','claimed_at','user_id'
    ];
    protected $casts = ['expires_at'=>'datetime','claimed'=> 'boolean','claimed_at'=>'datetime'];

    public function user() { return $this->belongsTo(User::class); }
}
