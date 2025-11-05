<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\Command as DeviceCommand;

class CheckSoilAndQueueWater extends Command
{
    protected $signature = 'plants:check-soil';
    protected $description = 'Queue water_on when soil < 35%';

    public function handle()
    {
        $devices = Device::with(['sensors' => function($q){
            $q->where('type','soil');
        }])->get();

        foreach ($devices as $d) {
            $soil = $d->sensors->first();
            if (!$soil) continue;

            $recent = $soil->readings()
               ->where('recorded_at','>=',now()->subMinutes(5))
               ->orderByDesc('recorded_at')->first();

            if ($recent && (float)$recent->value < 35.0) {
                DeviceCommand::create([
                  'device_id'=>$d->id,
                  'command'=>'water_on',
                  'params'=>['duration_sec'=>5]
                ]);
                $this->info("queued water_on for {$d->id}");
            }
        }
    }
}
