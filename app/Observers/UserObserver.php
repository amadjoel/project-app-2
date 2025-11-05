<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->roles()->count() > 1) {
            throw ValidationException::withMessages([
                'roles' => ['Users can only have one role at a time.'],
            ]);
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if ($user->roles()->count() > 1) {
            throw ValidationException::withMessages([
                'roles' => ['Users can only have one role at a time.'],
            ]);
        }
    }
}