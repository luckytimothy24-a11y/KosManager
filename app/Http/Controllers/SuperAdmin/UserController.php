<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('ownedKos', 'bookings');

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
        return view('super-admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
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

    public function edit(User $user)
    {
        return view('super-admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->only('name', 'email', 'role', 'phone', 'is_active');
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

    public function destroy(User $user)
    {
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
