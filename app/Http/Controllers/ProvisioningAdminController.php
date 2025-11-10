<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProvisioningToken;
use Illuminate\Support\Str;

class ProvisioningAdminController extends Controller
{
    public function index()
    {
        $tokens = ProvisioningToken::orderByDesc('id')->limit(20)->get();
        return view('provisioning.index', compact('tokens'));
    }

public function generate(Request $req)
{
    $data = $req->validate([
        'planned_device_id' => 'nullable|string',
        'name_hint'         => 'nullable|string',
        'location_hint'     => 'nullable|string',
        'ttl_hours'         => 'nullable|integer|min:1|max:168',
    ]);

    // pastikan int; jika tidak ada set default 12
    $ttl = isset($data['ttl_hours']) ? (int) $data['ttl_hours'] : 12;

    $token = ProvisioningToken::create([
        'token'             => Str::random(36),
        'planned_device_id' => $data['planned_device_id'] ?? null,
        'name_hint'         => $data['name_hint'] ?? null,
        'location_hint'     => $data['location_hint'] ?? null,
        'expires_at'        => now()->addHours($ttl),
        'claimed'           => false,
    ]);

    return redirect('/provisioning')->with('status','Token dibuat: '.$token->token);
}

}

