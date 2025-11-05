<?php
namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    // ambil ringkasan per sensor untuk 24 jam terakhir
    public function summariesForDevice(Device $device)
    {
        // ambil id sensor per type
        $map = $device->sensors()->pluck('id','type'); // ['soil'=>3, 'temp'=>4, ...]

        $since = now()->subHours(24);
        $out = [];

        foreach (['soil','temp','hum','color_r','color_g','color_b'] as $type) {
            $sid = $map[$type] ?? null;
            if (!$sid) { $out[$type] = null; continue; }

            $rows = DB::table('sensor_readings')
                ->selectRaw('MIN(value) as min_v, MAX(value) as max_v, AVG(value) as avg_v')
                ->where('sensor_id',$sid)
                ->where('recorded_at','>=',$since)
                ->first();

            $last = DB::table('sensor_readings')
                ->select('value','recorded_at')
                ->where('sensor_id',$sid)
                ->orderByDesc('recorded_at')
                ->first();

            $out[$type] = [
                'min' => $rows? (float)$rows->min_v : null,
                'max' => $rows? (float)$rows->max_v : null,
                'avg' => $rows? round((float)$rows->avg_v,2) : null,
                'last'=> $last? (float)$last->value : null,
                'last_at'=> $last? $last->recorded_at : null,
            ];
        }

        // indeks kehijauan sederhana: G / (R+G+B)
        $g = $out['color_g']['last'] ?? null;
        $r = $out['color_r']['last'] ?? null;
        $b = $out['color_b']['last'] ?? null;
        $green_index = null;
        if ($r !== null && $g !== null && $b !== null) {
            $sum = $r + $g + $b;
            if ($sum > 0) $green_index = round($g / $sum, 3);
        }

        // deteksi sederhana
        $alerts = [];
        if (($out['soil']['last'] ?? 100) < 35) $alerts[] = 'Tanah kering (soil < 35%)';
        if (($out['temp']['last'] ?? 0) > 32)  $alerts[] = 'Suhu tinggi (temp > 32°C)';
        if (($out['hum']['last']  ?? 0) > 85)  $alerts[] = 'Kelembapan udara tinggi (hum > 85%)';

        return [
            'since' => $since,
            'metrics' => $out,
            'green_index' => $green_index,
            'alerts' => $alerts,
        ];
    }
}
