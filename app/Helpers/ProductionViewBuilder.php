<?php

namespace App\Helpers;



use App\Filament\Resources\ProductionResource;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceProductionItem;
use App\Models\Material;
use App\Models\Production;
use App\Models\ProductionSize;
use App\Models\ProductionStage;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ProductionService;
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
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Set;

use Filament\Infolists\Components\Actions as BAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Tabs;
use Pest\ArchPresets\Custom;
use Filament\Support\Enums\Alignment;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use Filament\Infolists\Components\Split;

class ProductionViewBuilder
{
    public static function buildProductionMaterialView(Production $record)
    {
        $productionMaterials = $record->productionMaterials; // отримуємо етапи виробництва
        $materials = [];
        $materials[] = BAction::make([
            Action::make('addMaterial')
            ->label('Додати матеріал')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->form([
                Select::make('warehouse_id')
                    ->label('Склад')
                    ->options(\App\Models\Warehouse::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('material_id')
                    ->label('Матеріал')
                    ->options(\App\Models\Material::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('quantity')
                    ->label('Кількість')
                    ->required()
                    ->numeric()
                    ->minValue(0.01),
                TextInput::make('description')
                    ->label('Опис')
                    ->nullable(),
            ])
            ->action(function (array $data, Production $record): void {
                $record->addProductionMaterial(Material::find($data['material_id']),Warehouse::find( $data['warehouse_id']), $data['quantity'], $data['description']);
            })

        ]);
      //  dd($productionMaterials);
        foreach ($productionMaterials as $material) {
            //dd($material->material->name);
            if(is_null($material->material )){
               continue;
              //   dd($material,$material->material_id ,Material::find($material->material_id));
            }
            $materialTitle = 'Матеріал - ' . $material->material->name;
            $materials[] = Fieldset::make('Матеріал - ' . $material->material->name)
                    //->collapsed($material->date_writing_off !== null)
                    //->description($material->date_writing_off ? 'Списано ' . $material->quantity . ' одиниць' : 'Не списано')
                    ->columnSpanFull()

                    ->schema([
                        BAction::make([
                            Action::make('deleteMaterial'.$material->id)
                            ->label('Видалити матеріал')
                            ->hidden(fn (Production $record) => $record->status !== 'створено')
                            ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
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
                            }),
                            Action::make('edit'.$material->id)
                                ->label('Редагувати')
                                ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
                                ->hidden($material->date_writing_off !== null)
                                ->icon('heroicon-o-pencil')
                                ->color('primary')
                                ->form([
                                    Select::make('warehouse_id')
                                        ->label('Склад')
                                        ->options(Warehouse::pluck('name', 'id'))
                                        ->required()
                                        ->default($material->warehouse_id),
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
                                ProductionService::updateMaterialProduction($material, $data);
                               // $material->quantity = $data['quantity'];
                                //$material->price = $data['price'];
                               // $material->description = $data['description'];
                               // $material->material_id = $data['material_id'];
                                //$material->date_writing_off = $data['date_writing_off'];

                            // try {
                            //     if ($material->save()) {
                            //     Notification::make()
                            //         ->title('Оновлено матеріал виробництва!')
                            //         ->success()
                            //         ->send();
                            //     } else {
                            //     Notification::make()
                            //         ->title('Не вдалося оновити матеріал виробництва!')
                            //         ->danger()
                            //         ->send();
                            //     }
                            // } catch (\Exception $e) {
                            //     Notification::make()
                            //     ->title('Помилка збереження: ' . $e->getMessage())
                            //     ->danger()
                            //     ->send();
                            // }
                            }),
                        ]),

                    TextEntry::make($material->material->name ?? '')
                        ->label('Назва матеріалу')
                        ->default($material->material->name)
                        ->weight('bold'),

                    TextEntry::make($material->quantity ?? '')
                        ->label('Кількість')
                        ->default($material->quantity)
                        ->numeric(),

                    TextEntry::make($material->price ?? '')
                        ->label('Ціна за одиницю')
                        ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
                        ->default($material->price)
                        ->prefix('₴'),
                    TextEntry::make($material->price.'total' ?? '')
                        ->label('Загальна вартість')
                        ->visible(fn () => auth()->user()->role === 'admin'  || auth()->user()->role === 'manager')
                        ->default($material->price * $material->quantity)
                        ->prefix('₴'),

                   ImageEntry::make('photo'.$material->material->image)
                        ->defaultImageUrl('/storage/'.$material->material->image)
                        ->label('Зображення'),

                    TextEntry::make($material->description.'starage' ?? '')
                        ->label('Запаси на складі')
                        ->color(fn ($state) => $material->getStockInWarehouse() < 1 ? 'danger' : 'gray')
                        ->default($material->getStockInWarehouse()),

                    TextEntry::make($material->date_writing_off ?? '')
                        ->label('Дата списання')
                        ->default($material->date_writing_off),
                    TextEntry::make('errors')
                        ->badge()
                        ->hidden($material->getStockInWarehouse() >= $material->quantity)
                        ->label('Повідомлення')
                        ->default('Матеріалів не достатньо на складі!')
                        ->color('danger')
                    ])
                    ->columns([
                    'sm' => 2,
                    'lg' => 3,
                    ]);
            }
        return $materials;
    }

    public static function configureView(Production $record)
    {
        return Tabs::make('Tabs')
                ->columnSpanFull()
                ->tabs([
                    Tabs\Tab::make('Клієнт')
                    ->schema([
                        BAction::make([
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

                        ])->alignment(Alignment::Center),
                        self::buildCustomerSection($record->customer),
                    ]),
                    Tabs\Tab::make('Замовлення')
                        ->schema([
                            BAction::make([
                                self::recalculatePrice($record),
                                self::actionStartProduction($record),
                                self::actionStopProduction($record),
                                self::actionPauseProduction($record),
                                self::actionEditProduction($record),
                                self::addInvoiceOn($record),
                                self::addInvoiceSales($record),
                            ])->alignment(Alignment::Center),
                                self::buildOrderSection($record),

                        ]),
                    Tabs\Tab::make('Розміри')
                        ->schema([
                            BAction::make([
                                self::editCustomerSizeSection($record),
                                self::applyCustomerSizeSection($record),
                            ])->alignment(Alignment::Center),
                            self::buildSizeSection($record),
                        ]),
                    Tabs\Tab::make('Виробництво')
                        ->schema(
                            self::buildStagesProduction($record),
                        ),
                    Tabs\Tab::make('Матеріали')
                        ->schema(
                            self::buildProductionMaterialView($record),
                        ),
                ]);
    }

    public static function buildOrderSection(Production $record)
    {
        return Fieldset::make('Замовлення на виробництво')
            //->collapsed(false)
            ->columns(12)
            ->columnSpanFull()
            //->columnSpan(12)
            // ->headerActions([

            // ])
            ->schema([
                // BAction::make([
                //     self::recalculatePrice($record),
                //     self::actionStartProduction($record),
                //     self::actionEditProduction($record),
                // ]),
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
                                'в роботі' => 'warning',
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
                            ->label('Вартість виробу')
                            ->visible(fn () => auth()->user()->role === 'admin'  || auth()->user()->role === 'manager')
                            ->prefix('₴'),

                        TextEntry::make('production_date')
                            ->label('Дата виготовлення')
                            ->date(),

                        TextEntry::make('image')
                            ->label('Зображення')
                            ->hidden(fn ($state) => empty($state)),

                        //self::buildSizeSection($record)
                    ])
                // Fieldset::make('customer')
                //     ->label('Клієнт')
                //     ->columns(12)
                //     ->schema([
                //         self::buildSizeSection($record),
                //     ]),

            ->columns([
                'sm' => 3,
                'lg' => 5,
            ]);
    }

    public static function buildCustomerSection($customer = null)
    {
        if(is_null($customer)) {
            return Fieldset::make('Клієнт - ')
                ->label('Немає клієнта')
                // ->headerActions([

                // ])
                ->schema([
                    BAction::make([
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
                    ]),
                ])
                ->columnSpanFull();
        }else {
           return Fieldset::make('Клієнт - ' . $customer->name)
                //->description($customer->email ?? 'Немає email')
                ->columnSpanFull()
                ->columns(4)
                ->schema([

                        TextEntry::make($customer->name ?? '')
                            ->label('Ім\'я клієнта')
                            ->default($customer->name),

                        TextEntry::make($customer->email ?? '')
                            ->label('Email клієнта')
                            ->default($customer->email),
                        TextEntry::make($customer->phone ?? '')
                            ->label('Телефон')
                            ->default($customer->phone),

                        TextEntry::make($customer->address ?? '')
                            ->label('Адреса')
                            ->default($customer->address),


                ]);
        }
    }

    public static function applyCustomerSizeSection($record)
    {
       return Action::make('applyCustomerSize')
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
            });
    }

    public static function editCustomerSizeSection(Production $record)
    {
        return Action::make('editCustomerSize')
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
            TextInput::make('neck')
                ->label('Обхват шиї')
                ->numeric()
                ->default($record->productionSizes->neck ?? null),
            TextInput::make('front')
                ->label('Перід')
                ->numeric()
                ->default($record->productionSizes->front ?? null),
            TextInput::make('epitrachelion')
                ->label('Епитрахиля')
                ->numeric()
                ->default($record->productionSizes->epitrachelion ?? null),
            TextInput::make('abdomen_volume')
                ->label('Об\'єм живота')
                ->numeric()
                ->default($record->productionSizes->abdomen_volume ?? null),
            TextInput::make('height')
                ->label('Ріст')
                ->numeric()
                ->default($record->productionSizes->height ?? null),
            TextInput::make('floor_height')
                ->label('Ріст до підлоги')
                ->numeric()
                ->default($record->productionSizes->floor_height ?? null),
            TextInput::make('chest_volume')
                ->label('Об\'єм грудей')
                ->numeric()
                ->default($record->productionSizes->chest_volume ?? null),
            Checkbox::make('cuffs')
                ->label('Нарукавники')
                //->boolean()
                ->default($record->productionSizes->cuffs ?? null),
            Textarea::make('awards')
                ->label('Нагороди')
                ->default($record->productionSizes->awards ?? null),
            Checkbox::make('sticharion')
                ->label('Стихар')
                //->boolean()
                ->default($record->productionSizes->sticharion ?? null),
            Textarea::make('comment')
                ->label('Коментар')
                ->default($record->productionSizes->comment ?? null),
        ])
        ->action(function (array $data, Production $record): void {
            $productionSize = $record->productionSizes;

            if (!$productionSize) {
                $productionSize = new ProductionSize();
                $productionSize->production_id = $record->id;
                //$productionSize->customer_id = $record->customer_id;
            }

            $productionSize->throat = $data['throat'] ?? null;
            $productionSize->redistribution = $data['redistribution'] ?? null;
            $productionSize->behind = $data['behind'] ?? null;
            $productionSize->hips = $data['hips'] ?? null;
            $productionSize->length = $data['length'] ?? null;
            $productionSize->sleeve = $data['sleeve'] ?? null;
            $productionSize->shoulder = $data['shoulder'] ?? null;
            $productionSize->comment = $data['comment'] ?? null;
            $productionSize->neck = $data['neck'] ?? null;
            $productionSize->front = $data['front'] ?? null;
            $productionSize->epitrachelion = $data['epitrachelion'] ?? null;
            $productionSize->abdomen_volume = $data['abdomen_volume'] ?? null;
            $productionSize->height = $data['height'] ?? null;
            $productionSize->floor_height = $data['floor_height'] ?? null;
            $productionSize->chest_volume = $data['chest_volume'] ?? null;
            $productionSize->cuffs = $data['cuffs'] ?? null;
            $productionSize->awards = $data['awards'] ?? null;
            $productionSize->sticharion = $data['sticharion'] ?? null;

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
        });
    }

    public static function buildSizeSection(Production $record)
    {
        return Fieldset::make('Розміри виробу')
            //->description('Розміри виробу')
            ->columnSpan(8)
            // ->headerActions([

            // ])
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

                TextEntry::make('neck')
                    ->label('Обхват шиї')
                    ->default($record->productionSizes->neck ?? '-'),

                TextEntry::make('front')
                    ->label('Перід')
                    ->default($record->productionSizes->front ?? '-'),

                TextEntry::make('epitrachelion')
                    ->label('Епитрахиля')
                    ->default($record->productionSizes->epitrachelion ?? '-'),

                TextEntry::make('abdomen_volume')
                    ->label('Об\'єм живота')
                    ->default($record->productionSizes->abdomen_volume ?? '-'),

                TextEntry::make('height')
                    ->label('Ріст')
                    ->default($record->productionSizes->height ?? '-'),

                TextEntry::make('floor_height')
                    ->label('Ріст до підлоги')
                    ->default($record->productionSizes->floor_height ?? '-'),

                TextEntry::make('chest_volume')
                    ->label('Об\'єм грудей')
                    ->default($record->productionSizes->chest_volume ?? '-'),

                TextEntry::make('cuffs')
                    ->label('Нарукавники'),
                    //->default($record->productionSizes->cuffs ? 'Так' : 'Ні'),

                TextEntry::make('awards')
                    ->label('Нагороди')
                    ->default($record->productionSizes->awards ?? '-'),

                TextEntry::make('sticharion')
                    ->label('Стихар'),
                    //->default($record->productionSizes->sticharion ? 'Так' : 'Ні'),

                TextEntry::make('comment')
                    ->label('Коментар')
                    ->default($record->productionSizes->comment ?? '-'),
            ])
                ->columns([
                'sm' => 2,
                'lg' => 4,
            ]);
    }


    public static function buildMaterialsSection(Production $record): Section
    {
        return Section::make('Матеріали виробництва')
            ->description('Матеріали, які використовуються у виробництві')
            ->collapsed(false)
            ->columns(12)
            ->columnSpan(6)
            ->headerActions([
                Action::make('addMaterial')->label('Додати матеріал'),
            ])
            ->schema(self::buildProductionMaterialView($record))
            ->columns([
                'sm' => 2,
                'lg' => 4,
            ]);
    }

    public static function actionStartProduction(Production $record)
    {
        return Action::make('startProduction')
            ->label('Розпочати виробництво')
            ->icon('heroicon-o-play')
            ->visible(fn (Production $record) => $record->status === 'створено')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Production $record): void {
                //$record->status = 'в роботі';
                ProductionService::startProduction($record);
                // try {
                //     if (ProductionService::startProduction($record)) {
                //         Notification::make()
                //             ->title('Виробництво успішно запущено!')
                //             ->success()
                //             ->send();
                //     } else {
                //         Notification::make()
                //             ->title('Не вдалося запустити виробництво!')
                //             ->danger()
                //             ->send();
                //     }
                // } catch (\Exception $e) {
                //     Notification::make()
                //         ->title('Помилка збереження: ' . $e->getMessage())
                //         ->danger()
                //         ->send();
                // }
            });
    }

    public static function actionStopProduction(Production $record)
    {
        return Action::make('stopProduction')
            ->label('Зупинити виробництво')
            ->icon('heroicon-o-stop')
            ->visible(fn (Production $record) => $record->status === 'в роботі')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (Production $record): void {
                //$record->status = 'в роботі';
                ProductionService::stopProduction($record);
                // try {
                //     if (ProductionService::startProduction($record)) {
                //         Notification::make()
                //             ->title('Виробництво успішно запущено!')
                //             ->success()
                //             ->send();
                //     } else {
                //         Notification::make()
                //             ->title('Не вдалося запустити виробництво!')
                //             ->danger()
                //             ->send();
                //     }
                // } catch (\Exception $e) {
                //     Notification::make()
                //         ->title('Помилка збереження: ' . $e->getMessage())
                //         ->danger()
                //         ->send();
                // }
            });
    }


    public static function actionPauseProduction(Production $record)
    {
        return Action::make('pauseProduction')
            ->label('Призупинити виробництво')
            ->icon('heroicon-o-pause')
            ->visible(fn (Production $record) => $record->status === 'в роботі')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (Production $record): void {
                //$record->status = 'в роботі';
                ProductionService::pauseProduction($record);
                // try {
                //     if (ProductionService::startProduction($record)) {
                //         Notification::make()
                //             ->title('Виробництво успішно запущено!')
                //             ->success()
                //             ->send();
                //     } else {
                //         Notification::make()
                //             ->title('Не вдалося запустити виробництво!')
                //             ->danger()
                //             ->send();
                //     }
                // } catch (\Exception $e) {
                //     Notification::make()
                //         ->title('Помилка збереження: ' . $e->getMessage())
                //         ->danger()
                //         ->send();
                // }
            });
    }


    public static function actionEditProduction(Production $record)
    {
      return  Action::make('editProduction')
                ->label('Редагувати')
                ->icon('heroicon-o-pencil')
                ->color('info')
                ->requiresConfirmation()
                ->form([
                    TextInput::make('name')
                        ->label('Назва')
                        ->required()
                        ->default($record->name),
                    Textarea::make('description')
                        ->label('Опис')
                        ->maxLength(255)
                        ->default($record->description),
                    TextInput::make('quantity')
                        ->label('Кількість')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default($record->quantity),
                ])
                ->action(function (array $data, Production $record): void {
                    ProductionService::updateProduction($record,$data);
                    // $record->name = $data['name'];
                    // $record->description = $data['description'];
                    // $record->quantity = $data['quantity'];
                    // try {
                    //     if () {
                    //         Notification::make()
                    //             ->title('Виробництво успішно змінено!')
                    //             ->success()
                    //             ->send();
                    //     } else {
                    //         Notification::make()
                    //             ->title('Не вдалося змінити виробництво!')
                    //             ->danger()
                    //             ->send();
                    //     }
                    // } catch (\Exception $e) {
                    //     Notification::make()
                    //         ->title('Помилка збереження: ' . $e->getMessage())
                    //         ->danger()
                    //         ->send();
                    // }
                });
        }



    public static function addInvoiceOn($record){

        return Action::make('addInvoiceOn')
            ->label('Встановити вартість виробу і перемістити на склад')
            ->icon('heroicon-o-calculator')
            ->color('success')
            ->visible(fn () => $record->status === 'виготовлено')
            ->form([
                Select::make('type_price')
                    ->label('Спосіб націнки')
                    ->options([
                        'у відсотках' => 'у відсотках',
                        'у гривнях' => 'у гривнях',
                    ])
                    ->default('у відсотках')
                    ->reactive()
                    ->required(),
                TextInput::make('addprice')
                    ->label('Сума націнки')
                    ->required()
                    ->numeric()
                    ->prefix('₴')
                    ->visible(fn (Get $get) => $get('type_price') === 'у гривнях'),
                TextInput::make('addprice_percent')
                    ->label('Сума націнки у відсотках')
                    ->required()
                    ->numeric()
                    ->prefix('%')
                    ->visible(fn (Get $get) => $get('type_price') === 'у відсотках'),
                Select::make('warehouse_id')
                    ->label('Перемістити на склад')
                    ->options(Warehouse::pluck('name', 'id'))
                    ->required(),
            ])
            ->action(function (array $data, Production $record): void {
                ProductionService::createInvoice($record, $data);
            });
    }



    public static function addInvoiceSales($record){

        return Action::make('addInvoiceOn')
            ->label('Продати виріб замовнику')
            ->icon('heroicon-o-shopping-cart')
            ->color('success')
            //->visible(fn () => $record->status === 'виготовлено')
            ->form([
                Select::make('type_price')
                    ->label('Спосіб націнки')
                    ->options([
                        'у відсотках' => 'у відсотках',
                        'у гривнях' => 'у гривнях',
                    ])
                    ->default('у відсотках')
                    ->reactive()
                    ->required(),
                TextInput::make('addprice')
                    ->label('Сума націнки')
                    ->required()
                    ->numeric()
                    ->prefix('₴')
                    ->visible(fn (Get $get) => $get('type_price') === 'у гривнях'),
                TextInput::make('addprice_percent')
                    ->label('Сума націнки у відсотках')
                    ->required()
                    ->numeric()
                    ->prefix('%')
                    ->visible(fn (Get $get) => $get('type_price') === 'у відсотках'),
                // Select::make('warehouse_id')
                //     ->label('Перемістити на склад')
                //     ->options(Warehouse::pluck('name', 'id'))
                //     ->required(),
                Select::make('customer_id')
                    ->label('Кілєнт')
                    ->options(Customer::pluck('name', 'id'))
                    ->default($record->customer_id) // якщо користувач має клієнта, то встановлюємо його
                    ->required(),
            ])
            ->action(function (array $data, Production $record): void {
                ProductionService::createInvoice($record, $data);
            });
    }



    public static function recalculatePrice($record){

        return Action::make('recalculatePrice')
            ->label('Перерахувати вартість')
            ->icon('heroicon-o-calculator')
            ->color('warning')
            ->hidden(fn () => $record->status !== 'створено')
            ->requiresConfirmation()
            ->action(function (Production $record): void {
                try {
                    $record->price = $record->getTotalCostWithStagesAndMarkup(); // перерахунок вартості
                    if ($record->save()) {
                        Notification::make()
                            ->title('Вартість успішно перераховано!')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Не вдалося перерахувати вартість!')
                            ->danger()
                            ->send();
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Помилка перерахунку: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }



    /**
     * Отримуємо етапи виробництва
     *
     * @param Production $record
     * @return array
     */
    // отримуємо етапи виробництва
    public static function buildStagesProduction(Production $record): array
    {
        $productionStages = $record->productionStages; // отримуємо етапи виробництва
        $stages = [];
        $stages[] = BAction::make([
            Action::make('addStage')
            ->label('Додати етап')
            ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->form([
                TextInput::make('name')
                    ->label('Назва етапу')
                    ->required(),
                TextInput::make('description')
                    ->label('Опис етапу')
                    ->required(),
                // Select::make('status')
                //     ->label('Статус етапу')
                //     ->options([
                //         'очікує' => 'очікує',
                //         'в роботі' => 'в роботі',
                //         'виготовлено' => 'виготовлено',
                //         'скасовано' => 'скасовано',
                //     ])
                //     ->default('очікує')
                //     ->required(),
                TextInput::make('paid_worker')
                    ->label('Оплата працівнику')
                    ->required()
                    ->numeric()
                    //->hidden(auth()->user()->role !== 'admin')
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
                $stage->status = 'очікує';
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
        ]);
        // ->headerActions([
        //    ]);
        foreach ($productionStages as $stage) {
            $stages[] = Fieldset::make('Етап виробництва - ' . $stage->name)
                            //->collapsed($stage->status !== 'в роботі')
                            ->columnSpanFull()
                            ->schema([
                                BAction::make([
                                    Action::make('edit'.$stage->id)
                                    ->label('Редагувати')
                                    ->hidden($stage->status === 'виготовлено')
                                    ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
                                    ->icon('heroicon-o-pencil')
                                    ->color('primary')
                                    ->form([
                                        TextInput::make('name')
                                            ->label('Назва етапу')
                                            ->required()
                                            ->default($stage->name),
                                        TextInput::make('description')
                                            ->label('Опис етапу')
                                            ->default($stage->description),
                                        // Select::make('status')
                                        //     ->label('Статус етапу')
                                        //     ->options([
                                        //         'очікує' => 'очікує',
                                        //         'в роботі' => 'в роботі',
                                        //         'виготовлено' => 'виготовлено',
                                        //         'скасовано' => 'скасовано',
                                        //     ])
                                        //     ->default($stage->status)
                                        //     ->required(),
                                        TextInput::make('paid_worker')
                                            ->label('Оплата працівнику')
                                            ->required()
                                            //->hidden(auth()->id() !== $stage->user_id)
                                            //->visible(auth()->user()->role === 'admin')
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
                                        //$stage->status = $data['status'];
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
                                    }),

                                    Action::make('delete'.$stage->id)
                                        ->label('Видалити')
                                        ->icon('heroicon-o-trash')
                                        ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
                                        ->color('danger')
                                        ->requiresConfirmation()
                                        ->action(function (Production $record) use ($stage): void {
                                            try {
                                                if ($stage->delete()) {
                                                    Notification::make()
                                                        ->title('Етап виробництва успішно видалено!')
                                                        ->success()
                                                        ->send();
                                                } else {
                                                    Notification::make()
                                                        ->title('Не вдалося видалити етап виробництва!')
                                                        ->danger()
                                                        ->send();
                                                }
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Помилка видалення: ' . $e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                        Action::make('start-'.$stage->id.'-stage')
                                        ->label('Почати')
                                        //->visible(fn (ProductionStage $stage, Production $record) => $stage->status == 'очікує')
                                        //->hidden(fn (Production $record, ProductionStage $stage) => $record->status != 'в роботі' and $stage->status === 'в роботі' ||  $record->status != 'в роботі' and $stage->status === 'виготовлено')
                                        ->hidden(fn (Production $record) => $record->status != 'в роботі')
                                        ->icon('heroicon-o-play')
                                        ->color('success')
                                        ->requiresConfirmation()
                                        ->action(function (Production $record) use ($stage): void {
                                            try {
                                                if (ProductionService::startStage($stage)) {
                                                    Notification::make()
                                                        ->title('Етап виробництва успішно стартував!')
                                                        ->success()
                                                        ->send();
                                                } else {
                                                    Notification::make()
                                                        ->title('Не вдалося запустити етап!')
                                                        ->danger()
                                                        ->send();
                                                }
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Помилка старту: ' . $e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),

                                        Action::make('end-'.$stage->id.'-stage')
                                            ->label('Завершити')
                                            //->visible(fn (ProductionStage $stage, Production $record) => $stage->status == 'очікує')
                                            //->hidden(fn (Production $record, ProductionStage $stage) => $record->status != 'в роботі' and $stage->status === 'в роботі' ||  $record->status != 'в роботі' and $stage->status === 'виготовлено')
                                            ->hidden(fn (Production $record) => $record->status != 'в роботі')
                                            ->icon('heroicon-o-stop')
                                            ->color('warning')
                                            ->requiresConfirmation()
                                            ->action(function (Production $record) use ($stage): void {
                                                ProductionService::endStage($stage);
                                            })

                                ]),
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
                                    ->default($stage->paid_worker * $record->quantity)
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


}
