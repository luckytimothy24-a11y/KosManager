<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $kosQuery = Kos::where('status', 'active')
            ->withCount(['kamar as available_rooms' => fn ($qr) => $qr->where('status', 'available')])
            ->withMin('kamar as min_price', 'monthly_price')
            ->orderByDesc('available_rooms');

        if ($q !== '') {
            $search = addcslashes($q, '%_');
            $kosQuery->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $featuredKos = $kosQuery->limit(6)->get();

        $stats = [
            'kos' => Kos::where('status', 'active')->count(),
            'kamar' => Kamar::where('status', 'available')->count(),
            'owners' => User::where('role', 'owner')->count(),
        ];

        return view('welcome', compact('featuredKos', 'stats', 'q'));
    }
}
