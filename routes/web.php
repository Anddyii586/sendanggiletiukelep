<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminGalleryController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminSiteSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/destination', [HomeController::class, 'destination'])->name('destination');
Route::get('/packages', [HomeController::class, 'packages'])->name('packages.index');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/packages/{service:slug}', [HomeController::class, 'package'])->name('packages.show');
Route::post('/payments/midtrans/notification', [PaymentController::class, 'notification'])
    ->name('payments.midtrans.notification');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:user'])->group(function (): void {
    Route::get('/dashboard', fn () => redirect()->route('my-bookings.index'))->name('dashboard');
    Route::redirect('/bookings', '/my-bookings')->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout');
    Route::post('/bookings/{booking}/pay', [BookingController::class, 'pay'])->name('bookings.pay');

    Route::get('/my-bookings', [BookingController::class, 'index'])->name('my-bookings.index');
    Route::get('/my-bookings/{booking}', [BookingController::class, 'show'])->name('my-bookings.show');
    Route::get('/my-bookings/{booking}/ticket', [BookingController::class, 'ticket'])->name('my-bookings.ticket');
    Route::get('/my-bookings/{booking}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/my-bookings/{booking}/review', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('services', AdminServiceController::class)->except(['show']);
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking}/ticket', [BookingController::class, 'ticket'])->name('bookings.ticket');
        Route::patch('/bookings/{booking}/complete', [AdminBookingController::class, 'complete'])->name('bookings.complete');
        Route::patch('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/reports/transactions', [AdminReportController::class, 'transactions'])->name('reports.transactions');
        Route::get('/reports/transactions/export', [AdminReportController::class, 'exportTransactions'])->name('reports.transactions.export');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::resource('galleries', AdminGalleryController::class)->except(['show']);
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{review}/visibility', [AdminReviewController::class, 'visibility'])->name('reviews.visibility');
        Route::get('/site-settings', [AdminSiteSettingController::class, 'edit'])->name('site-settings.edit');
        Route::put('/site-settings', [AdminSiteSettingController::class, 'update'])->name('site-settings.update');
});
