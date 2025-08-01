<?php

namespace App\Policies;

use App\Models\Receipt;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceiptPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('inscription.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Receipt $receipt)
    {
        return $user->hasPermissionTo('inscription.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('inscription.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Receipt $receipt)
    {
        return $user->hasPermissionTo('inscription.edit');
    }
}
