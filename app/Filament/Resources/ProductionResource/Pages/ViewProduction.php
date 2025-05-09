<?php

namespace App\Filament\Resources\ProductionResource\Pages;

use App\Filament\Resources\ProductionResource;
use App\Helpers\InvoiceSectionBuilder;
use App\Helpers\ProductionViewBuilder;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceProductionItem;
use App\Models\Material;
use App\Models\Production;
use App\Models\ProductionSize;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
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
use Filament\Forms\Components\Textarea;
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
           // Actions\EditAction::make(),
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->getRecord(); // поточний Production
        // $customer = Customer::find($record->customer_id); // отримуємо клієнта
        // $user = User::find($record->user_id); // отримуємо відповідального
        // $invoice = Invoice::find($record->invoice_id); // отримуємо рахунок
        // $transactions = Transaction::where('invoice_id', $record->invoice_id)->get(); // отримуємо транзакції
        // $totalPaid = $transactions->sum('amount'); // сума всіх транзакцій
        // $totalPaid = $totalPaid ? $totalPaid : 0; // якщо сума транзакцій не визначена, то 0
        // $total = $record->price; // загальна сума замовлення
        // $productionStages = $record->productionStages; // етапи виробництва

        return $infolist
                ->columns(12)
                ->schema([
                    ProductionViewBuilder::configureView($record),
                    $this->getInvoices($record),
                    //dd($record->invoice_off),
                   // InvoiceSectionBuilder::buildSection($record->invoice, ' ПРОДАЖ'),
                    //InvoiceSectionBuilder::buildSection($record->invoice_off,' СПИСАННЯ'),
                   // $record->invoice_off ? InvoiceSectionBuilder::buildSection($record->invoice_off,' СПИСАННЯ'): ProductionViewBuilder::configureView($record),
                ]);
                  ///* // $stages,
                //     Section::make('Замовлення на виробництво')
                //         ->collapsed(false)
                //         ->columns(12)
                //         ->columnSpan(12)
                //         ->headerActions([


                //             Action::make('recalculatePrice')
                //                 ->label('Перерахувати вартість')
                //                 ->icon('heroicon-o-calculator')
                //                 ->color('warning')
                //                 ->hidden(fn () => $record->status !== 'створено')
                //                 ->requiresConfirmation()
                //                 ->action(function (Production $record): void {
                //                     try {
                //                         $record->price = $record->getTotalCostWithStagesAndMarkup(); // перерахунок вартості
                //                         if ($record->save()) {
                //                             Notification::make()
                //                                 ->title('Вартість успішно перераховано!')
                //                                 ->success()
                //                                 ->send();
                //                         } else {
                //                             Notification::make()
                //                                 ->title('Не вдалося перерахувати вартість!')
                //                                 ->danger()
                //                                 ->send();
                //                         }
                //                     } catch (\Exception $e) {
                //                         Notification::make()
                //                             ->title('Помилка перерахунку: ' . $e->getMessage())
                //                             ->danger()
                //                             ->send();
                //                     }
                //                 }),

                //             Action::make('startProduction')
                //                 ->label('Розпочати виробництво')
                //                 ->icon('heroicon-o-play')
                //                 ->color('success')
                //                 ->hidden(fn () => $record->status !== 'створено')
                //                 ->requiresConfirmation()
                //                 ->action(function (Production $record): void {
                //                     $record->status = 'в роботі';
                //                     try {
                //                         if ($record->save()) {
                //                             Notification::make()
                //                                 ->title('Виробництво успішно запущено!')
                //                                 ->success()
                //                                 ->send();
                //                         } else {
                //                             Notification::make()
                //                                 ->title('Не вдалося запустити виробництво!')
                //                                 ->danger()
                //                                 ->send();
                //                         }
                //                     } catch (\Exception $e) {
                //                         Notification::make()
                //                             ->title('Помилка збереження: ' . $e->getMessage())
                //                             ->danger()
                //                             ->send();
                //                     }
                //                 }),

                //             Action::make('editProduction')
                //                 ->label('Редагувати')
                //                 ->icon('heroicon-o-pencil')
                //                 ->color('info')
                //                 ->requiresConfirmation()
                //                 ->form([
                //                     TextInput::make('name')
                //                         ->label('Назва')
                //                         ->required()
                //                         ->default($record->name),
                //                     Textarea::make('description')
                //                         ->label('Опис')
                //                         ->maxLength(255)
                //                         ->default($record->description),
                //                     TextInput::make('quantity')
                //                         ->label('Кількість')
                //                         ->required()
                //                         ->numeric()
                //                         ->minValue(1)
                //                         ->default($record->quantity),
                //                 ])
                //                 ->action(function (array $data, Production $record): void {
                //                     $record->name = $data['name'];
                //                     $record->description = $data['description'];
                //                     $record->quantity = $data['quantity'];

                //                     try {
                //                         if ($record->save()) {
                //                             Notification::make()
                //                                 ->title('Виробництво успішно змінено!')
                //                                 ->success()
                //                                 ->send();
                //                         } else {
                //                             Notification::make()
                //                                 ->title('Не вдалося змінити виробництво!')
                //                                 ->danger()
                //                                 ->send();
                //                         }
                //                     } catch (\Exception $e) {
                //                         Notification::make()
                //                             ->title('Помилка збереження: ' . $e->getMessage())
                //                             ->danger()
                //                             ->send();
                //                     }
                //                 })
                //         ])
                //         ->schema([
                //             Fieldset::make('production')
                //                 ->label('Виробництво')
                //                 ->columns(5)
                //                 ->schema([
                //                     TextEntry::make('name')
                //                         ->label('Назва')
                //                         ->weight('bold'),

                //                     TextEntry::make('description')
                //                         ->label('Опис'),

                //                     TextEntry::make('status')
                //                         ->label('Статус')
                //                         ->badge()
                //                         ->color(fn ($state) => match ($state) {
                //                             'створено' => 'info',
                //                             'в роботі' => 'warning',
                //                             'виготовлено' => 'success',
                //                             'скасовано' => 'danger',
                //                             default => 'gray',
                //                         }),

                //                     TextEntry::make('type')
                //                         ->label('Тип')
                //                         ->badge()
                //                         ->color(fn ($state) => $state === 'замовлення' ? 'success' : 'info'),

                //                     TextEntry::make('pay')
                //                         ->label('Оплата')
                //                         ->badge()
                //                         ->color(fn ($state) => match ($state) {
                //                             'не оплачено' => 'danger',
                //                             'завдататок' => 'warring',
                //                             'оплачено' => 'success',
                //                             'часткова оплата' => 'warring',
                //                             default => 'info',
                //                         }),

                //                     TextEntry::make('customer.name')
                //                         ->label('Клієнт'),

                //                     TextEntry::make('user.name')
                //                         ->label('Відповідальний'),

                //                     TextEntry::make('quantity')
                //                         ->label('Кількість'),

                //                     TextEntry::make('price')
                //                         ->label('Вартість виробу')
                //                         ->prefix('₴'),

                //                     TextEntry::make('production_date')
                //                         ->label('Дата виготовлення')
                //                         ->date(),

                //                     TextEntry::make('image')
                //                         ->label('Зображення')
                //                         ->hidden(fn ($state) => empty($state)),
                //                 ]),
                //             Fieldset::make('customer')
                //                 ->label('Клієнт')
                //                 ->columns(12)
                //                 ->schema(
                //                     array_merge(
                //                         $this->getCustomer($record),
                //                         $this->getSizeProduction($record)
                //                     ),
                //                 ),

                //         ])
                //         ->columns([
                //             'sm' => 2,
                //             'lg' => 4,
                //         ]),





                //     Section::make('Процес виробництва')
                //         ->description('Деталі виробництва')
                //         ->collapsed(false)
                //         ->columns(12)
                //         ->columnSpan(6)
                //         ->headerActions([
                //             Action::make('addStage')
                //                 ->label('Додати етап')
                //                 ->icon('heroicon-o-plus')
                //                 ->color('success')
                //                 ->form([
                //                     TextInput::make('name')
                //                         ->label('Назва етапу')
                //                         ->required(),
                //                     TextInput::make('description')
                //                         ->label('Опис етапу')
                //                         ->required(),
                //                     Select::make('status')
                //                         ->label('Статус етапу')
                //                         ->options([
                //                             'очікує' => 'очікує',
                //                             'в роботі' => 'в роботі',
                //                             'виготовлено' => 'виготовлено',
                //                             'скасовано' => 'скасовано',
                //                         ])
                //                         ->default('очікує')
                //                         ->required(),
                //                     TextInput::make('paid_worker')
                //                         ->label('Оплата працівнику')
                //                         ->required()
                //                         ->numeric()
                //                         ->hidden(auth()->user()->role !== 'admin')
                //                         ->helperText('Оплата працівнику за виконану роботу'),
                //                     Select::make('user_id')
                //                         ->label('Виконавець')
                //                         ->options(User::pluck('name', 'id'))
                //                         ->required(),
                //                 ])
                //                 ->action(function (array $data, Production $record): void {
                //                     $stage = new \App\Models\ProductionStage();
                //                     $stage->production_id = $record->id;
                //                     $stage->name = $data['name'];
                //                     $stage->description = $data['description'];
                //                     $stage->status = $data['status'];
                //                     $stage->paid_worker = $data['paid_worker'];
                //                     $stage->user_id = $data['user_id'];

                //                     try {
                //                         if ($stage->save()) {
                //                             Notification::make()
                //                                 ->title('Новий етап виробництва успішно додано!')
                //                                 ->success()
                //                                 ->send();
                //                         } else {
                //                             Notification::make()
                //                                 ->title('Не вдалося додати новий етап виробництва!')
                //                                 ->danger()
                //                                 ->send();
                //                         }
                //                     } catch (\Exception $e) {
                //                         Notification::make()
                //                             ->title('Помилка збереження: ' . $e->getMessage())
                //                             ->danger()
                //                             ->send();
                //                     }
                //                 })
                //         ])
                //         ->schema($this->getStages($record))
                //         ->columns([
                //             'sm' => 2,
                //             'lg' => 4,
                //         ]),
                //     Section::make('Матеріали виробництва')
                //         ->description('Матеріали, які використовуються у виробництві')
                //         ->collapsed(false)
                //         ->columns(12)
                //         ->columnSpan(6)
                //         ->headerActions([
                //             Action::make('addMaterial')
                //                 ->label('Додати матеріал')
                //                 ->icon('heroicon-o-plus')
                //                 ->color('success')
                //                 ->form([
                //                     Select::make('warehouse_id')
                //                         ->label('Склад')
                //                         ->options(\App\Models\Warehouse::pluck('name', 'id'))
                //                         ->searchable()
                //                         ->preload()
                //                         ->required(),

                //                     Select::make('material_id')
                //                         ->label('Матеріал')
                //                         ->options(\App\Models\Material::pluck('name', 'id'))
                //                         ->searchable()
                //                         ->preload()
                //                         ->required(),
                //                     TextInput::make('quantity')
                //                         ->label('Кількість')
                //                         ->required()
                //                         ->numeric()
                //                         ->minValue(1),
                //                     TextInput::make('description')
                //                         ->label('Опис')
                //                         ->nullable(),
                //                 ])
                //                 ->action(function (array $data, Production $record): void {
                //                     $record->addProductionMaterial(Material::find($data['material_id']),Warehouse::find( $data['warehouse_id']), $data['quantity'], $data['description']);
                //                 })
                //         ])
                //         ->schema(
                //             ProductionViewBuilder::buildProductionMaterialView($record)
                //             //$this->getMaterials($record)
                //             )
                //         ->columns([
                //             'sm' => 2,
                //             'lg' => 4,
                //         ]),
                //     Section::make('Накладні')
                //         ->description('Накладні, які пов\'язані з цим виробництвом')
                //         ->collapsed(false)
                //         ->columns(12)
                //         ->columnSpan(12)
                //         ->headerActions([
                //         ])
                //         ->schema(
                //             array_merge($this->getInvoices($record),$this->getInvoicesMaterials($record))
                //             )
                //         ->columns([
                //            'sm' => 2,
                //            'lg' => 4,
                //         ])



                // ]);//*/

       // return //$infolist

    }





    private function getSizeProduction(Production $record): array
    {

        //dd($record->customer->size);
        $data[] = Section::make('Розміри виробу')
                ->description('Розміри виробу')
                ->columnSpan(8)
                ->headerActions([
                    Action::make('applyCustomerSize')
                        ->label('Застосувати розміри клієнта')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        //->visible(fn () => $record->customer->size->last() !== null)
                        ->requiresConfirmation()
                        ->action(function (Production $record): void {
                           // dd($record->customer->size->last());
                            $customerSize = $record->customer->size->last();
                           // $size = $record->productionSizes;
                            if ($customerSize) {
                                $size = $record->productionSizes ?? $record->productionSizes()->create();
                                $size->throat = $customerSize->throat;
                                $size->redistribution = $customerSize->redistribution;
                                $size->behind = $customerSize->behind;
                                $size->hips = $customerSize->hips;
                                $size->length = $customerSize->length;
                                $size->sleeve = $customerSize->sleeve;
                                $size->shoulder = $customerSize->shoulder;
                                $size->comment = $customerSize->comment;

                                try {
                                    if ($size->save()) {
                                        Notification::make()
                                            ->title('Розміри клієнта успішно застосовано до виробу!')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Не вдалося застосувати розміри клієнта до виробу!')
                                            ->danger()
                                            ->send();
                                    }
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Помилка збереження: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }
                        }),

                    Action::make('editCustomerSize')
                        ->label('Редагувати розміри')
                        ->icon('heroicon-o-pencil')
                        ->color('info')
                        ->form([
                            TextInput::make('throat')
                                ->label('Обхват горла')
                                ->numeric()
                                ->default($record->productionSizes->throat ?? null),
                            TextInput::make('redistribution')
                                ->label('Обхват грудей')
                                ->numeric()
                                ->default($record->productionSizes->redistribution ?? null),
                            TextInput::make('behind')
                                ->label('Обхват талії')
                                ->numeric()
                                ->default($record->productionSizes->behind ?? null),
                            TextInput::make('hips')
                                ->label('Обхват стегон')
                                ->numeric()
                                ->default($record->productionSizes->hips ?? null),
                            TextInput::make('length')
                                ->label('Довжина виробу')
                                ->numeric()
                                ->default($record->productionSizes->length ?? null),
                            TextInput::make('sleeve')
                                ->label('Довжина рукава')
                                ->numeric()
                                ->default($record->productionSizes->sleeve ?? null),
                            TextInput::make('shoulder')
                                ->label('Ширина плечей')
                                ->numeric()
                                ->default($record->productionSizes->shoulder ?? null),
                            Textarea::make('comment')
                                ->label('Коментар')
                                ->default($record->productionSizes->comment ?? null),
                        ])
                        ->action(function (array $data, Production $record): void {
                            $productionSize = $record->productionSizes;

                            if (!$productionSize) {
                                $productionSize = new ProductionSize();
                                $productionSize->customer_id = $record->customer_id;
                            }

                            $productionSize->throat = $data['throat'];
                            $productionSize->redistribution = $data['redistribution'];
                            $productionSize->behind = $data['behind'];
                            $productionSize->hips = $data['hips'];
                            $productionSize->length = $data['length'];
                            $productionSize->sleeve = $data['sleeve'];
                            $productionSize->shoulder = $data['shoulder'];
                            $productionSize->comment = $data['comment'];

                            try {
                                if ($productionSize->save()) {
                                    $record->save();

                                    Notification::make()
                                        ->title('Розміри клієнта успішно оновлено!')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Не вдалося оновити розміри клієнта!')
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
                        ->default($record->productionSizes->throat ?? '-'),

                    TextEntry::make('redistribution')
                        ->label('Обхват грудей')
                        ->default($record->productionSizes->redistribution ?? '-'),

                    TextEntry::make('behind')
                        ->label('Обхват талії')
                        ->default($record->productionSizes->behind ?? '-'),

                    TextEntry::make('hips')
                        ->label('Обхват стегон')
                        ->default($record->productionSizes->hips ?? '-'),

                    TextEntry::make('length')
                        ->label('Довжина виробу')
                        ->default($record->productionSizes->length ?? '-'),

                    TextEntry::make('sleeve')
                        ->label('Довжина рукава')
                        ->default($record->productionSizes->sleeve ?? '-'),

                    TextEntry::make('shoulder')
                        ->label('Ширина плечей')
                        ->default($record->productionSizes->shoulder ?? '-'),

                    TextEntry::make('comment')
                        ->label('Коментар')
                        ->default($record->productionSizes->comment ?? '-'),
                ])
                ->columns([
                   'sm' => 2,
                   'lg' => 4,
                ]);
            return $data;
    }



    /**
     * Отримуємо Дані про клієнта
     *
     * @param Production $record
     * @return array
     */
    private function getCustomer(Production $record): array
    {
        $customer = $record->customer; // отримуємо клієнта
        $data = [];
        //dd($customer);
        if(is_null($customer)) {
            $data[] = Section::make('Клієнт - ')
                ->description('Немає клієнта')
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
                ->columnSpan(4);
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
        }
        return $data;
    }



    /**
     * Отримуємо список виробництв у накладній
     *
     * @param Production $record
     * @return array
     */
    private function getProductionInvoice($invoiceProductionItems): array
    {
        $invoice = [];
        if(is_null($invoiceProductionItems)) {
            $invoice[] = Fieldset::make('invoiceProductionItems')
                            //->default(fn () => $invoiceProductionItems ?? [])
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
                            ]);
        }else {
           foreach ($invoiceProductionItems as $productionItems){
            $invoice[] = Fieldset::make('invoiceProductionItems'.$productionItems->id)
                            //->default(fn () => $production ?? [])
                            ->label('Виробництво - '.$productionItems->production->name)
                            ->columnSpan(12)
                            ->columns(4)
                            ->schema([
                                TextEntry::make('quantity'.$productionItems->id)
                                    ->default($productionItems->quantity)->label('Кількість'),
                                TextEntry::make('price'.$productionItems->id)
                                    ->default($productionItems->price)->label('Ціна'),
                                TextEntry::make('total'.$productionItems->id)
                                    ->default($productionItems->price)->label('Сума'),
                                TextEntry::make('productionname'.$productionItems->id)
                                    ->default($productionItems->production->name)->label('Виробництво'),
                                TextEntry::make('productiondescription'.$productionItems->id)
                                    ->default($productionItems->production->description)->label('Опис'),
                                TextEntry::make('productionstatus'.$productionItems->id)
                                    ->default($productionItems->production->status)->label('Статус'),
                                TextEntry::make('productiontype'.$productionItems->id)
                                    ->default($productionItems->production->type)->label('Тип'),
                            ]);
           }
        }
        return $invoice;
    }


    private function getItemsInvoice($invoiceItemsItems): array
    {
        $invoice = [];
        if(is_null($invoiceItemsItems)) {
            $invoice[] = Fieldset::make('invoiceProductionItems')
                            //->default(fn () => $invoiceProductionItems ?? [])
                            ->label('Товари у накладній відсутні')
                            ->columnSpan(12)
                            ->columns(4)
                            ->schema([
                                // TextEntry::make('quantity')->label('Кількість'),
                                // TextEntry::make('price')->label('Ціна'),
                                // TextEntry::make('total')->label('Сума'),
                                // TextEntry::make('production.name')->label('Виробництво'),
                                // TextEntry::make('production.description')->label('Опис'),
                                // TextEntry::make('production.status')->label('Статус'),
                                // TextEntry::make('production.type')->label('Тип'),
                            ]);
        }else {
           foreach ($invoiceItemsItems as $items){
            $invoice[] = Fieldset::make('invoiceItemsItems'.$items->id)
                            ->label('Товар - '.$items->material->name)
                            ->columnSpan(12)
                            ->columns(4)
                            ->schema([
                                TextEntry::make('quantity'.$items->id)
                                    ->default($items->quantity)->label('Кількість'),
                                TextEntry::make('price'.$items->id)
                                    ->default($items->price)->label('Ціна'),
                                TextEntry::make('total'.$items->id)
                                    ->default($items->total)->label('Сума'),
                                TextEntry::make('materialname'.$items->id)
                                    ->default($items->material->name)->label('Матеріал'),
                            ]);
           }
        }
        return $invoice;
    }


    /**
     * Отримуємо накладні
     *
     * @param Production $record
     * @return array
     */
    // отримуємо накладні
    private function getInvoices(Production $record)
    {
        $data = [];
        foreach ($record->invoice_off as $invoices){
            $invoiceItemsOff = [];
            foreach($invoices->invoiceItems as $items){
               $invoiceItemsOff [] = Section::make('Матеріал - '.$items->material->name)
                    ->label($items->material->name)
                    ->columnSpan(12)
                    ->columns(4)
                    ->schema([
                        TextEntry::make($items->quantity)->default($items->quantity)->label('Кількість'),
                        TextEntry::make('price')->default($items->price)->label('Ціна'),
                        TextEntry::make('total')->default($items->total)->label('Сума'),
                        BAction::make([
                            Action::make('Змінити кількість')
                                ->label('Змінити кількість')
                                ->visible(fn () => $invoices->status === 'створено')
                                ->icon('heroicon-o-pencil')
                                ->color('info')
                                ->action(fn () => $items),
                        ])
                    ]);
            }
            $data[] = Section::make('Накладна '.$invoices->invoice_number)
                ->label('Накладна - '.$invoices->invoice_number)
                ->description('Автоматично створена накладна на списання матеріалів зі складу '. $invoices->warehouse->name)
                ->columnSpan(12)
                ->columns(4)
                ->schema([
                    TextEntry::make('status'.$invoices->id)
                        ->default($invoices->status)->label('Статус накладної'),
                    TextEntry::make('total'.$invoices->id)
                        ->default($invoices->total)->label('Сума'),
                    TextEntry::make('notes'.$invoices->id)
                        ->default($invoices->notes)->label('Примітки'),
                    BAction::make([
                        Action::make('move_invoice' . $invoices->id)
                            ->label('Провести накладну')
                            ->icon('heroicon-o-check')
                            ->visible(fn () => $invoices->status === 'створено')
                            ->color('success')
                            ->action(fn () => $invoices),
                        Action::make('cancel_invoice' . $invoices->id)
                            ->label('Скасувати проведення')
                            //->visible(fn () => $invoices->status === 'проведено')
                            ->icon('heroicon-o-x-mark')
                            ->color('danger')
                            ->action(fn () => $invoices),
                    ]),
                    Fieldset::make('invoiceItems')
                        ->label('Позиції у накладній')
                        ->columnSpan(12)
                        ->columns(4)
                        ->schema(
                            $invoiceItemsOff
                        )->columns([
                            'sm' => 2,
                            'lg' => 4,
                        ]),
                ])
                ->columns([
                    'sm' => 2,
                    'lg' => 4,
                ]);
        }
        $invoice = Fieldset::make('Накладні на списання матеріалів')
                ->schema($data);

    //     $invoices = $record->invoice; // отримуємо накладні
    //     if(is_null($invoices)) {
    //         $productionItems = []; // отримуємо позиції виробництва
    //         $invoiceItems = []; // отримуємо позиції товарів
    //     }else{
    //         $productionItems = $invoices->invoiceProductionItems; // отримуємо позиції виробництва
    //         $invoiceItems = $invoices->invoiceItems; // отримуємо позиції товарів
    //     }

    //    // dd($invoices,$productionItems);
    //     $invoice = [];
    //     //dd($invoices);
    //     if(is_null($invoices)) {
    //         $invoice[] = Section::make('Немає накладної на продаж')
    //             ->description('Немає накладних')
    //             ->headerActions([
    //                 Action::make('addInvoice')
    //                     ->label('Додати накладну')
    //                     ->icon('heroicon-o-plus')
    //                     ->requiresConfirmation()
    //                     ->color('success')
    //                     ->form([
    //                         Select::make('invoice_id')
    //                             ->label('Виберіть накладну')
    //                             ->options(Invoice::pluck('invoice_number', 'id'))
    //                             ->searchable()
    //                             ->placeholder('Створити нову накладну')
    //                     ])
    //                     ->action(function (array $data, Production $record): void {
    //                         $record->makeInvoice($data['invoice_id'], $data);
    //                     })
    //             ])
    //             ->columnSpanFull();
    //     }else {
    //             $invoice[] = Section::make('Накладна на продаж виробу')
    //                 ->visible(fn () => $invoices !== null)
    //                 ->collapsed(false)
    //                 ->columnSpanFull()
    //                 ->columns(12)
    //                 ->schema([
    //                 Section::make('Інформація про накладну')
    //                    // ->description('Загально')
    //                     ->columns(2)
    //                     ->columnSpan(6, 12)
    //                     ->footerActions([
    //                     Action::make('move_invoice' . $invoices->id)
    //                         ->label('Провести накладну')
    //                         ->icon('heroicon-o-check')
    //                         ->visible(fn () => $invoices->status === 'створено')
    //                         ->color('success')
    //                         ->action(fn () => $invoices->moveInvoice()),
    //                     Action::make('cancel_invoice' . $invoices->id)
    //                         ->label('Скасувати проведення')
    //                         ->visible(fn () => $invoices->status === 'проведено')
    //                         ->icon('heroicon-o-x-mark')
    //                         ->color('danger')
    //                         ->action(fn () => $invoices->cancelInvoice()),
    //                     Action::make('printInvoice_' . $invoices->id)
    //                         ->label('Надрукувати накладну')
    //                         ->visible(fn () => $invoices->status === 'проведено')
    //                         ->icon('heroicon-o-printer')
    //                         ->color('info')
    //                         ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
    //                     ])
    //                     ->footerActionsAlignment(Alignment::Center)
    //                     ->schema([
    //                     TextEntry::make('invoice_number')
    //                         ->label('Номер накладної')
    //                         ->default($invoices->invoice_number)
    //                         ->badge()
    //                         ->color('info'),
    //                     TextEntry::make('invoice_date')
    //                         ->label('Дата накладної')
    //                         ->default($invoices->invoice_date)
    //                         ->badge()
    //                         ->color('info'),
    //                     TextEntry::make('type')->label('Тип')->default($invoices->type)->badge()->color('primary'),
    //                     TextEntry::make('status')->label('Статус')->default($invoices->status)->badge()->color(fn () => match ($invoices->status) {
    //                         'створено' => 'info',
    //                         'проведено' => 'success',
    //                         'скасовано' => 'danger',
    //                     }),
    //                     TextEntry::make('invoice.notes')
    //                         ->label('Примітки'),
    //                     ]),
    //                 Section::make('Фінанси')
    //                     //->description('Базова інформація про накладну')
    //                     ->columns(2)
    //                     ->columnSpan(6, 12)
    //                     ->footerActions([
    //                     Action::make('add_discount' . $invoices->id)
    //                         ->label('Додати знижку')
    //                         ->visible(fn () => $invoices->status === 'створено')
    //                         ->icon('heroicon-o-printer')
    //                         ->color('warning')
    //                         ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
    //                     Action::make('pay' . $invoices->id)
    //                         ->label('Оплатити')
    //                         ->visible(fn () => $invoices->status === 'проведено')
    //                         ->icon('heroicon-o-printer')
    //                         ->color('success')
    //                         ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
    //                     ])
    //                     ->footerActionsAlignment(Alignment::Center)
    //                     ->schema([
    //                         TextEntry::make('total')->label('Сума')->default($invoices->total)->badge()->color('success'),
    //                         TextEntry::make('paid')->label('Оплачено')->default($invoices->paid)->badge()->color('warning'),
    //                         TextEntry::make('due')->label('Заборгованість')->default($invoices->due)->badge()->color('danger'),
    //                         TextEntry::make('discount')->label('Знижка')->default($invoices->discount)->badge()->color('success'),
    //                         TextEntry::make('payment_status')
    //                             ->badge()
    //                             ->label('Статус оплати')
    //                             ->default($invoices->payment_status)
    //                             ->color(fn () => match ($invoices->payment_status) {
    //                             'завдаток' => 'info',
    //                             'частково оплачено' => 'warning',
    //                             'оплачено' => 'success',
    //                             'не оплачено' => 'danger',
    //                             }),
    //                     ]),

    //                     Section::make('Усі замовлення накладної')
    //                         //->description('Усі транзакції накладної')
    //                         ->collapsed(true)
    //                         ->columns(2)
    //                         ->columnSpanFull()
    //                         ->schema(
    //                             $this->getProductionInvoice($productionItems)
    //                         ),

    //                     Section::make('Усі товари у накладній')
    //                         //->description('Усі транзакції накладної')
    //                         ->collapsed(true)
    //                         ->columns(2)
    //                         ->columnSpanFull()
    //                         ->schema(
    //                             $this->getItemsInvoice($invoiceItems)
    //                         ),

    //                 //,

    //                 // RepeatableEntry::make('invoiceItems')
    //                 //     ->label('Позиції накладної')
    //                 //     ->columns(4)
    //                 //     ->columnSpanFull()
    //                 //     ->schema([
    //                 //     TextEntry::make('quantity')->label('Кількість'),
    //                 //     TextEntry::make('price')->label('Ціна'),
    //                 //     TextEntry::make('total')->label('Сума'),
    //                 //     ]),
    //                 Section::make('Транзакції')
    //                     ->description('Усі транзакції накладної')
    //                     ->collapsed(true)
    //                     ->columns(2)
    //                     ->columnSpanFull()
    //                     ->footerActions([
    //                     Action::make('add_discount' . $invoices->id)
    //                         ->label('Додати знижку')
    //                         ->icon('heroicon-o-printer')
    //                         ->color('warning')
    //                         ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
    //                     Action::make('pay' . $invoices->id)
    //                         ->label('Оплатити')
    //                         ->icon('heroicon-o-printer')
    //                         ->color('success')
    //                         ->url(fn () => route('invoice.pdf', ['invoice' => $invoices->id])),
    //                     ])
    //                     ->schema([
    //                     RepeatableEntry::make('transactions')
    //                         ->label('Транзакції')
    //                         ->columnSpan(12)
    //                         ->columns(4)
    //                         ->schema([
    //                         TextEntry::make('reference_number'),
    //                         TextEntry::make('description'),
    //                         TextEntry::make('debet.amount'),
    //                         TextEntry::make('transaction_date'),
    //                         ]),
    //                     ]),
    //                 ])
    //                 ->headerActions([
    //                 Action::make('reconcileFinances_' . $invoices->id)
    //                     ->label('Перерахувати фінанси')
    //                     ->icon('heroicon-s-variable')
    //                     ->color('danger')
    //                     ->action(fn () => $invoices->reconcileFinances()),
    //                 Action::make('customer_pay_' . $invoices->id)
    //                     ->label('Внести оплату')
    //                     ->icon('heroicon-s-currency-euro')
    //                     ->color('success')
    //                     ->requiresConfirmation()
    //                     ->form([
    //                     Select::make('account_id')
    //                         ->label('Гроші на рахунок')
    //                         ->options(Account::query()->whereNull('owner_id')->pluck('name', 'id'))
    //                         ->required(),
    //                     TextInput::make('amount')
    //                         ->numeric()
    //                         ->maxValue($invoices->due ?? 100)
    //                         ->default($invoices->due ?? 100)
    //                         ->label('Сума'),
    //                     TextInput::make('description')
    //                         ->label('Опис'),
    //                     ])
    //                     ->action(function (array $data) use ($invoices) {
    //                     Transaction::customer_pay_transaction($data, $invoices->customer, $invoices);
    //                     }),
    //                 ]);
    //             }
        return $invoice;
    }
    /**
     * Отримуємо матеріали накладних
     *
     * @param Production $record
     * @return array
     */
    private function getInvoicesMaterials(Production $record): array
    {
        $invoices_off = $record->invoice_off; // отримуємо накладні

        //dd($invoices_off,$record->invoice);
        if(is_null($invoices_off)) {
            $productionItems = []; // отримуємо позиції виробництва
            $invoiceItems = []; // отримуємо позиції товарів
        }else{
            $productionItems = $invoices_off->invoiceProductionItems; // отримуємо позиції виробництва
            $invoiceItems = $invoices_off->invoiceItems; // отримуємо позиції товарів
        }

        $materials = [];
        if (is_null($invoices_off)) {
            $materials[] = Section::make('Накладна на списання матеріалів')
            ->description('Немає матеріалів у накладній')
            ->headerActions([
                Action::make('addInvoice')
                    ->label('Згенерувати накладну')
                    ->icon('heroicon-o-plus')
                    ->requiresConfirmation()
                    ->color('success')
                    ->form([
                        Select::make('invoice_id')
                            ->label('Виберіть накладну')
                            ->options(Invoice::pluck('invoice_number', 'id'))
                            ->searchable()
                            ->placeholder('Створити нову накладну')
                    ])
                    ->action(function (array $data, Production $record): void {
                        $record->makeInvoiceOff();
                    })
            ])
            ->columnSpanFull();
        } else {
            $invoiceMaterials = $invoices_off->invoiceItems; // отримуємо матеріали накладної

            $materials[] = Section::make('Накладна на списання матеріалів')
            ->visible(fn () => $invoices_off !== null)
            ->collapsed(false)
            ->columnSpanFull()
            ->columns(12)
            ->schema([
                Section::make('Інформація про накладну')
                ->columns(2)
                ->columnSpan(6, 12)
                ->footerActions([
                    Action::make('move_invoice' . $invoices_off->id)
                        ->label('Провести накладну')
                        ->icon('heroicon-o-check')
                        ->visible(fn () => $invoices_off->status === 'створено')
                        ->color('success')
                        ->action(fn () => $invoices_off->moveInvoice()),
                    Action::make('cancel_invoice' . $invoices_off->id)
                        ->label('Скасувати проведення')
                        ->visible(fn () => $invoices_off->status === 'проведено')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn () => $invoices_off->cancelInvoice()),
                    Action::make('printInvoice_' . $invoices_off->id)
                        ->label('Надрукувати накладну')
                        ->visible(fn () => $invoices_off->status === 'проведено')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn () => route('invoice.pdf', ['invoice' => $invoices_off->id])),
                ])
                ->footerActionsAlignment(Alignment::Center)
                ->schema([
                    TextEntry::make('invoice_number'.$invoices_off->id)
                    ->label('Номер накладної')
                    ->default($invoices_off->invoice_number)
                    ->badge()
                    ->color('info'),
                    TextEntry::make('invoice_date'.$invoices_off->id)
                    ->label('Дата накладної')
                    ->default($invoices_off->invoice_date)
                    ->badge()
                    ->color('info'),
                    TextEntry::make('type'.$invoices_off->id)
                    ->label('Тип')
                    ->default($invoices_off->type)
                    ->badge()
                    ->color('primary'),
                    TextEntry::make('status'.$invoices_off->id)
                    ->label('Статус')
                    ->default($invoices_off->status)
                    ->badge()
                    ->color(fn () => match ($invoices_off->status) {
                        'створено' => 'info',
                        'проведено' => 'success',
                        'скасовано' => 'danger',
                    }),
                    TextEntry::make('invoice.notes'.$invoices_off->id)
                    ->label('Примітки'),
                ]),
                Section::make('Фінанси')
                ->columns(2)
                ->columnSpan(6, 12)
                ->footerActions([

                ])
                ->footerActionsAlignment(Alignment::Center)
                ->schema([
                    TextEntry::make('total')
                    ->label('Сума')
                    ->default($invoices_off->total)
                    ->badge()
                    ->color('success'),
                    TextEntry::make('paid')
                    ->label('Оплачено')
                    ->default($invoices_off->paid)
                    ->badge()
                    ->color('warning'),
                    TextEntry::make('due')
                    ->label('Заборгованість')
                    ->default($invoices_off->due)
                    ->badge()
                    ->color('danger'),
                    TextEntry::make('discount')
                    ->label('Знижка')
                    ->default($invoices_off->discount)
                    ->badge()
                    ->color('success'),
                    TextEntry::make('payment_status')
                    ->badge()
                    ->label('Статус оплати')
                    ->default($invoices_off->payment_status)
                    ->color(fn () => match ($invoices_off->payment_status) {
                        'завдаток' => 'info',
                        'частково оплачено' => 'warning',
                        'оплачено' => 'success',
                        'не оплачено' => 'danger',
                    }),
                ]),


                Section::make('Усі замовлення накладної')
                    //->description('Усі транзакції накладної')
                    ->collapsed(true)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema(
                        $this->getProductionInvoice($productionItems)
                ),

                Section::make('Усі товари у накладній')
                    //->description('Усі транзакції накладної')
                    ->collapsed(true)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema(
                        $this->getItemsInvoice($invoiceItems)
                    ),
            ]);
        }

        return $materials;
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
                    ->footerActions([
                        Action::make('deleteMaterial')
                            ->label('Видалити матеріал')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(function (Production $record) use ($material): void {
                                try {
                                    if ($material->delete()) {
                                        Notification::make()
                                            ->title('Матеріал успішно видалено!')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Не вдалося видалити матеріал!')
                                            ->danger()
                                            ->send();
                                    }
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Помилка видалення: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                    ])
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
                        Select::make('material_id')
                            ->label('Матеріал')
                            ->options(Material::pluck('name', 'id'))
                            ->default($material->material_id)
                            ->searchable()
                            ->preload()
                            ->required(),

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
                            $material->material_id = $data['material_id'];
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



