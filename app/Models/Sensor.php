<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $fillable = ['device_id','type','unit','label'];
    public function device() { return $this->belongsTo(Device::class); }
    public function readings(){ return $this->hasMany(SensorReading::class); }
}

