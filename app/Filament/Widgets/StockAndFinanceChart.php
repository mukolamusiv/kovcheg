<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\TransactionEntry;
use Filament\Widgets\ChartWidget;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class StockAndFinanceChart extends ChartWidget implements HasForms
{

    use InteractsWithForms;
    //protected static ?string $pollingInterval = '5s';

    public ?int $accountId = null;

  //  protected static ?string $heading = 'Chart';

    protected static ?string $heading = 'Динаміка запасів та зобов’язань';
    protected static string $color = 'info';
    protected int | string | array $columnSpan = 'full';



    protected function getFilters(): ?array
    {
        $accounts = Account::query()
            ->select('id', 'name')
            ->where('owner_type', null)
            ->orderBy('name')
            ->get();

           $data = [];
        foreach ($accounts as $account) {
            $data[$account->id] = $account->name;
        }
           // dd($data);

            return $data;
        // return [
        //     'today' => 'Today',
        //     'week' => 'Last week',
        //     'month' => 'Last month',
        //     'year' => 'This year',
        // ];
    }

    public static function canView(): bool
        {
            return auth()->user()->role === 'admin'; // обмеження на працівника
        }


    protected function getData(): array
    {

        $accountId = $this->filter;
        // if (!$this->accountId) {
        //     return [
        //         'datasets' => [],
        //         'labels' => [],
        //     ];
        // }
        // Generate 20+ test records for TransactionEntry
        // for ($i = 0; $i < 5; $i++) {
        //     $dat = \App\Models\TransactionEntry::create([
        //         'account_id' => 4, // Припускаємо, що account_id 4 є дійсним
        //         'transaction_id' => 14, // Припускаємо, що transaction_id 1 є дійсним
        //         'entry_type' => $i % 2 === 0 ? 'дебет' : 'кредит', // Тип запису: дебет або кредит
        //         'amount' => rand(1000, 10000), // Випадкова сума від 100 до 1000
        //         //'created_at' => now()->subDays(rand(0, 365))->setTime(rand(0, 23), rand(0, 59), rand(0, 59)),
        //         'updated_at' => now(),
        //     ]);
        //     $dat['created_at'] = now()->subDays(rand(0, 90))->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
        //     $dat->save();
        // }

        $entries = TransactionEntry::query()
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m-01") as month,
                SUM(CASE WHEN entry_type = "дебет" THEN amount ELSE 0 END) as total_debet,
                SUM(CASE WHEN entry_type = "кредит" THEN amount ELSE 0 END) as total_credit
            ')
            ->where('account_id', $accountId)
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-01")')
            ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m-01")')
            ->get();

        $labels = $entries->pluck('month')->toArray();
        $debet = $entries->pluck('total_debet')->map(fn($v) => (float) $v)->toArray();
        $credit = $entries->pluck('total_credit')->map(fn($v) => (float) $v)->toArray();

      //  dd($entries, $labels, $debet, $credit);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Дохід (Дебет)',
                    'data' => $debet,
                    'borderColor' => '#10B981', // зелений
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                ],
                [
                    'label' => 'Витрати (Кредит)',
                    'data' => $credit,
                    'borderColor' => '#EF4444', // червоний
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
