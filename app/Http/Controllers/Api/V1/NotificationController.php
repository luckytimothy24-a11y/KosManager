<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return NotificationResource::collection($notifications);
    }

    public function show(Notification $notification): JsonResponse|NotificationResource
    {
        $this->authorize('view', $notification);

        return new NotificationResource($notification);
    }

    public function read(Notification $notification): JsonResponse|NotificationResource
    {
        $this->authorize('update', $notification);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        $notification->refresh();

        return new NotificationResource($notification);
    }
}
