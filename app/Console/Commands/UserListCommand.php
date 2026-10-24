<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserListCommand extends Command
{
    protected $signature = 'user:list';

    protected $description = 'List all registered user accounts in the current active database';

    public function handle(): int
    {
        $users = User::all(['id', 'name', 'email', 'role', 'created_at']);

        if ($users->isEmpty()) {
            $this->warn('No user accounts found in the active database.');

            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Registered At'],
            $users->map(fn ($u) => [
                $u->id,
                $u->name,
                $u->email,
                $u->role ?? 'user',
                $u->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ])
        );

        $this->info("Total registered users: {$users->count()}");

        return 0;
    }
}
