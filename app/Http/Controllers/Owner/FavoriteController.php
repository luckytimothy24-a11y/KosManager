<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()->isTenant(), 403);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with(['kos' => function ($q) {
                $q->withCount(['kamar as kamar_tersedia' => fn ($qr) => $qr->where('status', 'available')])
                    ->withCount('favorites')
                    ->withMin(['kamar as harga_mulai' => fn ($qr) => $qr->where('status', 'available')], 'monthly_price')
                    ->withMin(['kamar as harga_harian_mulai' => fn ($qr) => $qr->where('status', 'available')->whereNotNull('daily_price')], 'daily_price')
                    ->with([
                        'kamar' => fn ($k) => $k->where('status', 'available')->with('fasilitas')->limit(1),
                        'fasilitas' => fn ($f) => $f->active(),
                    ]);
            }])
            ->latest()
            ->paginate(12);

        $favoritedIds = $favorites->pluck('kos.id')->map(fn ($id) => (int) $id)->all();

        return view('tenant.favorite.index', compact('favorites', 'favoritedIds'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'kos_id' => 'required|exists:kos,id',
        ]);

        $existing = Favorite::where('user_id', $request->user()->id)
            ->where('kos_id', $request->kos_id)
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('success', 'Kos dihapus dari favorit.');
        }

        Favorite::create([
            'user_id' => $request->user()->id,
            'kos_id' => $request->kos_id,
        ]);

        return back()->with('success', 'Kos ditambahkan ke favorit.');
    }
}
