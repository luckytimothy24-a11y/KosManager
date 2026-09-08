<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Partner::class);

        $partners = Partner::withCount('campaigns')
            ->orderBy('name')
            ->paginate(12);

        return view('super-admin.partner.index', compact('partners'));
    }

    public function create()
    {
        $this->authorize('create', Partner::class);

        return view('super-admin.partner.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Partner::class);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:130|unique:partners,slug',
            'logo' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:2000',
            'website_url' => 'nullable|url|max:500',
            'monetization_type' => 'nullable|in:cpm,cpc,deal,contract',
            'contact_name' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:190',
            'status' => 'nullable|in:active,inactive',
        ]);

        $partner = Partner::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['slug'] ?? $data['name']),
            'logo' => $data['logo'] ?? null,
            'description' => $data['description'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'monetization_type' => $data['monetization_type'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'status' => $data['status'] ?? Partner::STATUS_ACTIVE,
        ]);

        AuditLogService::create('Partner', "Partner {$partner->name} dibuat", ['partner_id' => $partner->id]);

        return redirect()->route('super-admin.partners.index')
            ->with('success', 'Partner berhasil dibuat.');
    }

    public function edit(Partner $partner)
    {
        $this->authorize('update', $partner);

        return view('super-admin.partner.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $this->authorize('update', $partner);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:130|unique:partners,slug,'.$partner->id,
            'logo' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:2000',
            'website_url' => 'nullable|url|max:500',
            'monetization_type' => 'nullable|in:cpm,cpc,deal,contract',
            'contact_name' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:190',
            'status' => 'nullable|in:active,inactive',
        ]);

        $partner->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['slug'] ?? $data['name']),
            'logo' => $data['logo'] ?? null,
            'description' => $data['description'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'monetization_type' => $data['monetization_type'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'status' => $data['status'] ?? Partner::STATUS_ACTIVE,
        ]);

        AuditLogService::update('Partner', "Partner {$partner->name} diperbarui", ['partner_id' => $partner->id]);

        return redirect()->route('super-admin.partners.index')
            ->with('success', 'Partner berhasil diperbarui.');
    }

    public function destroy(Partner $partner)
    {
        $this->authorize('delete', $partner);

        // Hindari penghapusan destruktif: nonaktifkan partner yang masih dipakai campaign.
        if ($partner->campaigns()->exists()) {
            $partner->update(['status' => Partner::STATUS_INACTIVE]);
            AuditLogService::update('Partner', "Partner {$partner->name} dinonaktifkan karena masih dipakai campaign", ['partner_id' => $partner->id]);

            return redirect()->route('super-admin.partners.index')
                ->with('success', 'Partner dinonaktifkan karena masih digunakan campaign.');
        }

        $partner->delete();
        AuditLogService::delete('Partner', "Partner {$partner->name} dihapus", ['partner_id' => $partner->id]);

        return redirect()->route('super-admin.partners.index')
            ->with('success', 'Partner berhasil dihapus.');
    }
}
