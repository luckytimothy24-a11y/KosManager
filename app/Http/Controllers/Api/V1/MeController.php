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
}
