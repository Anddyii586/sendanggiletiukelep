<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use App\Services\AdminAuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(StoreServiceRequest $request, AdminAuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['slug'] = $this->makeUniqueSlug($data['name']);

        $service = Service::create($data);

        $auditLog->log(
            $request->user(),
            'service.created',
            $service,
            "Layanan {$service->name} dibuat.",
            ['name' => $service->name]
        );

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(StoreServiceRequest $request, Service $service, AdminAuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $before = $service->only(['name', 'price', 'pricing_type', 'is_active', 'is_featured']);

        if ($service->name !== $data['name'] || ! $service->slug) {
            $data['slug'] = $this->makeUniqueSlug($data['name'], $service->id);
        }

        $service->update($data);

        $auditLog->log(
            $request->user(),
            'service.updated',
            $service,
            "Layanan {$service->name} diperbarui.",
            [
                'before' => $before,
                'after' => $service->only(['name', 'price', 'pricing_type', 'is_active', 'is_featured']),
            ]
        );

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Request $request, Service $service, AdminAuditLogService $auditLog): RedirectResponse
    {
        if ($service->bookings()->exists()) {
            $service->update(['is_active' => false]);

            $auditLog->log(
                $request->user(),
                'service.deactivated',
                $service,
                "Layanan {$service->name} dinonaktifkan karena memiliki booking.",
                ['name' => $service->name]
            );

            return back()->with('success', 'Layanan memiliki booking, sehingga dinonaktifkan.');
        }

        $serviceName = $service->name;
        $service->delete();

        $auditLog->log(
            $request->user(),
            'service.deleted',
            $service,
            "Layanan {$serviceName} dihapus.",
            ['name' => $serviceName]
        );

        return back()->with('success', 'Layanan berhasil dihapus.');
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Service::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
