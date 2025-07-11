<x-filament-widgets::widget>
    <x-filament::section>
        <x-filament::widget>
            <x-filament::card>
                <h2 class="text-xl font-bold mb-4">Моя робота</h2>

                {{-- Очікують --}}
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Очікує на початок</h3>
                    @foreach ($pendingTasks as $task)
                        <x-filament::card class="mb-2">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-medium">Етап - {{ $task->name }}</div>
                                    {{-- <div class="text-sm text-color-info">Виробництво: {{ $task->production->name }}</div> --}}
                                    <div class="text-sm text-color-info">Виконавець: {{ $task->user->name }}</div>
                                </div>
                                <form wire:submit.prevent="startTask({{ $task->id }})">
                                    <x-filament::button type="submit" color="primary" size="sm">
                                        Почати
                                    </x-filament::button>
                                </form>
                            </div>
                        </x-filament::card>
                    @endforeach
                </div>

                {{-- В роботі --}}
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">В роботі</h3>
                    @foreach ($inProgressTasks as $task)
                    @if(!$task->production)
                                @continue
                            @endif
                        <x-filament::card class="mb-2">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-medium">Етап - {{ $task->name }}</div>
                                    <div class="text-sm text-color-info">Виробництво: {{ $task->production->name }}</div>
                                    <div class="text-sm text-color-info">Виконавець: {{ $task->user->name }}</div>
                                </div>
                                <form wire:submit.prevent="completeTask({{ $task->id }})">
                                    <x-filament::button type="submit" color="success" size="sm">
                                        Завершити
                                    </x-filament::button>
                                </form>
                            </div>
                        </x-filament::card>
                        <br>
                    @endforeach
                </div>

                {{-- Завершені --}}
                {{-- <div>
                    <h3 class="font-semibold mb-2">Завершені</h3>
                    @foreach ($doneTasks as $task)
                        <x-filament::card class="mb-2 opacity-80">
                            <div>
                                <div class="font-medium">{{ $task->name }}</div>
                                <div class="text-sm text-gray-500">Виконано: {{ $task->date?->format('d.m.Y H:i') }}</div>
                            </div>
                        </x-filament::card>
                    @endforeach
                </div> --}}
            </x-filament::card>
        </x-filament::widget>
    </x-filament::section>
</x-filament-widgets::widget>
