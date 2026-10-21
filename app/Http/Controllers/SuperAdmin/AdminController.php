<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignAdminRequest;
use App\Http\Requests\CreateAdminRequest;
use App\Models\Kos;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::where('role', UserRole::ADMIN->value)
            ->withCount('assignedKos')
            ->with(['assignedKos' => fn ($q) => $q->select('kos.id', 'kos.name', 'kos.status')]);

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('has_assignment')) {
            $query->has('assignedKos');
        }

        $admins = $query->latest()->paginate(15)->withQueryString();

        return view('super-admin.admins.index', compact('admins'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('super-admin.admins.create');
    }

    public function store(CreateAdminRequest $request)
    {
        $this->authorize('create', User::class);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::ADMIN->value,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        AuditLogService::log('Create', 'Admin', "Admin {$user->name} dibuat", data: ['user_id' => $user->id]);

        return redirect()->route('super-admin.admins.index')->with('success', 'Admin berhasil dibuat.');
    }

    public function show(User $admin)
    {
        $this->authorize('view', $admin);

        $admin->load([
            'assignedKos' => fn ($q) => $q->withCount('kamar'),
            'assignedKos.kamar',
        ]);

        $assignedKosCount = $admin->assignedKos()->count();
        $kamarCount = $admin->assignedKos->sum(fn ($kos) => $kos->kamar->count());

        return view('super-admin.admins.show', ['user' => $admin, 'assignedKosCount' => $assignedKosCount, 'kamarCount' => $kamarCount]);
    }

    public function activate(User $admin)
    {
        $this->authorize('toggleActive', $admin);

        abort_if($admin->is_active, 400, 'Admin sudah aktif.');

        $admin->update(['is_active' => true]);

        AuditLogService::log('Activate', 'Admin', "Admin {$admin->name} diaktifkan", data: ['user_id' => $admin->id]);

        return redirect()->route('super-admin.admins.index')->with('success', "Admin {$admin->name} berhasil diaktifkan.");
    }

    public function deactivate(User $admin)
    {
        $this->authorize('toggleActive', $admin);

        abort_if(! $admin->is_active, 400, 'Admin sudah nonaktif.');

        $admin->update(['is_active' => false]);

        AuditLogService::log('Deactivate', 'Admin', "Admin {$admin->name} dinonaktifkan", data: ['user_id' => $admin->id]);

        return redirect()->route('super-admin.admins.index')->with('success', "Admin {$admin->name} berhasil dinonaktifkan.");
    }

    public function assign(AssignAdminRequest $request, User $admin)
    {
        $this->authorize('manageAssignment', $admin);

        abort_if(! $admin->isAdmin(), 404);
        abort_if(! $admin->is_active, 400, 'Admin harus aktif untuk menerima assignment.');

        $kos = Kos::whereKey($request->kos_id)->firstOrFail();

        if ($admin->assignedKos()->where('kos_id', $kos->id)->exists()) {
            return back()->with('error', 'Admin sudah ditugaskan ke kos ini.');
        }

        $admin->assignedKos()->attach($kos->id);

        AuditLogService::log('Assign', 'Admin', "Admin {$admin->name} ditugaskan ke kos {$kos->name}", data: ['user_id' => $admin->id, 'kos_id' => $kos->id]);

        return back()->with('success', 'Admin berhasil ditugaskan ke kos.');
    }

    public function unassign(Request $request, User $admin, Kos $kos)
    {
        $this->authorize('manageAssignment', $admin);

        abort_if(! $admin->isAdmin(), 404);

        $admin->assignedKos()->detach($kos->id);

        AuditLogService::log('Unassign', 'Admin', "Admin {$admin->name} dilepas dari kos {$kos->name}", data: ['user_id' => $admin->id, 'kos_id' => $kos->id]);

        return back()->with('success', 'Admin berhasil dilepas dari kos.');
    }
}
