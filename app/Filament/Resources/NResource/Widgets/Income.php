<?php

namespace App\Filament\Resources\NResource\Widgets;

use Filament\Widgets\ChartWidget;

class Income extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
