<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

/**
 * @deprecated Kept for backward compatibility — delegates to Tenant\TenantCheckInController.
 * New code must use App\Http\Controllers\Tenant\TenantCheckInController.
 */
class TenantCheckInController extends Controller
{
    public function show(Booking $booking)
    {
        return app(\App\Http\Controllers\Tenant\TenantCheckInController::class)->show($booking);
    }

    /**
     * Legacy confirm endpoint: now treated as tenant SUBMIT (pending owner review).
     * Enforced: only tenant can submit, owner cannot self-confirm via this route is blocked elsewhere,
     * but we also guard that tenant is authenticated via policy; if owner hits it they are forbidden
     * by route middleware (tenant group). Kept so old tests that call this route still enter submit flow
     * (now pending, not self-confirmed).
     */
    public function confirm(Request $request, Booking $booking)
    {
        return app(\App\Http\Controllers\Tenant\TenantCheckInController::class)->submit($request, $booking);
    }
}
