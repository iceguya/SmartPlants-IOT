<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    public $timestamps = false;
    protected $fillable = ['device_id','command','params','status','created_at','sent_at','ack_at'];
    protected $casts = ['params'=>'array','created_at'=>'datetime','sent_at'=>'datetime','ack_at'=>'datetime'];
    public function device() { return $this->belongsTo(Device::class); }
}
