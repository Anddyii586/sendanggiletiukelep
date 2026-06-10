<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
        {--name= : Admin display name}
        {--email= : Admin email address}
        {--password= : Admin password. Omit this option to enter it securely.}';

    protected $description = 'Create a production admin user safely.';

    public function handle(): int
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        if ($name === null) {
            $name = $this->ask('Admin name');
        }

        if ($email === null) {
            $email = $this->ask('Admin email');
        }

        if ($password === null) {
            $password = $this->secret('Admin password');
        }

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', Password::min(8)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => (string) $name,
            'email' => (string) $email,
            'password' => Hash::make((string) $password),
            'role' => 'admin',
        ]);

        $this->info("Admin user created: {$user->email}");

        return self::SUCCESS;
    }
}
