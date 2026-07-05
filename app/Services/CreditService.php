<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\User;

class CreditService
{
    public function charge(User $user, int $amount, string $description): bool
    {
        if (($user->credits_balance ?? 0) < $amount) {
            return false;
        }

        $user->decrement('credits_balance', $amount);

        CreditTransaction::create([
            'user_id' => $user->id,
            'type' => 'saida',
            'amount' => $amount,
            'description' => $description,
        ]);

        return true;
    }

    public function add(User $user, int $amount, string $description): void
    {
        $user->increment('credits_balance', $amount);

        CreditTransaction::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'amount' => $amount,
            'description' => $description,
        ]);
    }
}
