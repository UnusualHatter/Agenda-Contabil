<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function view(User $user, Client $client): bool
    {
        return $user->active;
    }

    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->canWrite();
    }

    // A client with history is deactivated, never deleted.
    public function delete(User $user, Client $client): bool
    {
        return $user->active
            && $user->isAdmin()
            && $client->appointments()->withTrashed()->doesntExist();
    }
}
