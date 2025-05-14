<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\TransactionEntry;
use App\Models\WarehouseMaterial;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceCart extends BaseWidget
{


    protected function getData(): array
    {
        $accountId = Account::query()
            ->select('id', 'name')
            ->where('owner_type', null)
            ->orderBy('name')
            ->get();

        foreach ($accountId as $account) {
            $data[$account->id] = $account->id;
        }
        $entries = TransactionEntry::query()
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m-01") as month,
                SUM(CASE WHEN entry_type = "дебет" THEN amount ELSE 0 END) as total_debet,
                SUM(CASE WHEN entry_type = "кредит" THEN amount ELSE 0 END) as total_credit
            ')
            ->where('account_id',  $data)
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-01")')
            ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m-01")')
            ->get();

        $debet = $entries->pluck('total_debet')->map(fn($v) => (float) $v)->sum();
        $credit = $entries->pluck('total_credit')->map(fn($v) => (float) $v)->sum();

        //dd($debet, $credit);
        // $labels = $entries->pluck('month')->toArray();



        // всього матеріалів на складі
        $materialTotal = 0.00;
        foreach (WarehouseMaterial::all() as $material) {
            $materialTotal  += $material->quantity * $material->price;
        }
        $materialTotal = number_format($materialTotal, 2, '.', '');



        // зобовязання перед постачальниками
        $obligation = 0.00;
        foreach (Account::where('account_type','пасив')->get() as $account_type) {
            $obligation += $account_type->quantity * $account_type->price;
        }
        $obligation = number_format($obligation, 2, '.', '');


        // зобовязання клієнтів постачальниками
        $obligation = 0.00;
        foreach (Account::where('account_type','актив')->get() as $account_type) {
            $obligation += $account_type->quantity * $account_type->price;
        }
        $obligation = number_format($obligation, 2, '.', '');



        $datas = [
            'total' => $debet,
            'materialTotal' => $materialTotal,
            'obligation' => $obligation,
            'profit' => 3.12,
            'profit_increase' => 3,
        ];

        return $datas;
    }

    protected function getStats(): array
    {
        $data = $this->getData();

        $total = [
            Stat::make('Варість матеріалів', $data['materialTotal'].' грн')
                ->description('Загальна вартість матеріалів на складах')
                ->color('success'),
            Stat::make('Зобовязання', $data['obligation'].' грн')
                ->description('Наші зобовязання перед постачальниками')
                ->color('danger'),
            Stat::make('Прибуток', '3:12')
                ->description('3% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];

        return $total;
    }
}
