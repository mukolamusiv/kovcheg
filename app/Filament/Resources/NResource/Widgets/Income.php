<?php

namespace App\Filament\Resources\NResource\Widgets;

use App\Models\Invoice;
use App\Models\Production;
use Filament\Widgets\ChartWidget;

class Income extends ChartWidget
{
    protected static ?string $heading = 'Чистий дохід';

    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '900px';
    protected static ?string $pollingInterval = '2230s';

    public function calculateTotalIncome()
    {
        $salesInvoices = Invoice::selectRaw('SUM(total) as total, MONTH(invoice_date) as month')
            ->groupBy('month')
            ->where('type', 'продаж')
            ->pluck('total', 'month');

        $purchaseInvoices = Production::selectRaw('SUM(price * quantity) as total, MONTH(production_date) as month')
            ->groupBy('month')
            ->pluck('total', 'month');


        $totalIncome = [];

        foreach (range(1, 12) as $month) {
            $sales = $salesInvoices[$month] ?? 0;
            $purchases = $purchaseInvoices[$month] ?? 0;
            $totalIncome[$month] = $sales - $purchases;
        }

        $formattedIncome = [];
        foreach (range(1, 12) as $month) {
            $formattedIncome[] = $totalIncome[$month] ?? 0;
        }
        return $formattedIncome;
        //return $totalIncome;
    }

    protected function getData(): array
    {

        //return $this->calculateTotalIncome();

        return [
            'datasets' => [
                [
                    'label' => 'Фінансовий результат',
                    'data' => $this->calculateTotalIncome() ?? 0,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
                    'labels' => ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
