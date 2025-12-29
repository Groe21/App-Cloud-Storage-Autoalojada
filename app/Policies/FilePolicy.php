<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    /**
     * Determine if the user can view the file.
     */
    public function view(User $user, File $file): bool
    {
        return $user->id === $file->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can update the file.
     */
    public function update(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    /**
     * Determine if the user can delete the file.
     */
    public function delete(User $user, File $file): bool
    {
        return $user->id === $file->user_id || $user->isAdmin();
    }
}
