<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('update', $user);

        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('delete', $user);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        if ($user->isOwner() && $user->ownedKos()->exists()) {
            return Redirect::route('profile.edit')->with('error', 'Akun tidak dapat dihapus karena masih memiliki kos. Alihkan atau hapus kos terlebih dahulu.');
        }

        if ($user->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            return Redirect::route('profile.edit')->with('error', 'Akun tidak dapat dihapus karena Anda super admin satu-satunya.');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
