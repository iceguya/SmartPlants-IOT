<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function latest(Request $req)
{
    $id = $req->query('sensor_id');
    $s = \App\Models\Sensor::findOrFail($id);
    $r = $s->readings()->orderByDesc('recorded_at')->first();
    if (!$r) return response()->json([], 204);

    return response()->json([
        'sensor_id' => $s->id,
        'value' => (float)$r->value,
        'time'  => $r->recorded_at->format('H:i'),
    ]);
}

}
