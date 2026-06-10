<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

test('.env.example berisi placeholder production tanpa secret nyata', function (): void {
    $contents = file_get_contents(base_path('.env.example'));

    expect($contents)->toContain(
        'APP_ENV=production',
        'APP_DEBUG=false',
        'APP_URL=https://your-domain.com',
        'SESSION_DRIVER=database',
        'SESSION_SECURE_COOKIE=true',
        'SESSION_SAME_SITE=lax',
        'CACHE_STORE=database',
        'QUEUE_CONNECTION=database',
        'MIDTRANS_IS_PRODUCTION=false'
    );

    expect(preg_match('/^APP_KEY=\s*$/m', $contents))->toBe(1)
        ->and(preg_match('/^DB_PASSWORD=\s*$/m', $contents))->toBe(1)
        ->and(preg_match('/^MIDTRANS_SERVER_KEY=\s*$/m', $contents))->toBe(1)
        ->and(preg_match('/^MIDTRANS_CLIENT_KEY=\s*$/m', $contents))->toBe(1)
        ->and(preg_match('/^MAIL_PASSWORD=\s*$/m', $contents))->toBe(1);

    expect($contents)->not->toContain(
        '@&min586',
        '123123123',
        'SB-Mid-server',
        'Mid-server',
        'xoxb-',
        'sk_live',
        'ngrok authtoken'
    );
});

test('admin create command bisa membuat admin', function (): void {
    $this->artisan('admin:create', [
        '--name' => 'Production Admin',
        '--email' => 'production-admin@example.com',
        '--password' => 'strong-password',
    ])
        ->expectsOutput('Admin user created: production-admin@example.com')
        ->assertExitCode(0);

    $admin = User::where('email', 'production-admin@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Production Admin')
        ->and($admin->role)->toBe('admin')
        ->and(Hash::check('strong-password', $admin->password))->toBeTrue();
});

test('admin create command menolak password pendek', function (): void {
    $this->artisan('admin:create', [
        '--name' => 'Short Password Admin',
        '--email' => 'short-password-admin@example.com',
        '--password' => 'short',
    ])
        ->assertExitCode(1);

    expect(User::where('email', 'short-password-admin@example.com')->exists())->toBeFalse();
});

test('admin create command menolak email duplikat', function (): void {
    User::factory()->create([
        'email' => 'duplicate-admin@example.com',
        'role' => 'admin',
    ]);

    $this->artisan('admin:create', [
        '--name' => 'Duplicate Admin',
        '--email' => 'duplicate-admin@example.com',
        '--password' => 'strong-password',
    ])
        ->assertExitCode(1);

    expect(User::where('email', 'duplicate-admin@example.com')->count())->toBe(1);
});

test('route admin tetap protected', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('route webhook midtrans tetap tersedia', function (): void {
    $route = Route::getRoutes()->getByName('payments.midtrans.notification');

    expect($route)->not->toBeNull();
    expect($route->uri())->toBe('payments/midtrans/notification');
    expect($route->methods())->toContain('POST');
});

test('scheduler bookings expire tetap terdaftar', function (): void {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('bookings:expire')
        ->assertExitCode(0);
});
