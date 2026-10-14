<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        // Identitas diambil dari authenticated API token, bukan dari request body/query.
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'identity_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'role' => ['prohibited'],
            'is_active' => ['prohibited'],
        ]);

        $user = $request->user();
        $user->fill($validated);
        $user->save();

        return response()->json([
            'data' => new UserResource($user->fresh()),
            'message' => 'Profil berhasil diperbarui.',
        ]);
    }
}
