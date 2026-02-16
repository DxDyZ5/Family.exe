<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    /**
     * Any authenticated user can view photos.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can view a photo.
     */
    public function view(User $user, Photo $photo): bool
    {
        return true;
    }

    /**
     * Any authenticated user can upload photos.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the admin can delete photos.
     */
    public function delete(User $user, Photo $photo): bool
    {
        return $user->is_admin;
    }
}
