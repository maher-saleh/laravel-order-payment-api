<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;


/**
 * Order Policy.
 * 
 * Defines authorization logic for Order operations.
 * Keeps authorization logic separate from controllers.
 */
class OrderPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }



    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }



    /**
     * Determine if the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }



    /**
     * Determine if the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}