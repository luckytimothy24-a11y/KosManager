<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isTenant()) {
            $orders = WorkOrder::with(['kos', 'kamar', 'technician'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);

            return view('owner.work-orders.index', compact('orders'));
        }

        if ($user->isTechnician()) {
            $orders = WorkOrder::with(['kos', 'kamar', 'reporter'])
                ->where('assigned_technician_id', $user->id)
                ->latest()
                ->paginate(10);

            return view('owner.work-orders.index', compact('orders'));
        }

        $this->authorize('viewAny', WorkOrder::class);

        $kosIds = $user->isSuperAdmin()
            ? null
            : ($user->isAdmin()
                ? $user->assignedKos()->pluck('kos.id')->all()
                : Kos::where('owner_id', $user->id)->pluck('id')->all());

        $query = WorkOrder::with(['reporter', 'kos', 'kamar', 'technician'])
            ->when($kosIds !== null, fn ($q) => $q->whereIn('kos_id', $kosIds))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest();

        $orders = $query->paginate(20);

        $technicians = User::where('role', 'technician')->where('is_active', true)->orderBy('name')->get();

        return view('owner.work-orders.index', compact('orders', 'kosIds', 'technicians'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', WorkOrder::class);

        $user = $request->user();

        $stays = Penghuni::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['kos', 'kamar'])
            ->get();

        return view('owner.work-orders.create', compact('stays'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', WorkOrder::class);

        $validated = $request->validate([
            'kos_id' => ['required', 'integer', 'exists:kos,id'],
            'kamar_id' => ['nullable', 'integer', 'exists:kamar,id'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'legacy_damage_report_id' => ['nullable', 'integer', 'exists:damage_reports,id'],
            'legacy_dispute_id' => ['nullable', 'integer', 'exists:disputes,id'],
        ]);

        $user = $request->user();

        $penghuni = Penghuni::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('kos_id', (int) $validated['kos_id'])
            ->first();

        abort_unless($penghuni, 403, 'Anda tidak memiliki hunian aktif di kos ini.');

        $order = WorkOrder::create([
            'user_id' => $user->id,
            'kos_id' => (int) $validated['kos_id'],
            'kamar_id' => $validated['kamar_id'] ?? $penghuni->kamar_id,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? WorkOrder::PRIORITY_NORMAL,
            'status' => WorkOrder::STATUS_PENDING,
            'legacy_damage_report_id' => $validated['legacy_damage_report_id'] ?? null,
            'legacy_dispute_id' => $validated['legacy_dispute_id'] ?? null,
        ]);

        return redirect()->route('tenant.work-orders.show', $order)
            ->with('success', 'Work order berhasil dibuat.');
    }

    public function show(WorkOrder $workOrder)
    {
        $this->authorize('view', $workOrder);

        $workOrder->load(['reporter', 'kos', 'kamar', 'technician', 'legacyDamageReport', 'legacyDispute']);

        if (request()->user()->isTenant()) {
            return view('owner.work-orders.show', compact('workOrder'));
        }

        if (request()->user()->isTechnician()) {
            return view('owner.work-orders.show', compact('workOrder'));
        }

        return view('owner.work-orders.show', compact('workOrder'));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $this->authorize('update', $workOrder);

        $user = $request->user();

        // Teknisi hanya boleh update status (in_progress/resolved) miliknya.
        if ($user->isTechnician()) {
            $request->validate([
                'status' => 'required|in:in_progress,resolved',
                'resolution' => 'nullable|string|max:2000',
            ]);

            $workOrder->update([
                'status' => $request->status,
                'resolution' => $request->resolution,
                'resolved_at' => $request->status === WorkOrder::STATUS_RESOLVED ? now() : null,
            ]);

            return redirect()->route('owner.work-orders.show', $workOrder)
                ->with('success', 'Status work order diperbarui.');
        }

        // Admin/owner/super_admin
        $this->authorize('update', $workOrder);

        $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,resolved,closed',
            'assigned_technician_id' => 'nullable|integer|exists:users,id',
            'resolution' => 'nullable|string|max:2000',
        ]);

        $workOrder->update([
            'status' => $request->status,
            'assigned_technician_id' => $request->assigned_technician_id,
            'resolution' => $request->resolution,
            'resolved_at' => in_array($request->status, [WorkOrder::STATUS_RESOLVED, WorkOrder::STATUS_CLOSED]) ? now() : null,
        ]);

        return redirect()->route('owner.work-orders.index')
            ->with('success', 'Work order diperbarui.');
    }

    public function destroy(WorkOrder $workOrder)
    {
        $this->authorize('delete', $workOrder);

        $workOrder->delete();

        return redirect()->route('owner.work-orders.index')
            ->with('success', 'Work order dihapus.');
    }
}
