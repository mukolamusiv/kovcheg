<?php

namespace App\Filament\Resources\ProductionResource\Pages;

use App\Filament\Resources\ProductionResource;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Production;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Filament\HasInvoiceSection;
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
use Filament\Infolists\Components\Tabs;
use Pest\ArchPresets\Custom;
use Filament\Support\Enums\Alignment;
//use Filament\Infolists\Components\Actions;


class ViewProduction extends ViewRecord
{
    use HasInvoiceSection;
    protected static string $resource = ProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {

        $record = $this->getRecord(); // поточний Production
        $customer = Customer::find($record->customer_id); // отримуємо клієнта
        $user = User::find($record->user_id); // отримуємо відповідального
        $invoice = Invoice::find($record->invoice_id); // отримуємо рахунок
        $transactions = Transaction::where('invoice_id', $record->invoice_id)->get(); // отримуємо транзакції
        $totalPaid = $transactions->sum('amount'); // сума всіх транзакцій
        $totalPaid = $totalPaid ? $totalPaid : 0; // якщо сума транзакцій не визначена, то 0
        $total = $record->price; // загальна сума замовлення
        $productionStages = $record->productionStages; // етапи виробництва



        return $infolist
                ->columns(12)
                ->schema([
                   // $stages,
                    Section::make('Замовлення на виробництво')
                        ->collapsed(false)
                        ->columns(12)
                        ->columnSpan(12)
                        ->headerActions([
                            Action::make('Star')
                                ->label('Оплатити')
                                ->icon('heroicon-s-currency-euro')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->form([
                                    Select::make('account')
                                        ->label('Гроші на рахунок')
                                        ->options(Account::query()->where(['owner_id' => null])->pluck('name', 'id'))
                                        ->required(),
                                    TextInput::make('price')
                                        ->label('Сума оплати')
                                        ->required()
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue($record->price)
                                        ->default($record->price)
                                ])
                                ->action(function (array $data, Production $record): void {

                                    // dd($data,$record->invoice());

                                    // $record->author()->associate($data['authorId']);
                                    // $record->save();
                                })
                        ])
                        ->schema([
                            Fieldset::make('production')
                                ->label('Виробництво')
                                ->columns(5)
                                ->schema([
                                    TextEntry::make('name')
                                        ->label('Назва')
                                        ->weight('bold'),

                                    TextEntry::make('description')
                                        ->label('Опис'),

                                    TextEntry::make('status')
                                        ->label('Статус')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'створено' => 'info',
                                            'в роботі' => 'warring',
                                            'виготовлено' => 'success',
                                            'скасовано' => 'danger',
                                            default => 'gray',
                                        }),

                                    TextEntry::make('type')
                                        ->label('Тип')
                                        ->badge()
                                        ->color(fn ($state) => $state === 'замовлення' ? 'success' : 'info'),

                                    TextEntry::make('pay')
                                        ->label('Оплата')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'не оплачено' => 'danger',
                                            'завдататок' => 'warring',
                                            'оплачено' => 'success',
                                            'часткова оплата' => 'warring',
                                            default => 'info',
                                        }),

                                    TextEntry::make('customer.name')
                                        ->label('Клієнт'),

                                    TextEntry::make('user.name')
                                        ->label('Відповідальний'),

                                    TextEntry::make('quantity')
                                        ->label('Кількість'),

                                    TextEntry::make('price')
                                        ->label('Ціна')
                                        ->prefix('₴'),

                                    TextEntry::make('production_date')
                                        ->label('Дата виготовлення')
                                        ->date(),

                                    TextEntry::make('image')
                                        ->label('Зображення')
                                        ->hidden(fn ($state) => empty($state)),
                                ]),
                            Fieldset::make('customer')
                                ->label('Клієнт')
                                ->columns(12)
                                ->schema($this->getCustomer($record),),
                        ])
                        ->columns([
                            'sm' => 2,
                            'lg' => 4,
                        ]),





                    Section::make('Процес виробництва')
                        ->description('Деталі виробництва')
                        ->collapsed(false)
                        ->columns(12)
                        ->columnSpan(6)
                        ->headerActions([
                            Action::make('addStage')
                                ->label('Додати етап')
                                ->icon('heroicon-o-plus')
                                ->color('success')
                                ->form([
                                    TextInput::make('name')
                                        ->label('Назва етапу')
                                        ->required(),
                                    TextInput::make('description')
                                        ->label('Опис етапу')
                                        ->required(),
                                    Select::make('status')
                                        ->label('Статус етапу')
                                        ->options([
                                            'очікує' => 'очікує',
                                            'в роботі' => 'в роботі',
                                            'виготовлено' => 'виготовлено',
                                            'скасовано' => 'скасовано',
                                        ])
                                        ->default('очікує')
                                        ->required(),
                                    TextInput::make('paid_worker')
                                        ->label('Оплата працівнику')
                                        ->required()
                                        ->numeric()
                                        ->hidden(auth()->user()->role !== 'admin')
                                        ->helperText('Оплата працівнику за виконану роботу'),
                                    Select::make('user_id')
                                        ->label('Виконавець')
                                        ->options(User::pluck('name', 'id'))
                                        ->required(),
                                ])
                                ->action(function (array $data, Production $record): void {
                                    $stage = new \App\Models\ProductionStage();
                                    $stage->production_id = $record->id;
                                    $stage->name = $data['name'];
                                    $stage->description = $data['description'];
                                    $stage->status = $data['status'];
                                    $stage->paid_worker = $data['paid_worker'];
                                    $stage->user_id = $data['user_id'];

                                    try {
                                        if ($stage->save()) {
                                            Notification::make()
                                                ->title('Новий етап виробництва успішно додано!')
                                                ->success()
                                                ->send();
                                        } else {
                                            Notification::make()
                                                ->title('Не вдалося додати новий етап виробництва!')
                                                ->danger()
                                                ->send();
                                        }
                                    } catch (\Exception $e) {
                                        Notification::make()
                                            ->title('Помилка збереження: ' . $e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                })
                        ])
                        ->schema($this->getStages($record))
                        ->columns([
                            'sm' => 2,
                            'lg' => 4,
                        ]),
                    Section::make('Матеріали виробництва')
                        ->description('Матеріали, які використовуються у виробництві')
                        ->collapsed(false)
                        ->columns(12)
                        ->columnSpan(6)
                        ->headerActions([
                            Action::make('addMaterial')
                                ->label('Додати матеріал')
                                ->icon('heroicon-o-plus')
                                ->color('success')
                                ->form([
                                    Select::make('material_id')
                                        ->label('Матеріал')
                                        ->options(\App\Models\Material::pluck('name', 'id'))
                                        ->required(),
                                    TextInput::make('quantity')
                                        ->label('Кількість')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1),
                                    TextInput::make('description')
                                        ->label('Опис')
                                        ->nullable(),
                                ])
                                ->action(function (array $data, Production $record): void {
                                    $material = new \App\Models\ProductionMaterial();
                                    $material->production_id = $record->id;
                                    $material->material_id = $data['material_id'];
                                    $material->quantity = $data['quantity'];
                                    //$material->price = $data['price'];
                                    $material->description = $data['description'];

                                    try {
                                        if ($material->save()) {
                                            Notification::make()
                                                ->title('Матеріал успішно додано до виробництва!')
                                                ->success()
                                                ->send();
                                        } else {
                                            Notification::make()
                                                ->title('Не вдалося додати матеріал до виробництва!')
                                                ->danger()
                                                ->send();
                                        }
                                    } catch (\Exception $e) {
                                        Notification::make()
                                            ->title('Помилка збереження: ' . $e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                })
                        ])
                        ->schema($this->getMaterials($record))
                        ->columns([
                            'sm' => 2,
                            'lg' => 4,
                        ]),
                    Section::make('Накладні')
                        ->description('Накладні, які пов\'язані з цим виробництвом')
                        ->collapsed(false)
                        ->columns(12)
                        ->columnSpan(12)
                        ->headerActions([
                        ])
                        ->schema($this->getInvoices($record))
                        ->columns([
                           'sm' => 2,
                           'lg' => 4,
                        ])



                ]);

       // return //$infolist

    }





    private function getCustomer(Production $record): array
    {
        $customer = $record->customer; // отримуємо клієнта
        $data = [];
        //dd($customer);
        if(is_null($customer)) {
            $data[] = Section::make('Клієнт - ')
                ->description('Немає клієнта')
                ->columnSpanFull();
        }else {
            $data[] = Section::make('Клієнт - ' . $customer->name)
                ->description($customer->email ?? 'Немає email')
                ->columnSpan(4)
                ->headerActions([
                    Action::make('selectCustomer')
                        ->label('Вибрати клієнта')
                        ->icon('heroicon-o-user')
                        ->color('success')
                        ->form([
                            Select::make('customer_id')
                                ->label('Клієнт')
                                ->options(Customer::pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (array $data, Production $record): void {
                            $record->customer_id = $data['customer_id'];

                            try {
                                if ($record->save()) {
                                    Notification::make()
                                        ->title('Клієнта успішно змінено!')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Не вдалося змінити клієнта!')
                                        ->danger()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Помилка збереження: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                ])
                ->schema([
                    TextEntry::make($customer->name ?? '')
                        ->label('Ім\'я клієнта')
                        ->default($customer->name),

                    TextEntry::make($customer->email ?? '')
                        ->label('Email клієнта')
                        ->default($customer->email),
                ]);


            $data[] = Section::make('Розміри клієнта')
                ->description('Розміри клієнта')
                ->columnSpan(8)
                ->headerActions([
                    Action::make('selectCustomerSize')
                        ->label('Вибрати розміри')
                        ->icon('heroicon-o-user')
                        ->color('success')
                        ->form([
                            Select::make('customer_size_id')
                                ->label('Розміри клієнта')
                                ->options(\App\Models\CustomerSize::where('customer_id', $customer->id)->pluck('id', 'id'))
                                ->required(),
                        ])
                        ->action(function (array $data, Production $record): void {
                            $record->customer_size_id = $data['customer_size_id'];

                            try {
                                if ($record->save()) {
                                    Notification::make()
                                        ->title('Розміри клієнта успішно змінено!')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Не вдалося змінити розміри клієнта!')
                                        ->danger()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Помилка збереження: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                ])
                ->schema([
                    TextEntry::make('throat')
                        ->label('Обхват горла')
                        ->default($record->customerSize->throat ?? '-'),

                    TextEntry::make('redistribution')
                        ->label('Обхват грудей')
                        ->default($record->customerSize->redistribution ?? '-'),

                    TextEntry::make('behind')
                        ->label('Обхват талії')
                        ->default($record->customerSize->behind ?? '-'),

                    TextEntry::make('hips')
                        ->label('Обхват стегон')
                        ->default($record->customerSize->hips ?? '-'),

                    TextEntry::make('length')
                        ->label('Довжина виробу')
                        ->default($record->customerSize->length ?? '-'),

                    TextEntry::make('sleeve')
                        ->label('Довжина рукава')
                        ->default($record->customerSize->sleeve ?? '-'),

                    TextEntry::make('shoulder')
                        ->label('Ширина плечей')
                        ->default($record->customerSize->shoulder ?? '-'),

                    TextEntry::make('comment')
                        ->label('Коментар')
                        ->default($record->customerSize->comment ?? '-'),
                ])
                ->columns([
                   'sm' => 2,
                   'lg' => 4,
                ]);
        }
        return $data;
    }


    /**
     * Отримуємо накладні
     *
     * @param Production $record
     * @return array
     */
    // отримуємо накладні
    private function getInvoices(Production $record): array
    {
        $invoices = $record->invoice; // отримуємо накладні
        $invoice = [];
        //dd($invoices);
        if(is_null($invoices)) {
            $invoice[] = Section::make('Накладна - ')
                ->description('Немає накладних')
                ->columnSpanFull();
        }else {

                $invoice[] = Section::make('Накладна та фінанси')
                    ->visible(fn () => $invoices !== null)
                    ->collapsed(false)
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                    Section::make('Інформація про накладну')
                        ->description('Загально')
                        ->columns(2)
                        ->columnSpan(6, 12)
                        ->footerActions([
                        Action::make('move_invoice' . $invoices->id)
                            ->label('Провести накладну')
                            ->icon('heroicon-o-check')
                            ->visible(fn () => $invoices->status === 'створено')
                            ->color('success')
                            ->action(fn () => $invoices->moveInvoice()),
                        Action::make('cancel_invoice' . $invoices->id)
                            ->label('Скасувати проведення')
                            ->visible(fn () => $invoices->status === 'проведено')
                            ->icon('heroicon-o-x-mark')
                            ->color('danger')
                            ->action(fn () => $invoices->cancelInvoice()),
                        Action::make('printInvoice_' . $invoices->id)
                            ->label('Надрукувати накладну')
                            ->icon('heroicon-o-printer')
                            ->color('info')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
                        ])
                        ->footerActionsAlignment(Alignment::Center)
                        ->schema([
                        TextEntry::make('invoice_number')
                            ->label('Номер накладної')
                            ->default($invoices->invoice_number)
                            ->badge()
                            ->color('info'),
                        TextEntry::make('invoice_date')
                            ->label('Дата накладної')
                            ->default($invoices->invoice_date)
                            ->badge()
                            ->color('info'),
                        TextEntry::make('type')->label('Тип')->default($invoices->type)->badge()->color('primary'),
                        TextEntry::make('status')->label('Статус')->default($invoices->status)->badge()->color(fn () => match ($invoices->status) {
                            'створено' => 'info',
                            'проведено' => 'success',
                            'скасовано' => 'danger',
                        }),
                        TextEntry::make('invoice.notes')
                            ->label('Примітки'),
                        ]),
                    Section::make('Фінанси')
                        ->description('Базова інформація про накладну')
                        ->columns(2)
                        ->columnSpan(6, 12)
                        ->footerActions([
                        Action::make('add_discount' . $invoices->id)
                            ->label('Додати знижку')
                            ->icon('heroicon-o-printer')
                            ->color('warning')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
                        Action::make('pay' . $invoices->id)
                            ->label('Оплатити')
                            ->icon('heroicon-o-printer')
                            ->color('success')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
                        ])
                        ->footerActionsAlignment(Alignment::Center)
                        ->schema([
                        TextEntry::make('total')->label('Сума')->default($invoices->total)->badge()->color('success'),
                        TextEntry::make('paid')->label('Оплачено')->default($invoices->paid)->badge()->color('warning'),
                        TextEntry::make('due')->label('Заборгованість')->default($invoices->due)->badge()->color('danger'),
                        TextEntry::make('discount')->label('Знижка')->default($invoices->discount)->badge()->color('success'),
                        TextEntry::make('payment_status')
                            ->badge()
                            ->label('Статус оплати')
                            ->default($invoices->payment_status)
                            ->color(fn () => match ($invoices->payment_status) {
                            'завдаток' => 'info',
                            'частково оплачено' => 'warning',
                            'оплачено' => 'success',
                            'не оплачено' => 'danger',
                            }),
                        ]),
                    RepeatableEntry::make('produc')
                        ->default(fn () => $invoices->invoiceProductionItems ?? [])
                        ->label('Позиції виробництва')
                        ->columnSpan(12)
                        ->columns(4)
                        ->schema([
                        TextEntry::make('quantity')->label('Кількість'),
                        TextEntry::make('price')->label('Ціна'),
                        TextEntry::make('total')->label('Сума'),
                        TextEntry::make('production.name')->label('Виробництво'),
                        TextEntry::make('production.description')->label('Опис'),
                        TextEntry::make('production.status')->label('Статус'),
                        TextEntry::make('production.type')->label('Тип'),
                        ]),
                    RepeatableEntry::make('invoiceItems')
                        ->label('Позиції накладної')
                        ->columns(4)
                        ->columnSpanFull()
                        ->schema([
                        TextEntry::make('quantity')->label('Кількість'),
                        TextEntry::make('price')->label('Ціна'),
                        TextEntry::make('total')->label('Сума'),
                        ]),
                    Section::make('Транзакції')
                        ->description('Усі транзакції накладної')
                        ->collapsed(true)
                        ->columns(2)
                        ->columnSpanFull()
                        ->footerActions([
                        Action::make('add_discount' . $invoices->id)
                            ->label('Додати знижку')
                            ->icon('heroicon-o-printer')
                            ->color('warning')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
                        Action::make('pay' . $invoices->id)
                            ->label('Оплатити')
                            ->icon('heroicon-o-printer')
                            ->color('success')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
                        ])
                        ->schema([
                        RepeatableEntry::make('transactions')
                            ->label('Транзакції')
                            ->columnSpan(12)
                            ->columns(4)
                            ->schema([
                            TextEntry::make('reference_number'),
                            TextEntry::make('description'),
                            TextEntry::make('debet.amount'),
                            TextEntry::make('transaction_date'),
                            ]),
                        ]),
                    ])
                    ->headerActions([
                    Action::make('reconcileFinances_' . $invoices->id)
                        ->label('Перерахувати фінанси')
                        ->icon('heroicon-s-variable')
                        ->color('danger')
                        ->action(fn () => $invoices->reconcileFinances()),
                    Action::make('customer_pay_' . $invoices->id)
                        ->label('Внести оплату')
                        ->icon('heroicon-s-currency-euro')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                        Select::make('account_id')
                            ->label('Гроші на рахунок')
                            ->options(Account::query()->whereNull('owner_id')->pluck('name', 'id'))
                            ->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->maxValue($invoices->due ?? 100)
                            ->default($invoices->due ?? 100)
                            ->label('Сума'),
                        TextInput::make('description')
                            ->label('Опис'),
                        ])
                        ->action(function (array $data) use ($invoices) {
                        Transaction::customer_pay_transaction($data, $invoices->customer, $invoices);
                        }),
                    ]);
                }
        return $invoice;
    }


    /**
     * Отримуємо етапи виробництва
     *
     * @param Production $record
     * @return array
     */
    // отримуємо етапи виробництва
    private function getStages(Production $record): array
    {
        $productionStages = $record->productionStages; // отримуємо етапи виробництва
        $stages = [];
        foreach ($productionStages as $stage) {
            $stages[] = Section::make('Етап виробництва - ' . $stage->name)
                            ->collapsed($stage->status !== 'в роботі')
                            ->columnSpanFull()
                            ->headerActions([
                                Action::make('edit'.$stage->id)
                                    ->label('Редагувати')
                                    ->hidden($stage->status === 'виготовлено')
                                    ->icon('heroicon-o-pencil')
                                    ->color('primary')
                                    ->form([
                                        TextInput::make('name')
                                            ->label('Назва етапу')
                                            ->required()
                                            ->default($stage->name),
                                        TextInput::make('description')
                                            ->label('Опис етапу')
                                            ->required()
                                            ->default($stage->description),
                                        Select::make('status')
                                            ->label('Статус етапу')
                                            ->options([
                                                'очікує' => 'очікує',
                                                'в роботі' => 'в роботі',
                                                'виготовлено' => 'виготовлено',
                                                'скасовано' => 'скасовано',
                                            ])
                                            ->default($stage->status)
                                            ->required(),
                                        TextInput::make('paid_worker')
                                            ->label('Оплата працівнику')
                                            ->required()
                                            ->hidden(auth()->user()->role !== 'admin')
                                            ->helperText('Оплата працівнику за виконану роботу')
                                            ->default($stage->paid_worker)
                                            ->numeric(),
                                        Select::make('user_id')
                                            ->label('Виконавець')
                                            ->options(User::pluck('name', 'id'))
                                            ->default($stage->user_id)
                                            ->required(),
                                    ])
                                    ->action(function (array $data, Production $record) use ($stage): void {
                                        //$stage = $data;
                                        $stage->user_id = $data['user_id'];
                                        $stage->paid_worker = $data['paid_worker'];
                                        $stage->status = $data['status'];
                                        $stage->name = $data['name'];
                                        $stage->description = $data['description'];

                                        try {
                                            if ($stage->save()) {
                                                Notification::make()
                                                    ->title('Оновлено один з етапів виробництва!')
                                                    ->success()
                                                    ->send();
                                            } else {
                                                Notification::make()
                                                    ->title('Не вдалося оновити етап виробництва!')
                                                    ->danger()
                                                    ->send();
                                            }
                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->title('Помилка збереження: ' . $e->getMessage())
                                                ->danger()
                                                ->send();
                                        }

                                        // dd($data,$record,$stage);

                                        // $record->author()->associate($data['authorId']);
                                        // $record->save();
                                    })
                                ])

                            ->schema([
                                TextEntry::make($stage->name ?? '')
                                    ->label('Назва')
                                    ->default($stage->name)
                                    ->weight('bold'),

                                TextEntry::make($stage->description ?? '')
                                    ->default($stage->description)
                                    ->label('Примітка'),

                                TextEntry::make($stage->status ?? '')
                                    ->label('Статус')
                                    ->badge()
                                    ->default($stage->status)
                                    ->color(fn ($state) => match ($state) {
                                        'створено' => 'info',
                                        'в роботі' => 'warning',
                                        'виготовлено' => 'success',
                                        'скасовано' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make($stage->date ?? '')
                                    ->label('Дата')
                                    ->default($stage->date)
                                    ->date(),

                                TextEntry::make('paid_worker')
                                    ->label('Оплата')
                                    ->hidden(auth()->user()->role !== 'admin' && auth()->user()->id !== $stage->user_id)
                                    ->default($stage->paid_worker)
                                    ->numeric(),

                                TextEntry::make($stage->user->name ?? '')
                                    ->label('Виконавець')
                                    ->default($stage->user->name)
                                    ->weight('bold'),
                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ]);
        }
        return $stages;
    }



    /**
     * Отримуємо матеріали виробництва
     *
     * @param Production $record
     * @return array
     */
    // отримуємо матеріали виробництва
    private function getMaterials(Production $record): array
    {
        $productionMaterials = $record->productionMaterials; // отримуємо етапи виробництва
        $materials = [];
        foreach ($productionMaterials as $material) {
            $materials[] = Section::make('Матеріал - ' . $material->material->name)
                    ->collapsed($material->date_writing_off !== null)
                    ->description($material->date_writing_off ? 'Списано ' . $material->quantity . ' одиниць' : 'Не списано')
                    ->columnSpanFull()
                    ->headerActions([
                    Action::make('edit'.$material->id)
                        ->label('Редагувати')
                        ->hidden($material->date_writing_off !== null)
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->form([
                        TextInput::make('quantity')
                            ->label('Кількість')
                            ->required()
                            ->default($material->quantity)
                            ->numeric(),
                        // TextInput::make('price')
                        //     ->label('Ціна')
                        //     ->required()
                        //     ->default($material->price)
                        //     ->numeric(),
                        TextInput::make('description')
                            ->label('Опис')
                            ->default($material->description),
                        // TextInput::make('date_writing_off')
                        //     ->label('Дата списання')
                        //     ->default($material->date_writing_off),
                        ])
                        ->action(function (array $data, Production $record) use ($material): void {
                            $material->quantity = $data['quantity'];
                            //$material->price = $data['price'];
                            $material->description = $data['description'];
                            //$material->date_writing_off = $data['date_writing_off'];

                        try {
                            if ($material->save()) {
                            Notification::make()
                                ->title('Оновлено матеріал виробництва!')
                                ->success()
                                ->send();
                            } else {
                            Notification::make()
                                ->title('Не вдалося оновити матеріал виробництва!')
                                ->danger()
                                ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                            ->title('Помилка збереження: ' . $e->getMessage())
                            ->danger()
                            ->send();
                        }
                        })
                    ])

                    ->schema([
                    TextEntry::make($material->material->name ?? '')
                        ->label('Назва матеріалу')
                        ->default($material->material->name)
                        ->weight('bold'),

                    TextEntry::make($material->quantity ?? '')
                        ->label('Кількість')
                        ->default($material->quantity)
                        ->numeric(),

                    TextEntry::make($material->price ?? '')
                        ->label('Ціна')
                        ->default($material->price)
                        ->prefix('₴'),

                    TextEntry::make($material->description ?? '')
                        ->label('Опис')
                        ->default($material->description),

                    TextEntry::make($material->date_writing_off ?? '')
                        ->label('Дата списання')
                        ->default($material->date_writing_off),

                    ])
                    ->columns([
                    'sm' => 2,
                    'lg' => 3,
                    ]);
            }
        return $materials;
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



