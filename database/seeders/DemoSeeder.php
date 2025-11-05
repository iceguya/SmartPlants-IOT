<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
public function run(): void
{
    // Device demo untuk uji API ingest manual
    $device = \App\Models\Device::create([
        'id' => 'esp-plant-01',
        'name' => 'ESP Tanaman A',
        'location' => 'Greenhouse #1',
        'api_key' => Str::random(40),
        'status' => 'offline',
    ]);

    $sensorTypes = [
        ['type'=>'soil','unit'=>'%','label'=>'Soil moisture'],
        ['type'=>'temp','unit'=>'C','label'=>'Air temperature'],
        ['type'=>'hum','unit'=>'%','label'=>'Air humidity'],
        ['type'=>'color_r','unit'=>'au','label'=>'Leaf R'],
        ['type'=>'color_g','unit'=>'au','label'=>'Leaf G'],
        ['type'=>'color_b','unit'=>'au','label'=>'Leaf B'],
    ];
    foreach ($sensorTypes as $s) {
        \App\Models\Sensor::create($s + ['device_id'=>$device->id]);
    }

    // Token provisioning contoh
    \App\Models\ProvisioningToken::create([
        'token' => Str::random(36),
        'planned_device_id' => 'esp-plant-02',
        'name_hint' => 'ESP Tanaman B',
        'location_hint' => 'Greenhouse #2',
        'expires_at' => now()->addHours(12),
        'claimed' => false,
    ]);
}
}
