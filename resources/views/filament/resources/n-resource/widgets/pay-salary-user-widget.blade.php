<x-filament-widgets::widget>
    <x-filament::section>
        <x-filament::card>
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-medium text-color-danger">На балансі працівника- {{ $balance }}</div>
                    {{-- <div class="text-sm text-color-info">Виробництво: {{ $task->production->name }}</div> --}}
                    {{-- <div class="text-sm text-color-info">Виконавець: {{ $task->user->name }}</div> --}}
                </div>
                <x-filament::button color="primary" size="sm" wire:click="$set('isModalOpen', true)">
                    Виплатити зарплату
                </x-filament::button>

                <x-filament::modal wire:model="isModalOpen">
                    <x-slot name="title">
                        Вибір гаманця
                    </x-slot>

                    <x-slot name="content">
                        <form wire:submit.prevent="startTask({{ $userId }})">
                            <div class="mb-4">
                                <select wire:model="selectedWallet" label="Гаманець">
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-filament::button type="submit" color="primary">
                                Підтвердити
                            </x-filament::button>
                        </form>
                    </x-slot>
                </x-filament::modal>
            </div>
        </x-filament::card>
    </x-filament::section>
</x-filament-widgets::widget>
