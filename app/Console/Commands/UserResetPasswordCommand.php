<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UserResetPasswordCommand extends Command
{
    protected $signature = 'user:reset-password 
                            {email : The email address of the user}
                            {--password= : New password to set (min 8 chars)}
                            {--name= : Name of the user if creating a new record}
                            {--create : Create the user if not found}';

    protected $description = 'Reset the password for a registered user, or create the account if missing';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $password = $this->option('password');

        if (! $password) {
            $password = $this->ask('Enter new password (min 8 characters)');
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return 1;
        }

        $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

        if (! $user) {
            if ($this->option('create') || $this->confirm("User '{$email}' not found. Would you like to create this account now?", true)) {
                $name = $this->option('name') ?: $this->ask('Enter user name', Str::headline(explode('@', $email)[0]));
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'user',
                ]);

                $this->info("User '{$email}' created successfully with your chosen password!");

                return 0;
            }

            $this->error("No user found with email '{$email}'.");

            return 1;
        }

        $user->password = $password;
        $user->save();

        $this->info("Password for user '{$user->email}' ({$user->name}) has been reset successfully!");

        return 0;
    }
}
