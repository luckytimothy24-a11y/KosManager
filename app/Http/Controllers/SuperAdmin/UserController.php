<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $query = User::withCount('ownedKos', 'bookings');

        if ($request->user()->isAdmin()) {
            $kosIds = $request->user()->assignedKos()->pluck('kos.id');
            if ($kosIds->isEmpty()) {
                $query->where('id', -1);
            } else {
                $query->where(function ($q) use ($kosIds) {
                    $q->where(function ($qq) use ($kosIds) {
                        $qq->where('role', 'owner')
                            ->whereHas('ownedKos', fn ($oq) => $oq->whereIn('id', $kosIds));
                    })->orWhere(function ($qq) use ($kosIds) {
                        $qq->where('role', 'tenant')
                            ->whereHas('penghunis', fn ($pq) => $pq->whereIn('kos_id', $kosIds));
                    })->orWhere(function ($qq) use ($kosIds) {
                        $qq->where('role', 'admin')
                            ->whereHas('assignedKos', fn ($aq) => $aq->whereIn('kos.id', $kosIds));
                    });
                });
            }
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('super-admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('super-admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
        ]);

        AuditLogService::create('User', "Membuat user {$request->name} ({$request->role})");

        return redirect()->route('super-admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        $user->load('ownedKos', 'bookings', 'penghunis');

        return view('super-admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('super-admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $data = $request->only('name', 'email', 'role', 'phone', 'is_active');

        // Defense in depth: only a super admin may change role or active
        // status through this endpoint — even if this action is ever reached
        // by another role (e.g. self-profile update), escalation is impossible.
        if (! auth()->user()->isSuperAdmin()) {
            unset($data['role'], $data['is_active']);
        }

        $data['is_active'] = filter_var($data['is_active'] ?? $user->is_active, FILTER_VALIDATE_BOOLEAN);

        if ($user->id === auth()->id()) {
            if (($data['role'] ?? $user->role) !== 'super_admin') {
                abort(400, 'Anda tidak dapat mengubah role akun Anda sendiri dari Super Admin.');
            }
            if (! $data['is_active']) {
                abort(400, 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
        }

        if ($user->role === 'super_admin') {
            $remainingSuperAdmins = User::where('role', 'super_admin')
                ->where('id', '!=', $user->id)
                ->count();
            if (($data['role'] ?? $user->role) !== 'super_admin' && $remainingSuperAdmins < 1) {
                abort(400, 'Tidak dapat mengubah role super admin terakhir.');
            }
            if ($user->is_active && ! $data['is_active'] && $remainingSuperAdmins < 1) {
                abort(400, 'Tidak dapat menonaktifkan super admin terakhir.');
            }
        }

        $user->update($data);

        AuditLogService::update('User', "Memperbarui user {$user->name}");

        return redirect()->route('super-admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Aktif/nonaktif cepat tanpa hapus (mis. suspend tenant abuser).
     * Larangan: diri sendiri & super admin terakhir.
     */
    public function toggleActive(User $user)
    {
        $this->authorize('update', $user);
        abort_if($user->id === auth()->id(), 400, 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

        if ($user->role === 'super_admin' && $user->is_active) {
            $remaining = User::where('role', 'super_admin')->where('id', '!=', $user->id)->where('is_active', true)->count();
            abort_if($remaining < 1, 400, 'Tidak dapat menonaktifkan super admin terakhir.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        AuditLogService::update('User', ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan')." user {$user->name}");

        return back()->with('success', $user->is_active ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.');
    }

    /**
     * Reset kata sandi: generate acak sekali tampil (tanpa email/SMTP).
     * Password baru HANYA muncul di flash sesi ini — tidak masuk log/audit.
     */
    public function resetPassword(User $user)
    {
        $this->authorize('update', $user);

        $plain = Str::random(12);
        $user->update(['password' => Hash::make($plain)]);

        AuditLogService::update('User', "Mereset kata sandi user {$user->name}");

        return back()->with('new_password', $plain)
            ->with('success', "Kata sandi {$user->name} berhasil direset. Catat password baru ini — hanya tampil sekali.");
    }

    /**
     * Export CSV mengikuti filter search/role yang aktif.
     */
    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();
        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $filename = 'users-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Nama', 'Email', 'Role', 'Telepon', 'Aktif', 'Terdaftar']);
            $query->orderBy('id')->chunk(500, function ($users) use ($handle) {
                foreach ($users as $u) {
                    fputcsv($handle, [
                        $u->id, $u->name, $u->email, $u->role, $u->phone,
                        $u->is_active ? 'Ya' : 'Tidak',
                        $u->created_at?->format('Y-m-d H:i'),
                    ]);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        abort_if($user->id === auth()->id(), 400, 'Anda tidak dapat menghapus akun Anda sendiri.');

        if ($user->role === 'super_admin') {
            $superAdminCount = User::where('role', 'super_admin')->count();
            abort_if($superAdminCount <= 1, 400, 'Tidak dapat menghapus super admin terakhir.');
        }

        if ($user->role === 'owner' && $user->ownedKos()->exists()) {
            return redirect()->route('super-admin.users.index')
                ->with('error', 'User ini masih memiliki kos. Hapus atau alihkan kos terlebih dahulu.');
        }

        if ($user->bookings()->exists() || $user->penghunis()->exists()) {
            return redirect()->route('super-admin.users.index')
                ->with('error', 'User ini memiliki riwayat booking atau penghuni sehingga tidak dapat dihapus agar histori tagihan dan pembayaran tetap utuh. Nonaktifkan akun melalui menu edit sebagai gantinya.');
        }

        $user->delete();

        AuditLogService::delete('User', "Menghapus user {$user->name} (soft delete)");

        return redirect()->route('super-admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
