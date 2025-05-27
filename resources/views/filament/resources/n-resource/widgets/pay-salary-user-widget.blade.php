<x-filament-widgets::widget>
    <x-filament::section>
        <x-filament::card>
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-medium text-color-danger">На балансі працівника- {{ $balance }} грн.</div>
                    {{-- <div class="text-sm text-color-info">Виробництво: {{ $task->production->name }}</div> --}}
                    {{-- <div class="text-sm text-color-info">Виконавець: {{ $task->user->name }}</div> --}}
                </div>



                    <x-slot name="title">
                        Виплата зарплати
                    </x-slot>

                        <form wire:submit.prevent="paySalary({{ $userId }})">
                            <div class="mb-4">
                                <label for="selectedWallet" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Виплатити з гаманця</label>
                                <select id="selectedWallet" wire:model="selectedWallet" name="selectedWallet" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-gray-300">
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Сума</label>
                                <input type="number" id="amount" wire:model="amount" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-gray-300" placeholder="Введіть суму" value="{{ $balance }}">
                            </div>
                            @if($balance > 100)
                                <x-filament::button type="submit" color="primary">
                                    Виплатити зарплату
                                </x-filament::button>
                            @endif
                        </form>

            </div>
        </x-filament::card>
    </x-filament::section>
</x-filament-widgets::widget>
