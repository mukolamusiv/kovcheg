<?php

namespace App\Filament\Resources\NResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpdateInfo extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Інформація про версію','0.2.1')
            ->description('
            - Додано можливість додавати фото;
            - Таблиця залишків на складі;
            - Додано можливість привязати постачальника до матеріалу;
            - Генерація штрих коду;'),
        ];
    }
}
