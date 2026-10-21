<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManagementAgreementRequest;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Services\AuditLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ManagementAgreementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = ManagementAgreement::with(['kos', 'propertyOwner', 'approver'])->latest();
        if ($user->isOwner()) {
            $query->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $assignedIds = $user->assignedKos()->pluck('kos.id')->all();
            $query->whereIn('kos_id', $assignedIds);
        }
        $agreements = $query->paginate(15)->withQueryString();
        if ($user->isOwner()) {
            $kosOptions = Kos::where('owner_id', $user->id)->whereNotNull('property_owner_id')->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        } elseif ($user->isAdmin()) {
            $assignedIds = $user->assignedKos()->pluck('kos.id')->all();
            $kosOptions = Kos::whereIn('id', $assignedIds)->whereNotNull('property_owner_id')->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        } else {
            $kosOptions = Kos::whereNotNull('property_owner_id')->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        }

        return view('owner.agreements.index', compact('agreements', 'kosOptions'));
    }

    public function store(StoreManagementAgreementRequest $request)
    {
        $user = $request->user();
        $kos = Kos::findOrFail($request->kos_id);
        abort_unless($user->canAccessKos($kos->id) || $user->isSuperAdmin(), 403);
        if (! $kos->property_owner_id) {
            return back()->with('error', 'Tentukan Pemilik Kos Asli untuk kos ini terlebih dahulu.')->withInput();
        }
        try {
            return \DB::transaction(function () use ($request, $kos, $user) {
                Kos::whereKey($kos->id)->lockForUpdate()->first();
                $exists = ManagementAgreement::where('kos_id', $kos->id)->where('effective_from', $request->effective_from)->lockForUpdate()->exists();
                if ($exists) {
                    throw new \RuntimeException('duplicate_effective_from');
                }
                $newFrom = $request->effective_from;
                $newTo = $request->effective_to ?? '9999-12-31';
                $overlap = ManagementAgreement::where('kos_id', $kos->id)
                    ->where('effective_from', '<=', $newTo)
                    ->where(function ($q) use ($newFrom) {
                        $q->whereNull('effective_to')->orWhere('effective_to', '>=', $newFrom);
                    })->lockForUpdate()->exists();
                if ($overlap) {
                    throw new \RuntimeException('overlap');
                }
                $agreement = ManagementAgreement::create([
                    'kos_id' => $kos->id,
                    'property_owner_id' => $kos->property_owner_id,
                    'commission_type' => $request->commission_type,
                    'commission_value' => $request->commission_value,
                    'effective_from' => $request->effective_from,
                    'effective_to' => $request->effective_to,
                    'approved_by' => $user->id,
                    'notes' => $request->notes,
                ]);
                try {
                    AuditLogService::create('Perjanjian Komisi', "Perjanjian komisi dibuat untuk {$kos->name}", ['agreement_id' => $agreement->id, 'kos_id' => $kos->id]);
                } catch (\Throwable $e) {
                    \Log::warning('Agreement audit failed: '.$e->getMessage());
                }

                return redirect()->route('owner.agreements.index')->with('success', 'Perjanjian komisi berhasil dibuat.');
            });
        } catch (\RuntimeException $e) {
            $msg = match ($e->getMessage()) {
                'duplicate_effective_from' => 'Perjanjian dengan tanggal efektif tersebut sudah ada.',
                'overlap' => 'Periode perjanjian tumpang tindih dengan perjanjian lain (termasuk rentang open-ended).',
                default => $e->getMessage(),
            };

            return back()->withErrors(['effective_from' => $msg])->withInput();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->withErrors(['effective_from' => 'Perjanjian dengan tanggal efektif tersebut sudah ada.'])->withInput();
            }
            throw $e;
        }
    }
}
