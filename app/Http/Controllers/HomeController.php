<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Review;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('public.home', [
            'settings' => SiteSetting::asKeyValue(),
            'services' => Service::active()
                ->orderByRaw('sort_order IS NULL, sort_order ASC')
                ->orderByDesc('is_featured')
                ->latest()
                ->take(3)
                ->get(),
            'galleries' => Gallery::active()->latest()->take(6)->get(),
            'reviews' => Review::visible()->with(['user', 'booking.service'])->latest()->take(3)->get(),
        ]);
    }

    public function destination(): View
    {
        return view('public.destination', [
            'settings' => SiteSetting::asKeyValue(),
            'services' => Service::active()->latest()->get(),
        ]);
    }

    public function packages(): View
    {
        return view('public.packages', [
            'settings' => SiteSetting::asKeyValue(),
            'services' => Service::active()
                ->orderByRaw('sort_order IS NULL, sort_order ASC')
                ->orderByDesc('is_featured')
                ->latest()
                ->paginate(9),
        ]);
    }

    public function package(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('public.package', [
            'settings' => SiteSetting::asKeyValue(),
            'service' => $service,
            'relatedServices' => Service::active()
                ->whereKeyNot($service->id)
                ->latest()
                ->take(3)
                ->get(),
            'reviews' => Review::visible()
                ->with(['user', 'booking.service'])
                ->whereHas('booking', fn ($query) => $query->where('service_id', $service->id))
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }

    public function contact(): View
    {
        return view('public.contact', [
            'settings' => SiteSetting::asKeyValue(),
        ]);
    }
}
