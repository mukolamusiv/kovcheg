<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CountMaterials extends BaseWidget
{



    public $sum = 0.00;

    public $count = 0.00;

    protected static ?string $pollingInterval = null;

    public function mount(): void
    {
        // if($model = ''){

        // }
        // $this->sum = $data['sum'] ?? 0;
        // $this->count = $data['count'] ?? 0;
        // $this->sum = \App\Models\Material::sum('quantity');
        // $this->count = \App\Models\Material::count();
    }


    protected function getStats(): array
    {
        return [
            Stat::make('Всього матеріалів на складі', $this->count)
                ->description('Кількість матеріалів на складі')
                ->value($this->count)
                ->color('primary'),

            Stat::make('Загальна вартість', $this->sum)
                ->description('Загальна вартість матеріалів на складі')
                ->value($this->sum)
                ->color('success'),
        ];
    }
}
