<?php

namespace App\Filament\Resources\NResource\Widgets;

use App\Models\Invoice;
use App\Models\Production;
use Filament\Widgets\ChartWidget;

class Income extends ChartWidget
{
    protected static ?string $heading = 'Чистий дохід';


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

        //dd($totalIncome);
        return $totalIncome;
    }

    protected function getData(): array
    {

        return $this->calculateTotalIncome();

        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => $this->calculateTotalIncome()[$month] ?? 0,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
