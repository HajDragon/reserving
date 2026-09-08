<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Product $product): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Product $product): bool
    {
        return (bool) $user->is_admin;
    }
}
