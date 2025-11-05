<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','name','location','api_key','status','last_seen'];

    public function sensors() { return $this->hasMany(Sensor::class); }
    public function commands(){ return $this->hasMany(Command::class); }
}

