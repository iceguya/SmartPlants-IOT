<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProvisioningToken;
use Illuminate\Support\Str;

class ProvisioningAdminController extends Controller
{
    public function index()
    {
        $tokens = ProvisioningToken::where('user_id', auth()->id())
            ->orderByDesc('id')
            ->limit(20)
            ->get();
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
        'user_id'           => auth()->id(),
    ]);

    return redirect('/provisioning')->with('status','Token dibuat: '.$token->token);
}

    /**
     * Delete a provisioning token by id.
     * If token is claimed, also delete the associated device and all its data.
     */
    public function destroy($id)
    {
        $token = ProvisioningToken::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
        if (!$token) {
            return redirect('/provisioning')->with('status', 'Token not found or unauthorized.');
        }

        $message = 'Token dihapus: '.$token->token;

        // Jika token sudah claimed, hapus device dan semua data terkait
        if ($token->claimed && $token->claimed_device_id) {
            $device = \App\Models\Device::find($token->claimed_device_id);
            if ($device) {
                // Laravel akan otomatis cascade delete sensors, readings, commands
                // karena foreign key constraint di migration
                $device->delete();
                $message = 'Token dan device "'.$device->name.'" (ID: '.$device->id.') beserta semua data sensor telah dihapus.';
            }
        }

        $token->delete();

        return redirect('/provisioning')->with('status', $message);
    }

}

