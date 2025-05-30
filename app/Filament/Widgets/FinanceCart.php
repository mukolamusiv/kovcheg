<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\TransactionEntry;
use App\Models\WarehouseMaterial;
use App\Models\WarehouseProduction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceCart extends BaseWidget
{



    public static function canView(): bool
    {
        return auth()->user()->role === 'admin'; // обмеження на працівника
    }


    protected function getData(): array
    {
        // $accountId = Account::query()
        //     ->select('id', 'name')
        //     ->where('owner_type', null)
        //     ->orderBy('name')
        //     ->get();

        // foreach ($accountId as $account) {
        //     $data[$account->id] = $account->id;
        // }
        // $entries = TransactionEntry::query()
        //     ->selectRaw('
        //         DATE_FORMAT(created_at, "%Y-%m-01") as month,
        //         SUM(CASE WHEN entry_type = "дебет" THEN amount ELSE 0 END) as total_debet,
        //         SUM(CASE WHEN entry_type = "кредит" THEN amount ELSE 0 END) as total_credit
        //     ')
        //     ->where('account_id',  $data)
        //     ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-01")')
        //     ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m-01")')
        //     ->get();

        // $debet = $entries->pluck('total_debet')->map(fn($v) => (float) $v)->sum();
        // $credit = $entries->pluck('total_credit')->map(fn($v) => (float) $v)->sum();

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
        foreach (Account::where('owner_type', '=', 'App\Models\Supplier')->get() as $account_type) {
            $obligation += $account_type->balance;
        }
        $obligation = number_format($obligation, 2, '.', '');


        // зобовязання клієнтів
        $active = 0.00;
        foreach (Account::where('owner_type', '=', 'App\Models\Customer')->get() as $account_types) {
            $active += $account_types->balance;
        }
        $active = number_format($active, 2, '.', '');


        // зобовязання наші перед працівниками
        $user = 0.00;
        foreach (Account::where('owner_type', '=', 'App\Models\User')->get() as $account_types) {
            $user += $account_types->balance;
        }
        $user = number_format($user, 2, '.', '');



        $productionActive = 0.00;
        $productionSale = 0.00;
        foreach (WarehouseProduction::all() as $production) {
            $productionActive += $production->price * $production->quantity;
            $productionSale += $production->production->price * $production->quantity;
        }
        // $active = 0.00;
        $productionSale = number_format( $productionSale, 2, '.', '');
        $productionActive = number_format($productionActive, 2, '.', '');
        $all = $productionActive - $productionSale;


        $datas = [
            //'total' => $debet,
            'materialTotal' => $materialTotal,
            'obligation' => $obligation,
            'active' => $active,
            'productionSale' => $productionSale,
            'productionActive' => $productionActive,
            'all' => $all ,
            'user' => $user,
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
            Stat::make('Очікування', $data['active'].' грн')
                ->description('Зобовязання клієнтів перед нами')
                ->color('success'),


            Stat::make('Не виплачена зарплата', $data['user'].' грн')
                ->description('Зарплата працівникам')
                ->color('danger'),
            // Stat::make('Собівартість продукції', $data['productionSale'].' грн')
            //     ->description('Собівартість готової продукції')
            //     ->color('warning'),
            // Stat::make('Чистий дохід', $data['all'].' грн')
            //     ->description('Усього')
            //     ->color('success'),


            Stat::make('Вартість готової продукції', $data['productionActive'].' грн')
                ->description('Загальна вартість виробів на складах')
                ->color('success'),
            Stat::make('Собівартість продукції', $data['productionSale'].' грн')
                ->description('Собівартість готової продукції')
                ->color('warning'),
            Stat::make('Чистий дохід', $data['all'].' грн')
                ->description('Усього')
                ->color('success'),
        ];

        return $total;
    }
}
