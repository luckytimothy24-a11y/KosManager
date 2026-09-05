<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingPackage;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AdvertisingPackageController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AdvertisingPackage::class);
        $packages = AdvertisingPackage::orderBy('sort_order')->orderBy('price')->paginate(10);

        return view('super-admin.advertising.package.index', compact('packages'));
    }

    public function create()
    {
        $this->authorize('create', AdvertisingPackage::class);

        return view('super-admin.advertising.package.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', AdvertisingPackage::class);

        $data = $request->validate([
            'code' => 'required|string|max:50|unique:advertising_packages,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'duration_days' => 'required|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'is_sponsored' => 'nullable|boolean',
            'is_homepage' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $package = AdvertisingPackage::create([
            'code' => strtoupper(trim($data['code'])),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration_days' => $data['duration_days'],
            'is_featured' => $request->boolean('is_featured'),
            'is_sponsored' => $request->boolean('is_sponsored'),
            'is_homepage' => $request->boolean('is_homepage'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        AuditLogService::create('Advertising', "Paket advertising {$package->name} dibuat", ['package_id' => $package->id]);

        return redirect()->route('super-admin.advertising.packages.index')
            ->with('success', 'Paket advertising berhasil dibuat.');
    }

    public function edit(AdvertisingPackage $package)
    {
        $this->authorize('update', $package);

        return view('super-admin.advertising.package.edit', compact('package'));
    }

    public function update(Request $request, AdvertisingPackage $package)
    {
        $this->authorize('update', $package);

        $data = $request->validate([
            'code' => 'required|string|max:50|unique:advertising_packages,code,'.$package->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'duration_days' => 'required|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'is_sponsored' => 'nullable|boolean',
            'is_homepage' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $package->update([
            'code' => strtoupper(trim($data['code'])),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration_days' => $data['duration_days'],
            'is_featured' => $request->boolean('is_featured'),
            'is_sponsored' => $request->boolean('is_sponsored'),
            'is_homepage' => $request->boolean('is_homepage'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        AuditLogService::update('Advertising', "Paket advertising {$package->name} diperbarui", ['package_id' => $package->id]);

        return redirect()->route('super-admin.advertising.packages.index')
            ->with('success', 'Paket advertising berhasil diperbarui.');
    }

    public function destroy(AdvertisingPackage $package)
    {
        $this->authorize('delete', $package);

        // Hindari penghapusan destruktif: nonaktifkan paket yang dipakai campaign.
        if ($package->campaigns()->exists()) {
            $package->update(['is_active' => false]);
            AuditLogService::update('Advertising', "Paket advertising {$package->name} dinonaktifkan karena sudah dipakai", ['package_id' => $package->id]);

            return redirect()->route('super-admin.advertising.packages.index')
                ->with('success', 'Paket advertising dinonaktifkan karena telah dipakai campaign.');
        }

        $package->delete();
        AuditLogService::delete('Advertising', "Paket advertising {$package->name} dihapus", ['package_id' => $package->id]);

        return redirect()->route('super-admin.advertising.packages.index')
            ->with('success', 'Paket advertising berhasil dihapus.');
    }
}
