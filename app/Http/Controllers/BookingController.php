<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        return view('bookings.index', [
            'bookings' => Booking::query()
                ->whereBelongsTo($request->user())
                ->with(['service', 'payment', 'ticket', 'review'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(Request $request): View
    {
        $services = Service::active()
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('name')
            ->get();

        return view('bookings.create', [
            'services' => $services,
            'selectedService' => $services->firstWhere('id', (int) $request->query('package')),
        ]);
    }

public function store(StoreBookingRequest $request): RedirectResponse
{
    dd('MASUK STORE', $request->all());

    $service = Service::active()->findOrFail($request->integer('package_id'));
    $participantCount = $request->integer('participant_count');

    $booking = DB::transaction(function () use ($request, $service, $participantCount): Booking {
        $subtotal = $service->pricing_type === 'per_trip'
            ? (float) $service->price
            : (float) $service->price * $participantCount;
        $serviceFee = 0;

        $booking = Booking::create([
            'booking_code' => $this->generateBookingCode(),
            'user_id' => $request->user()->id,
            'service_id' => $service->id,
            'visit_date' => $request->input('visit_date'),
            'participant_count' => $participantCount,
            'contact_name' => $request->input('contact_name'),
            'contact_phone' => $request->input('contact_phone'),
            'contact_email' => $request->input('contact_email'),
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'total_price' => $subtotal + $serviceFee,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'notes' => $request->input('notes'),
            'expires_at' => now()->addDay(),
        ]);

        $booking->payment()->create([
            'order_id' => 'ORDER-'.$booking->booking_code,
            'gross_amount' => $booking->total_price,
            'status' => Payment::STATUS_UNPAID,
            'expired_at' => $booking->expires_at,
        ]);

        return $booking;
    });

    return redirect()
        ->route('bookings.checkout', $booking)
        ->with('success', 'Booking berhasil dibuat. Silakan lanjutkan pembayaran.');
}

    public function checkout(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        return view('bookings.checkout', [
            'booking' => $booking->load(['service', 'payment', 'ticket']),
            'snapJsUrl' => config('midtrans.snap_js_url'),
            'midtransClientKey' => config('midtrans.client_key'),
            'midtransReady' => filled(config('midtrans.server_key')) && filled(config('midtrans.client_key')),
        ]);
    }

    public function pay(Booking $booking, MidtransService $midtrans): JsonResponse
    {
        Gate::authorize('pay', $booking);

        try {
            return response()->json([
                'snap_token' => $midtrans->createSnapToken($booking),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        return view('bookings.show', [
            'booking' => $booking->load(['service', 'payment', 'ticket', 'review']),
        ]);
    }

    public function ticket(Booking $booking): View|RedirectResponse
    {
        if (! $booking->isTicketAvailable()) {
            if (request()->user()?->isAdmin()) {
                return redirect()
                    ->route('admin.bookings.show', $booking)
                    ->with('error', 'E-ticket hanya tersedia setelah pembayaran berhasil.');
            }

            return redirect()
                ->route('bookings.checkout', $booking)
                ->with('error', 'E-ticket hanya tersedia setelah pembayaran berhasil.');
        }

        Gate::authorize('viewTicket', $booking);

        return view('bookings.ticket', [
            'booking' => $booking->load(['service', 'payment', 'ticket', 'user']),
        ]);
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'BK-'.now()->format('ymd').'-'.strtoupper(str()->random(5));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }
}
