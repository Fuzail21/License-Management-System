<?php

namespace App\Policies;

use App\Models\License;
use App\Models\LicenseStatus;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LicensePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, License $license): bool
    {
        // Admin can view all licenses
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can view licenses they created
        if ($user->isManager()) {
            // If created_by is null (old licenses), allow viewing
            if ($license->created_by === null) {
                return true;
            }

            // Manager can view their own licenses
            if ($license->created_by === $user->id) {
                return true;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, License $license): bool
    {
        // Admin can edit anything
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can only edit their own licenses
        if ($user->isManager() && $license->created_by === $user->id) {
            // Cannot edit rejected licenses
            if ($license->isRejected()) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, License $license): bool
    {
        // Only admin can delete licenses
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, License $license): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, License $license): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view pending licenses.
     */
    public function viewPending(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can approve the license.
     */
    public function approve(User $user, License $license): bool
    {
        return $user->isAdmin() && $license->isPending();
    }

    /**
     * Determine whether the user can reject the license.
     */
    public function reject(User $user, License $license): bool
    {
        return $user->isAdmin() && $license->isPending();
    }

    /**
     * Determine whether the user can force edit rejected licenses.
     */
    public function forceEdit(User $user, License $license): bool
    {
        return $user->isAdmin();
    }
}
