<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKosRequest;
use App\Http\Requests\UpdateKosRequest;
use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KosController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $this->authorize('viewAny', Kos::class);

        $query = Kos::withCount('kamar', 'penghunis');

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereIn('id', $kosIds);
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kos = $query->latest()->paginate(10)->withQueryString();

        return view('owner.kos.index', compact('kos'));
    }

    public function create()
    {
        $fasilitasList = Fasilitas::ofType('kos')->active()->get();

        $owners = ! $this->authUserIsOwner()
            ? User::where('role', 'owner')->orderBy('name')->get()
            : collect();

        return view('owner.kos.create', compact('fasilitasList', 'owners'));
    }

    public function store(StoreKosRequest $request)
    {
        $data = $request->validated();
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        // Extract gallery fields (not columns)
        $galleryFiles = $request->file('photos', []);
        if (! is_array($galleryFiles)) {
            $galleryFiles = $galleryFiles ? [$galleryFiles] : [];
        }
        $coverIndex = $request->input('cover_index');
        unset($data['photos'], $data['cover_index']);

        if ($request->user()->isOwner()) {
            $data['owner_id'] = $request->user()->id;
        } else {
            $request->validate(['owner_id' => ['required', Rule::exists('users', 'id')->where('role', 'owner')]]);
            $data['owner_id'] = $request->input('owner_id');
        }

        // Legacy single photo backward compat
        $legacyPhotoStored = false;
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('kos', 'public');
            $legacyPhotoStored = true;
        }

        $storedPaths = [];
        // Track legacy photo path for rollback if needed
        $legacyPath = $legacyPhotoStored ? $data['photo'] : null;

        DB::beginTransaction();

        try {
            $kos = Kos::create($data);
            $kos->fasilitas()->sync($fasilitasIds);

            if (! $request->user()->isAdmin()) {
                $adminIds = $request->input('admins', []);
                if (is_array($adminIds)) {
                    $kos->admins()->sync(array_filter($adminIds));
                }
            }

            // Gallery: store each uploaded photo
            if (! empty($galleryFiles)) {
                foreach ($galleryFiles as $idx => $file) {
                    if (! $file || ! $file->isValid()) {
                        continue;
                    }
                    $path = $file->store("kos/{$kos->id}", 'public');
                    $storedPaths[] = $path;

                    $isCover = false;
                    if ($coverIndex !== null && $coverIndex !== '') {
                        $isCover = ((int) $coverIndex === $idx);
                    } else {
                        $isCover = ($idx === 0);
                    }

                    KosPhoto::create([
                        'kos_id' => $kos->id,
                        'path' => $path,
                        'position' => $idx,
                        'is_cover' => $isCover,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            foreach ($storedPaths as $p) {
                try {
                    Storage::disk('public')->delete($p);
                } catch (\Throwable $ex) {
                }
            }

            if ($legacyPath) {
                try {
                    Storage::disk('public')->delete($legacyPath);
                } catch (\Throwable $ex) {
                }
            }

            throw $e;
        }

        return redirect()->route($request->user()->isAdmin() ? 'admin.kos.index' : 'owner.kos.index')->with('success', 'Kos berhasil ditambahkan.');
    }

    public function show(Kos $kos)
    {
        $this->authorize('view', $kos);
        $kos->loadCount('kamar', 'penghunis', 'bookings');
        $kos->load(['fasilitas', 'photos', 'coverPhoto']);

        return view('owner.kos.show', compact('kos'));
    }

    public function edit(Kos $kos)
    {
        $this->authorize('update', $kos);

        $kos->load(['photos', 'coverPhoto']);

        $fasilitasList = Fasilitas::ofType('kos')->active()->get();
        $selectedFasilitas = $kos->fasilitas->pluck('id')->toArray();

        $admins = User::where('role', 'admin')->orderBy('name')->get();
        $selectedAdmins = $kos->admins->pluck('id')->toArray();

        return view('owner.kos.edit', compact('kos', 'fasilitasList', 'selectedFasilitas', 'admins', 'selectedAdmins'));
    }

    public function update(UpdateKosRequest $request, Kos $kos)
    {
        $this->authorize('update', $kos);

        $data = $request->validated();
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        $deleteIds = $request->input('delete_photo_ids', []);
        if (! is_array($deleteIds)) {
            $deleteIds = $deleteIds ? [$deleteIds] : [];
        }
        $deleteIds = array_values(array_filter(array_map('intval', $deleteIds)));

        $coverPhotoId = $request->input('cover_photo_id');
        $coverPhotoId = $coverPhotoId !== null && $coverPhotoId !== '' ? (int) $coverPhotoId : null;

        $galleryFiles = $request->file('photos', []);
        if (! is_array($galleryFiles)) {
            $galleryFiles = $galleryFiles ? [$galleryFiles] : [];
        }
        $galleryFiles = array_values(array_filter($galleryFiles));

        unset($data['photos'], $data['delete_photo_ids'], $data['cover_photo_id']);

        // Scoped validation for delete ids
        if (! empty($deleteIds)) {
            $validCount = KosPhoto::where('kos_id', $kos->id)->whereIn('id', $deleteIds)->count();
            if ($validCount !== count($deleteIds)) {
                return back()->withErrors(['delete_photo_ids' => 'Foto yang dipilih tidak valid.'])->withInput();
            }
            // Authorize each
            foreach ($deleteIds as $did) {
                $photo = KosPhoto::find($did);
                if ($photo) {
                    $this->authorize('delete', $photo);
                }
            }
        }

        // Scoped validation for cover
        if ($coverPhotoId !== null) {
            $coverPhoto = KosPhoto::where('kos_id', $kos->id)->where('id', $coverPhotoId)->first();
            if (! $coverPhoto) {
                // Check if cover is among newly uploaded? No, IDs not yet exist
                // Also check if cover is being deleted
                if (in_array($coverPhotoId, $deleteIds, true)) {
                    return back()->withErrors(['cover_photo_id' => 'Cover tidak boleh foto yang dihapus.'])->withInput();
                }

                return back()->withErrors(['cover_photo_id' => 'Cover yang dipilih tidak valid.'])->withInput();
            }
            $this->authorize('setCover', $coverPhoto);
        }

        // Total count limit: existing - deletes + new <=10
        $existingCount = $kos->photos()->count();
        $afterDeleteCount = $existingCount - count($deleteIds);
        $totalAfter = $afterDeleteCount + count($galleryFiles);
        if ($totalAfter > 10) {
            return back()->withErrors(['photos' => 'Maksimal 10 foto per Kos.'])->withInput();
        }

        $storedPaths = [];
        $legacyNewPath = null;
        $legacyOldPath = $kos->photo;

        DB::beginTransaction();

        try {
            // Legacy single photo handling (backward compat)
            if ($request->hasFile('photo')) {
                if ($kos->photo && Storage::disk('public')->exists($kos->photo)) {
                    Storage::disk('public')->delete($kos->photo);
                }
                $legacyNewPath = $request->file('photo')->store('kos', 'public');
                $data['photo'] = $legacyNewPath;
            }

            $kos->update($data);
            $kos->fasilitas()->sync($fasilitasIds);

            if (! $request->user()->isAdmin()) {
                $adminIds = $request->input('admins', []);
                $kos->admins()->sync(is_array($adminIds) ? array_filter($adminIds) : []);
            }

            // Collect cover info before deletes
            $deletedWasCover = false;
            $deletedPositions = [];

            if (! empty($deleteIds)) {
                $toDelete = KosPhoto::where('kos_id', $kos->id)->whereIn('id', $deleteIds)->get();
                foreach ($toDelete as $photo) {
                    if ($photo->is_cover) {
                        $deletedWasCover = true;
                    }
                    $deletedPositions[] = $photo->position;
                    $path = $photo->path;
                    $photo->delete();
                    try {
                        Storage::disk('public')->delete($path);
                    } catch (\Throwable $e) {
                        \Log::warning('Kos photo file delete failed', ['photo_id' => $photo->id, 'path' => $path, 'error' => $e->getMessage()]);
                    }
                }

                // Compact positions
                $remaining = KosPhoto::where('kos_id', $kos->id)->orderBy('position')->get();
                foreach ($remaining as $idx => $rp) {
                    if ((int) $rp->position !== $idx) {
                        $rp->update(['position' => $idx]);
                    }
                }

                // If cover was deleted, assign smallest position as new cover
                if ($deletedWasCover) {
                    $remainingAfter = KosPhoto::where('kos_id', $kos->id)->orderBy('position')->get();
                    if ($remainingAfter->isNotEmpty()) {
                        $alreadyHasCover = $remainingAfter->contains(fn ($p) => $p->is_cover);
                        if (! $alreadyHasCover) {
                            $first = $remainingAfter->first();
                            // Clear others just in case
                            KosPhoto::where('kos_id', $kos->id)->update(['is_cover' => false]);
                            $first->update(['is_cover' => true]);
                        }
                    }
                }
            }

            // Handle cover change (explicit)
            if ($coverPhotoId !== null) {
                // Ensure not deleted
                if (! in_array($coverPhotoId, $deleteIds, true)) {
                    KosPhoto::where('kos_id', $kos->id)->update(['is_cover' => false]);
                    KosPhoto::where('kos_id', $kos->id)->where('id', $coverPhotoId)->update(['is_cover' => true]);
                }
            }

            // Append new photos
            if (! empty($galleryFiles)) {
                $currentMax = KosPhoto::where('kos_id', $kos->id)->max('position');
                $startPos = $currentMax !== null ? ((int) $currentMax + 1) : 0;

                // If no cover exists after deletes and we are appending, first appended becomes cover unless cover was explicitly set
                $hasCover = KosPhoto::where('kos_id', $kos->id)->where('is_cover', true)->exists();
                $shouldSetFirstAsCover = ! $hasCover && $coverPhotoId === null;

                foreach ($galleryFiles as $idx => $file) {
                    if (! $file || ! $file->isValid()) {
                        continue;
                    }
                    $path = $file->store("kos/{$kos->id}", 'public');
                    $storedPaths[] = $path;
                    $pos = $startPos + $idx;
                    $isCover = $shouldSetFirstAsCover && $idx === 0;
                    KosPhoto::create([
                        'kos_id' => $kos->id,
                        'path' => $path,
                        'position' => $pos,
                        'is_cover' => $isCover,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            foreach ($storedPaths as $p) {
                try {
                    Storage::disk('public')->delete($p);
                } catch (\Throwable $ex) {
                }
            }

            // If we stored a new legacy photo but rolled back, delete it
            if ($legacyNewPath) {
                try {
                    Storage::disk('public')->delete($legacyNewPath);
                } catch (\Throwable $ex) {
                }
            }

            throw $e;
        }

        return redirect()->route($request->user()->isAdmin() ? 'admin.kos.index' : 'owner.kos.index')->with('success', 'Kos berhasil diperbarui.');
    }

    public function destroy(Kos $kos)
    {
        $this->authorize('delete', $kos);

        $hasActivePenghuni = $kos->penghunis()->where('status', 'active')->exists();
        $hasActiveBooking = $kos->bookings()->whereIn('status', Booking::activeStatuses())->exists();

        if ($hasActivePenghuni || $hasActiveBooking) {
            return redirect()->route('owner.kos.index')
                ->with('error', 'Kos tidak dapat dihapus karena masih memiliki penghuni aktif atau booking aktif.');
        }

        $hasUnpaidTagihan = $kos->penghunis()
            ->whereHas('tagihans', fn ($q) => $q->outstanding())
            ->exists();

        if ($hasUnpaidTagihan) {
            return redirect()->route('owner.kos.index')
                ->with('error', 'Kos tidak dapat dihapus karena masih memiliki tagihan yang belum lunas.');
        }

        $hasHistory = $kos->kamar()->exists()
            || $kos->penghunis()->exists()
            || $kos->bookings()->exists();

        if ($hasHistory) {
            return redirect()->route('owner.kos.index')
                ->with('error', 'Kos tidak dapat dihapus karena masih memiliki riwayat kamar, penghuni, atau booking.');
        }

        $kos->delete();

        return redirect()->route('owner.kos.index')->with('success', 'Kos berhasil dihapus.');
    }

    private function authUserIsOwner(): bool
    {
        return auth()->user()->isOwner();
    }
}
