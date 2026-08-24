<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('ownedKos', 'bookings');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
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

        return redirect()->route('super-admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('super-admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->only('name', 'email', 'role', 'phone', 'is_active'));

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

        return redirect()->route('super-admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
