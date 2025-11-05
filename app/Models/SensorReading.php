<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = ['sensor_id','value','recorded_at'];
    protected $casts = ['recorded_at'=>'datetime'];
    public function sensor() { return $this->belongsTo(Sensor::class); }
}
