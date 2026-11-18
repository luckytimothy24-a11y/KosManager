<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'push_token' => ['required', 'string', 'max:500'],
            'device_type' => ['nullable', 'string', 'in:web,android,ios'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'subscription_data' => ['nullable', 'array'],
        ]);

        $device = UserDevice::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'push_token' => $validated['push_token'],
            ],
            [
                'device_type' => $validated['device_type'] ?? 'web',
                'device_name' => $validated['device_name'] ?? null,
                'subscription_data' => $validated['subscription_data'] ?? null,
                'user_agent' => $request->userAgent(),
                'last_active_at' => now(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'device_id' => $device->id,
        ]);
    }

    public function unregister(Request $request)
    {
        $validated = $request->validate([
            'push_token' => ['required', 'string', 'max:500'],
        ]);

        UserDevice::where('user_id', $request->user()->id)
            ->where('push_token', $validated['push_token'])
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Device unregistered successfully',
        ]);
    }
}
