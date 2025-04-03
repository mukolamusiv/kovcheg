<?php

namespace App\Filament\Resources\ProductionResource\Pages;

use App\Filament\Resources\ProductionResource;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Production;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Fieldset;

use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Closure;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Set;

use Filament\Infolists\Components\Actions as BAction;
use Pest\ArchPresets\Custom;

//use Filament\Infolists\Components\Actions;


class ViewProduction extends ViewRecord
{
    protected static string $resource = ProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {

       return $infolist
                ->columns(12)
                ->schema([
                        Section::make('Інформація про продукт')
                        ->columns(2)
                        ->columnSpan(8)
                        ->headerActions([
                            Action::make('printProduction')
                            ->label('Надрукувати інформацію про замовлення')
                            ->icon('heroicon-o-printer')
                            ->color('info')
                            ->url(
                                fn (Production $record): string =>
                                    $record->invoice ? route('ProductionDetail.pdf', ['production' => $record->id]) : '#'
                            ),
                        ])
                        ->schema([
                            TextEntry::make('name')
                                ->label('Назва виробу')
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('description')
                                ->label('Опис'),
                            TextEntry::make('quantity')
                                ->label('Кількість одиниць'),
                            TextEntry::make('price')
                                ->label('Вартість одиниці'),
                            TextEntry::make('status')
                                ->label('Стан виробництва'),
                        ]),


                        Section::make('Клієнт')
                        ->columns(2)
                        ->headerActions([
                            // Action::make('edit_sizes')
                            //     ->label('Редагувати розміри')
                            //     ->icon('heroicon-o-pencil')
                            //     ->color('primary')
                            //     ->form([
                            //         TextInput::make('throat')->label('Горловина')->numeric(),
                            //         TextInput::make('redistribution')->label('Переділ')->numeric(),
                            //         TextInput::make('behind')->label('Зад')->numeric(),
                            //         TextInput::make('hips')->label('Стегна')->numeric(),
                            //         TextInput::make('length')->label('Довжина')->numeric(),
                            //         TextInput::make('sleeve')->label('Рукав')->numeric(),
                            //         TextInput::make('shoulder')->label('Плече')->numeric(),
                            //         TextInput::make('comment')->label('Коментар'),
                            //     ])
                            //     ->action(function (array $data, Production $record): void {
                            //         $record->productionSizes()->updateOrCreate([], $data);
                            //         Notification::make()
                            //             ->title('Розміри оновлено успішно!')
                            //             ->success()
                            //             ->send();
                            //     }),

                                Action::make('sync_sizes')
                                ->label('Синхронізувати розміри')
                                ->icon('heroicon-c-identification')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->action(function (Production $record): void {
                                    if ($record->customer && $record->customer->size) {
                                       // dd($record->customer->size);
                                        $record->productionSizes()->updateOrCreate([], $record->customer->size->toArray()[0]);
                                       // dd($record->productionSizes, $record->customer->size->toArray());
                                        Notification::make()
                                            ->title('Розміри синхронізовано з даними клієнта!')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('У клієнта немає збережених розмірів!')
                                            ->danger()
                                            ->send();
                                    }
                                }),
                            Action::make('add_customer')
                                ->label('Обрати клієнта')
                                ->icon('heroicon-o-user')
                                ->color('info')
                                ->form([
                                    Select::make('customer_id')
                                        ->label('Вибрати клієнта з бази')
                                        ->options(Customer::all()->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        // ->createOptionForm([
                                        //     TextInput::make('name')
                                        //         ->label('Ім\'я')
                                        //         ->required(),
                                        //     TextInput::make('email')
                                        //         ->label('Email')
                                        //         ->email(),
                                        //     TextInput::make('phone')
                                        //         ->label('Телефон'),
                                        //     TextInput::make('address')
                                        //         ->label('Адреса'),
                                        // ])
                                        // ->createOptionAction(function (array $state) {
                                        //     return Customer::create($state)->id;
                                        // })
                                        ->required(),
                                ])
                                ->action(function (array $data, Production $record): void {
                                    $record->customer_id = $data['customer_id'];
                                    $invoice = $record->invoice;
                                    $invoice->customer_id = $record->customer_id;
                                    $invoice->save();
                                    //dd($invoice, $record);
                                    $record->save();


                                }),
                        ])
                        ->columnSpan(4)
                        ->columns(2)
                        ->schema([
                            TextEntry::make('customer.name')
                                ->label('Замовник')
                                ->badge()
                                ->color('danger'),
                            TextEntry::make('customer.email')
                                ->label('Email'),
                            TextEntry::make('customer.phone')
                                ->label('Телефон'),
                            TextEntry::make('customer.address')
                                ->label('Адреса'),


                                // Fieldset::make('Розміри')
                                //     ->schema([
                                //         TextEntry::make('productionSizes.throat')
                                //             ->label('Горловина'),
                                //         TextEntry::make('productionSizes.redistribution')
                                //             ->label('Переділ'),
                                //         TextEntry::make('productionSizes.behind')
                                //             ->label('Зад'),
                                //         TextEntry::make('productionSizes.hips')
                                //             ->label('Стегна'),
                                //         TextEntry::make('productionSizes.length')
                                //             ->label('Довжина'),
                                //         TextEntry::make('productionSizes.sleeve')
                                //             ->label('Рукав'),
                                //         TextEntry::make('productionSizes.shoulder')
                                //             ->label('Плече'),
                                //         TextEntry::make('productionSizes.comment')
                                //             ->label('Коментар'),

                        ]),

                    // Section::make('Клієнт')
                    // ->columns(2)
                    // ->columnSpan(4)
                    // ->schema([
                    //     TextEntry::make('customer.name')
                    //         ->label('Замовник')
                    //         ->badge()
                    //         ->color('danger'),
                    //     TextEntry::make('customer.email')
                    //         ->label('Email'),
                    //     TextEntry::make('customer.phone')
                    //         ->label('Телефон'),
                    //     TextEntry::make('customer.address')
                    //         ->label('Адреса'),
                    // ]),




                    Section::make('Розміри')
                    ->headerActions([
                        Action::make('edit_sizes')
                                ->label('Редагувати розміри')
                                ->icon('heroicon-o-pencil')
                                ->color('primary')
                                ->form([
                                    TextInput::make('throat')->label('Горловина')->numeric(),
                                    TextInput::make('redistribution')->label('Переділ')->numeric(),
                                    TextInput::make('behind')->label('Зад')->numeric(),
                                    TextInput::make('hips')->label('Стегна')->numeric(),
                                    TextInput::make('length')->label('Довжина')->numeric(),
                                    TextInput::make('sleeve')->label('Рукав')->numeric(),
                                    TextInput::make('shoulder')->label('Плече')->numeric(),
                                    TextInput::make('comment')->label('Коментар'),
                                ])
                                ->action(function (array $data, Production $record): void {
                                    $record->productionSizes()->updateOrCreate([], $data);
                                    Notification::make()
                                        ->title('Розміри оновлено успішно!')
                                        ->success()
                                        ->send();
                                }),
                    ])
                    ->schema([
                        TextEntry::make('productionSizes.throat')
                        ->label('Горловина'),
                        TextEntry::make('productionSizes.redistribution')
                            ->label('Переділ'),
                        TextEntry::make('productionSizes.behind')
                            ->label('Зад'),
                        TextEntry::make('productionSizes.hips')
                            ->label('Стегна'),
                        TextEntry::make('productionSizes.length')
                            ->label('Довжина'),
                        TextEntry::make('productionSizes.sleeve')
                            ->label('Рукав'),
                        TextEntry::make('productionSizes.shoulder')
                            ->label('Плече'),
                        TextEntry::make('productionSizes.comment')
                            ->label('Коментар'),
                    ])
                    ->columnSpan(12)
                    ->columns(4),




                        Section::make('Етапи виробництва')
                        ->headerActions([
                            Action::make('assign_users')
                                ->label('Призначити відповідальних')
                                ->icon('heroicon-o-user-group')
                                ->color('primary')
                                ->form(function (Production $record) {
                                    return $record->productionStages->map(function ($stage) {
                                        return Select::make("stages.{$stage->id}.user_id")
                                            ->label("Відповідальний для етапу: {$stage->name}")
                                            ->options(User::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->required();
                                    })->toArray();
                                })
                                ->action(function (array $data, Production $record): void {
                                    foreach ($data['stages'] as $stageId => $stageData) {
                                        $stage = $record->productionStages->find($stageId);
                                        if ($stage) {
                                            $stage->user_id = $stageData['user_id'];
                                            $stage->save();
                                        }
                                    }
                                    Notification::make()
                                        ->title('Відповідальних користувачів оновлено успішно!')
                                        ->success()
                                        ->send();
                                }),
                        ])
                            ->schema([
                                RepeatableEntry::make('productionStages')
                                ->label('Етапи виробництва')
                                ->columnSpan(12)
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('name')->label('Назва етапу'),
                                    TextEntry::make('status')->label('Статус'),
                                    TextEntry::make('date')->label('Дата завершення'),
                                    TextEntry::make('user.name')->label('Працівник'),
                                    ViewEntry::make('user')
                                    ->label('')
                                    ->view('components.user-photo', [
                                        'profile_photo_path' => $this->record->user?->profile_photo_path ?? null,
                                    ]),
                                ]),
                        ])
                        ->columnSpan(6)
                        ->columns(2),

                        Section::make('Матеріали виробництва')
                            ->schema([
                                RepeatableEntry::make('productionMaterials')
                                ->label('Матеріали')
                                ->columnSpan(12)
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('material.name')
                                        ->label('Матеріал'),
                                    TextEntry::make('quantity')
                                        ->label('Кількість'),
                                    TextEntry::make('description')
                                        ->label('Коментар'),
                                    TextEntry::make('date_writing_off')
                                        ->label('Дата списання'),
                                ]),
                        ])

                        ->columnSpan(6)
                        ->columns(2),


                    Section::make('Накладна та фінанси')
                        ->visible(fn (Production $record) => $record->invoice !== null)
                        ->columns(5)
                        ->headerActions([
                            Action::make('printInvoice')
                            ->label('Надрукувати накладну')
                            ->icon('heroicon-o-printer')
                            ->color('info')
                            ->url(
                                fn (Production $record): string =>
                                    $record->invoice ? route('invoice.pdf', ['invoice' => $record->invoice->id]) : '#'
                            ),

                        Action::make('reconcileFinances')
                            ->label('Перерахувати фінанси')
                            ->icon('heroicon-s-variable')
                            ->color('danger')
                            ->action(function (array $data, Production $record): void {
                                $record->invoice->reconcileFinances();
                                //dd($data, $record->invoice);
                               // $transaction = Transaction::customer_pay_transaction($data, $record->customer, $record->invoice);
                                //dd($transaction);

                                //$daya->custumer;
                            }),

                            Action::make('custumer_pay')
                                    ->label('Внести оплату')
                                    //->hidden(fn (Production $record): bool => $record->invoice->due == 0 ?? !isset($record->invoice->due))
                                    ->icon('heroicon-s-currency-euro')
                                    ->color('success')
                                    ->requiresConfirmation()
                                    ->form([
                                        Select::make('account_id')
                                            ->label('Гроші на рахунок')
                                            ->options(Account::query()->where(['owner_id' => null])->pluck('name', 'id'))
                                            ->required(),
                                        TextInput::make('amount')
                                            ->numeric()
                                            ->maxValue($this->record->invoice->due ?? 100)
                                            ->default($this->record->invoice->due ?? 100)
                                            ->label('Сума'),
                                            TextInput::make('description')
                                    ])
                                    ->action(function (array $data, Production $record): void {
                                        //dd($data, $record->invoice);
                                        $transaction = Transaction::customer_pay_transaction($data, $record->customer, $record->invoice);
                                        //dd($transaction);

                                        //$daya->custumer;
                                    }),
                        ])
                        ->columnSpan(12)
                        ->schema([
                            //reconcileFinances
                            TextEntry::make('invoice.invoice_number')
                                ->label('Номер накладної')
                                ->badge()
                                ->color('info'),
                            TextEntry::make('invoice.total')
                                ->label('Сума')
                                ->badge()
                                ->color('success'),
                            TextEntry::make('invoice.paid')
                                ->label('Оплачено')
                                ->badge()
                                ->color('warning'),
                            TextEntry::make('invoice.due')
                                ->label('Заборгованість')
                                ->badge()
                                ->color('danger'),
                            TextEntry::make('invoice.payment_status')
                                ->badge()
                                ->label('Статус оплати')
                                ->color(fn (string $state): string => match ($state) {
                                    'завдататок' => 'info',
                                    'частково оплачено' => 'warning',
                                    'оплачено' => 'success',
                                    'не оплачено' => 'danger',
                                }),
                                //->label('Оплачено'),
                                RepeatableEntry::make('invoice.transactions')
                                    ->label('Транзакції')
                                    ->columnSpan(12)
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('reference_number'),
                                        TextEntry::make('description'),
                                        TextEntry::make('debet.amount'),
                                        TextEntry::make('transaction_date'),
                                    ]),

                            // BAction::make([
                            //     Action::make('star')
                            //         ->label('Оплатити')
                            //         ->icon('heroicon-s-currency-euro')
                            //         ->color('danger')
                            //         ->requiresConfirmation()
                            //         ->form([
                            //             Select::make('account')
                            //                 ->label('Гроші на рахунок')
                            //                 ->options(Account::query()->where(['owner_id' => null])->pluck('name', 'id'))
                            //                 ->required(),
                            //             TextInput::make('price')
                            //         ])
                            //         ->action(function (array $data, Production $record): void {
                            //             dd($data, $record->invoice());
                            //         }),
                            // ]),
                        ]),
                        Section::make('Накладна на сировину')
                            ->headerActions([
                                Action::make('material_off')
                                ->label('Списати матеріали')
                                ->icon('heroicon-o-document-text')
                                ->color('danger')
                                ->form(function (Production $record) {
                                    return array_merge(
                                        [
                                            Select::make('warehouse_id')
                                                ->label('Виберіть склад')
                                                ->options(\App\Models\Warehouse::pluck('name', 'id'))
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(fn (Get $get, $state) => $get('warehouse_id')),
                                        ],
                                        $record->invoice_off->invoiceItems->map(function ($item) {
                                            return TextInput::make("materials.{$item->id}.quantity")
                                                ->label("Списати для {$item->material->name}")
                                                ->numeric()
                                                ->default($item->quantity)
                                                ->required()
                                                ->reactive()
                                                ->helperText(fn (Get $get) => "Доступно: " . $item->material->getStockInWarehouse($get('warehouse_id')))
                                                ->rule(function ($state, Get $get) use ($item) {
                                                    $warehouseStock = $item->material->getStockInWarehouse($get('warehouse_id'));

                                                    if ($state > $warehouseStock) {
                                                        Notification::make()
                                                        ->title('Недостатньо матеріалів на складі!')
                                                        ->danger()
                                                        ->send();
                                                        //return false;
                                                        throw \Illuminate\Validation\ValidationException::withMessages([
                                                            "materials.{$item->id}.quantity" => "Недостатньо матеріалу {$item->material->name} на складі. Доступно: {$warehouseStock}.",
                                                        ]);

                                                    }
                                                });
                                        })->toArray()
                                    );
                                })
                                ->action(function (array $data, Production $record): void {
                                    dd($record,$data);
                                    foreach ($data['materials'] as $itemId => $quantity) {
                                        $invoiceItem = $record->invoice_off->invoiceItems->find($itemId);

                                        if ($invoiceItem) {
                                            if ($quantity > $invoiceItem->quantity) {
                                                throw new \Exception("Списана кількість для {$invoiceItem->material->name} не може перевищувати доступну кількість.");
                                            }

                                            // Update the quantity in the invoice item
                                            $invoiceItem->quantity = $quantity;

                                            $invoiceItem->save();

                                            // Optionally, update the warehouse stock or perform other operations
                                        }
                                    }
                                }),
                            ])
                                //->visible(fn (Production $record) => $record->invoice !== null)
                            ->columns(5)
                            ->columnSpan(12)
                            ->schema([
                                TextEntry::make('invoice_off.invoice_number')
                                ->label('Номер накладної')
                                ->badge()
                                ->color('info'),
                                TextEntry::make('invoice_off.total')
                                    ->label('Сума')
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('invoice_off.paid')
                                    ->label('Оплачено')
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('invoice_off.due')
                                    ->label('Заборгованість')
                                    ->badge()
                                    ->color('danger'),
                                TextEntry::make('invoice_off.payment_status')
                                    ->badge()
                                    ->label('Статус оплати')
                                    ->color(fn (string $state): string => match ($state) {
                                        'завдататок' => 'gray',
                                        'часткова оплата' => 'warning',
                                        'оплачено' => 'success',
                                        'не оплачено' => 'danger',
                                    }),
                                RepeatableEntry::make('productionMaterials')
                                ->label('Метеріали')
                                ->columnSpan(12)
                                ->columns(4)
                                ->schema([
                                    TextEntry::make('material.name')->label('Назва матеріалу'),
                                    TextEntry::make('quantity')->label('Кількість'),
                                    TextEntry::make('price')->label('Ціна'),
                                    TextEntry::make('total')
                                        ->label('Вартість')
                                        ->default(fn ($record) => $record->quantity * $record->price),
                                ]),
                                ])
                ]);
       // return //$infolist

    }
}


// BAction::make([
//     Action::make('star')
//         ->label('Оплатити')
//         ->icon('heroicon-s-currency-euro')
//         ->color('danger')
//         ->requiresConfirmation()
//         ->form([
//             Select::make('account')
//                 ->label('Гроші на рахунок')
//                 ->options(Account::query()->where(['owner_id' => null])->pluck('name', 'id'))
//                 ->required(),
//             TextInput::make('price')
//         ])
//         ->action(function (array $data, Production $record): void {

//             dd($data,$record->invoice());

//             // $record->author()->associate($data['authorId']);
//             // $record->save();
//         })
//         // ->action(function (Production $star) {
//         //     $star();
//         // })
// ]),
