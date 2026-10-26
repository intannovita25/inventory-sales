<?php

namespace Tests\Concerns;

use App\Models\Product;
use App\Models\User;

trait InteractsWithInventory
{
    protected function makeAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    protected function makeStaff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF]);
    }

    protected function makeProduct(array $attributes = []): Product
    {
        return Product::factory()->create($attributes);
    }
}
