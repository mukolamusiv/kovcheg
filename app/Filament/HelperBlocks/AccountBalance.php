<?php

namespace App\Filament\HelperBlocks;

class AccountBalance
{
    public function getAccountBalance($account)
    {
        // Якщо рахунок не знайдено, повертаємо null або 0
        if (!$account) {
            return 0;
        }

        // Повертаємо баланс рахунку
        return $account->balance;
    }
}
