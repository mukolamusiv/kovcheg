<?php

namespace App\Filament\Widgets;

use App\Models\Production;
use App\Models\ProductionStage;
use App\Services\ProductionService;
use Filament\Widgets\Widget;

class WorkerTasksWidget extends Widget
{
    protected static string $view = 'filament.widgets.worker-tasks-widget';


    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $userId = auth()->id();

        return [
            'pendingTasks' => ProductionStage::where('user_id', $userId)
                ->where('status', 'очікує')
                ->with('production') // Завантаження пов'язаного виробництва
                ->get(),

            'inProgressTasks' => ProductionStage::where('user_id', $userId)
                ->where('status', 'в роботі')
                ->with('production') // Завантаження пов'язаного виробництва
                ->get(),

            'doneTasks' => ProductionStage::where('user_id', $userId)
                ->where('status', 'виготовлено')
                ->latest('date')
                ->take(5)
                ->get(),
        ];
    }

    // public static function canView(): bool
    // {
    //     return auth()->check(); // обмеження на працівника
    // }




    public function startTask($taskId)
    {
        $task = ProductionStage::findOrFail($taskId);
        ProductionService::startStage($task);

    }

    public function completeTask($taskId)
    {
        $task = ProductionStage::findOrFail($taskId);
        ProductionService::endStage($task);
    }
}
