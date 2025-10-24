<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceItemsRelationManager;
use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceProductionItemsRelationManager;
use App\Filament\Resources\InvoiceResource\RelationManagers\TransactionsRelationManager;
use App\Models\Invoice;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    //protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

        //іконка яка описує ресурс накладних

            //обмеження доступу
            public static function canViewAny(): bool
            {
                return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
            }

        protected static ?string $navigationIcon = 'heroicon-o-document-text'; //heroicon-o-clipboard-list

        //назва ресурсу
        protected static ?string $label = 'Накладні';
        //protected static ?string $pluralLabel = 'Накладні';

        protected static ?string $navigationLabel = 'Накладні';
        protected static ?string $navigationGroup = 'Документи';

        protected static ?string $modelLabel = 'Накладна';

        protected static ?string $pluralModelLabel = 'Накладні';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основна інформація')
                    ->label('Основна інформація')
                    ->description('Введіть основну інформацію про накладну')
                    ->schema([
                        Split::make([
                            Section::make([
                                Select::make('type')
                                    ->label('Тип накладної')
                                    ->options([
                                        'продаж'        => 'Продаж',
                                        'постачання'    => 'Постачання',
                                        'переміщення'      => 'переміщення',
                                        'повернення'      => 'повернення',
                                        'списання'      => 'Списання',
                                    ])
                                    ->required()
                                    ->reactive(),
                                Select::make('supplier_id')
                                    ->label('Постачальник')
                                    ->relationship('supplier', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->name ?? '—') // <- note ?-operator
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (callable $get) => $get('type') === 'постачання' || $get('type') === 'повернення'),
                                Select::make('customer_id')
                                    ->label('Клієнт')
                                    ->relationship('customer', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? '—')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (callable $get) => $get('type') === 'продаж' || $get('type') === 'повернення'),
                            ]),
                            Section::make([
                               Textarea::make('notes')
                                    ->label('Примітки')
                                    ->columnSpanFull()
                                    ->maxLength(255)
                                    ->placeholder('Введіть примітки до накладної'),
                            ]),
                        ])
                    ])
                    ->columnSpanFull(),

                Section::make('Матеріали')
                    ->label('Матеріали')
                    ->description('Введіть матеріали до накладної')
                    ->schema([
                            Section::make([
                                Forms\Components\Repeater::make('items_supliner')
                                    ->label('Матеріали')
                                    ->visible(fn (callable $get) => $get('type') === 'постачання')
                                    ->relationship('invoiceItems')
                                    ->schema([
                                        Select::make('material_id')
                                            ->label('Матеріал')
                                            ->relationship('material', 'name')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? '—')
                                            ->searchable()
                                            ->preload()
                                            ->columnSpan(6)
                                            ->required(),
                                        TextInput::make('quantity')
                                            ->label('Кількість')
                                            ->numeric()
                                            ->default(1)
                                            ->columnSpan(2)
                                            ->required(),
                                        TextInput::make('price')
                                            ->label('Вартість за одиницю')
                                            ->numeric()
                                            ->default(0.01)
                                            ->reactive()
                                            ->columnSpan(4)
                                            ->required(),
                                    ])
                                    ->columns([
                                        'sm' => 3,
                                        'lg' => 12,
                                    ]),
                                    Forms\Components\Repeater::make('items_customer')
                                    ->label('Матеріали')
                                    ->visible(fn (callable $get) => $get('type') !== 'постачання')
                                    ->relationship('invoiceItems')
                                    ->schema([
                                        Select::make('material_id')
                                            ->label('Матеріал')
                                            ->relationship('material', 'name')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? '—')
                                            ->searchable()
                                            ->preload()
                                            ->columnSpan(6)
                                            ->required(),
                                        TextInput::make('quantity')
                                            ->label('Кількість')
                                            ->numeric()
                                            ->default(1)
                                            ->columnSpan(2)
                                            ->required(),
                                    ])
                                    ->columns([
                                        'sm' => 3,
                                        'lg' => 12,
                                    ])

                                    //->columnSpanFull()

                           ])
                           ->columnSpan(8),
                        Section::make([
                                Select::make('warehouse_id')
                                    ->label('Склад')
                                    ->relationship('warehouse', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? '—')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('warehouse_id_to')
                                    ->label('Пермістити на склад')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? '—')
                                    ->relationship('warehouse', 'name')
                                    ->searchable()
                                    ->visible(fn (callable $get) => $get('type') === 'переміщення')
                                    ->preload()
                                    ->required(),
                                TextInput::make('shipping')
                                    ->label('Доставка')
                                    ->numeric()
                                    ->default(0.00)
                                    ->reactive(),
                                Select::make('saleUp')
                                    ->label('Націнка')
                                    ->hidden(fn (callable $get) => $get('discount') > 0)
                                    ->visible(fn (callable $get) => $get('type') === 'продаж')
                                    ->options([
                                        '0' => '0%',
                                        '5' => '5%',
                                        '10' => '10%',
                                        '15' => '15%',
                                        '20' => '20%',
                                        '25' => '25%',
                                        '30' => '30%',
                                        '35' => '35%',
                                        '40' => '40%',
                                        '45' => '45%',
                                        '50' => '50%',
                                    ])
                                    ->searchable()
                                    ->reactive()
                                    ->preload(),
                                Select::make('discount')
                                    ->label('Знижка')
                                    ->hidden(fn (callable $get) => $get('saleUp') > 0)
                                    ->visible(fn (callable $get) => $get('type') === 'продаж')
                                    ->reactive()
                                    ->options([
                                        '0' => '0%',
                                        '5' => '5%',
                                        '10' => '10%',
                                        '15' => '15%',
                                        '20' => '20%',
                                        '25' => '25%',
                                        '30' => '30%',
                                        '35' => '35%',
                                        '40' => '40%',
                                        '45' => '45%',
                                        '50' => '50%',
                                    ])
                                    ->searchable()
                                    ->preload(),
                            ])->columnSpan(4),
                    ])
                    ->columns([
                        'sm' => 6,
                        'lg' => 12,
                    ])
                    ->columnSpanFull(),

                // // Section::make('Матеріали')
                // //     ->label('Матеріали')
                // //     ->visible(fn (callable $get) => $get('type') != 'постачання')
                // //     ->description('Введіть матеріали до накладної')
                // //     ->schema([
                // //             Section::make([
                // //                 Forms\Components\Repeater::make('items')
                // //                     ->label('Матеріали')
                // //                     ->relationship('invoiceItems')
                // //                     ->schema([
                // //                         Select::make('material_id')
                // //                             ->label('Матеріал')
                // //                             ->relationship('material', 'name')
                // //                             ->searchable()
                // //                             ->preload()
                // //                             // ->validationAttribute(fn (callable $get) => \App\Models\WarehouseMaterial::where('warehouse_id', $get('warehouse_id'))
                // //                             //     ->where('material_id', $get('material_id'))
                // //                             //     ->exists())
                // //                             // ->validationMessages([
                // //                             //         'material_id' => 'Виберіть матеріал',
                // //                             //     ])
                // //                             //->visible(fn (callable $get) => in_array($get('type'), ['продаж','постачання', 'списання']))
                // //                             // ->required(fn (callable $get) => \App\Models\WarehouseMaterial::where('warehouse_id', $get('warehouse_id'))
                // //                             //     ->where('material_id', $get('material_id'))
                // //                             //     ->exists(false))
                // //                             ->columnSpan(8)
                // //                             ->required(),
                // //                         TextInput::make('quantity')
                // //                             ->label('Кількість')
                // //                             ->numeric()
                // //                             ->default(1)
                // //                             ->columnSpan(4)
                // //                             ->required(),
                // //                         TextInput::make('price')
                // //                             ->label('Вартість за одиницю')
                // //                             ->numeric()
                // //                             ->default(0.01)
                // //                             ->reactive()
                // //                             ->hidden(true)
                // //                             ->columnSpan(4)
                // //                             ->required(),
                // //                     ])
                // //                     ->columns([
                // //                         'sm' => 3,
                // //                         'lg' => 12,
                // //                     ])
                // //                     //->columnSpanFull()

                // //            ])
                // //            ->columnSpan(8),
                // //         Section::make([
                // //                 Select::make('warehouse_id')
                // //                     ->label('Склад')
                // //                     ->relationship('warehouse', 'name')
                // //                     ->searchable()
                // //                     ->preload()
                // //                     ->required(),
                // //                 TextInput::make('shipping')
                // //                     ->label('Доставка')
                // //                     ->numeric()
                // //                     ->default(0.00)
                // //                     ->reactive(),
                // //                 Select::make('saleUp')
                // //                     ->label('Націнка')
                // //                     ->hidden(fn (callable $get) => $get('discount') > 0)
                // //                     ->options([
                // //                         '0' => '0%',
                // //                         '5' => '5%',
                // //                         '10' => '10%',
                // //                         '15' => '15%',
                // //                         '20' => '20%',
                // //                         '25' => '25%',
                // //                         '30' => '30%',
                // //                         '35' => '35%',
                // //                         '40' => '40%',
                // //                         '45' => '45%',
                // //                         '50' => '50%',
                // //                     ])
                // //                     ->searchable()
                // //                     ->reactive()
                // //                     ->preload(),
                // //                 Select::make('discount')
                // //                     ->label('Знижка')
                // //                     ->hidden(fn (callable $get) => $get('saleUp') > 0)
                // //                     ->reactive()
                // //                     ->options([
                // //                         '0' => '0%',
                // //                         '5' => '5%',
                // //                         '10' => '10%',
                // //                         '15' => '15%',
                // //                         '20' => '20%',
                // //                         '25' => '25%',
                // //                         '30' => '30%',
                // //                         '35' => '35%',
                // //                         '40' => '40%',
                // //                         '45' => '45%',
                // //                         '50' => '50%',
                // //                     ])
                // //                     ->searchable()
                // //                     ->preload(),
                // //             ])->columnSpan(4),
                // //     ])
                // //     ->columns([
                // //         'sm' => 6,
                // //         'lg' => 12,
                // //     ])
                // //     ->columnSpanFull(),







                Section::make('Виробництво')
                    ->label('Вироби')
                    ->visible(true)
                    //->visible(fn (callable $get) => $get('type') === 'продаж' || $get('type') === 'повернення' || $get('type') === 'переміщення')
                    ->description('Введіть замовлення до накладної')
                    ->schema([
                            Section::make([
                                Forms\Components\Repeater::make('productions')
                                    ->label('Замовлення на виробництво')
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Готова продукція на скаладі')
                                            //->getOptionLabelFromRecordUsing(fn ($record) => $record?->name ?? '—') // <- note ?-operator
                                            ->options(
                                                    \App\Models\WarehouseProduction::with('production')
                                                        ->get()
                                                        ->mapWithKeys(fn ($wp) => [$wp->id => $wp->production?->name ?? '—'])
                                                        ->toArray()
                                                )
                                            //->options(\App\Models\WarehouseProduction::with('production')->get()->pluck('production.name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->columnSpan(6)
                                            ->required(),
                                        TextInput::make('quantity')
                                            ->label('Кількість виробів')
                                            ->numeric()
                                            ->columnSpan(3)
                                            ->required(),
                                    ])
                                    ->columns(12)
                                    //->columnSpanFull()
                                    ->columns([
                                        'sm' => 6,
                                        'lg' => 12,
                                    ]),

                                ]),
                            ]),
                        ]);



                // // Forms\Components\TextInput::make('invoice_number')
                //     ->label('Номер накладної')
                //     //->required()
                //     //->hidden(true)
                //     ->maxLength(255),
                // Forms\Components\Select::make('customer_id')
                //     ->label('Клієнт')
                //     ->relationship('customer', 'name')
                //     ->searchable()
                //     ->preload()
                //     ->createOptionForm([
                //         Forms\Components\TextInput::make('name')
                //             ->required()
                //             ->maxLength(255),
                //         // Add other customer fields here
                //     ]),
                // Forms\Components\Select::make('user_id')
                //     ->relationship('user', 'name')
                //     ->searchable()
                //     ->hidden(true)
                //     ->label('Користувач що провів накладну')
                //     ->preload()
                //     ->createOptionForm([
                //         Forms\Components\TextInput::make('name')
                //             ->required()
                //             ->maxLength(255),
                //         // Add other user fields here
                //     ]),
                // Forms\Components\Select::make('supplier_id')
                //     ->relationship('supplier', 'name')
                //     ->searchable()
                //     ->label('Постачальник')
                //     ->preload()
                //     ->createOptionForm([
                //         Forms\Components\TextInput::make('name')
                //             ->required()
                //             ->maxLength(255),
                //         // Add other supplier fields here
                //     ]),
                // Forms\Components\DatePicker::make('invoice_date')
                //     ->label('Дата накладної'),
                // Forms\Components\DatePicker::make('due_date')
                //     ->hidden(true)
                //     ->label('Дата проведення'),
                // Forms\Components\TextInput::make('total')
                //     ->label('Сума')
                //     ->required()
                //     ->default(0.00)
                //     ->numeric(),
                // Forms\Components\TextInput::make('paid')
                //     ->label('Оплачено')
                //     ->required()
                //     ->default(0.00)
                //     ->numeric(),
                // Forms\Components\TextInput::make('due')
                //     ->label('Заборгованість')
                //     ->required()
                //     ->numeric()
                //     ->default(0.00),
                // Forms\Components\TextInput::make('discount')
                //     ->label('Знижка')
                //     ->required()
                //     ->numeric()
                //     ->default(0.00),
                // Forms\Components\TextInput::make('shipping')
                //     ->label('Доставка')
                //     ->required()
                //     ->numeric()
                //     ->default(0.00),
                // Forms\Components\Select::make('type')
                //     ->options([
                //         'постачання' => 'постачання',
                //         'переміщення' => 'переміщення',
                //         'продаж' => 'продаж',
                //         'повернення' => 'повернення',
                //         'списання' => 'списання',
                //     ])
                //     ->default('продаж')
                //     ->required(),
                // Forms\Components\Select::make('payment_status')
                //     ->label('Статус оплати')
                //     ->options([
                //         'оплачено' => 'оплачено',
                //         'частково оплачено' => 'частково оплачено',
                //         'не оплачено' => 'не оплачено',
                //     ])
                //     ->default('не оплачено')
                //     ->required(),
                // Forms\Components\Select::make('status')
                //     ->label('Статус накладної')
                //     ->options([
                //         'створено' => 'створено',
                //         'проведено' => 'проведено',
                //         'скасовано' => 'скасовано',
                //     ])
                //     ->default('створено')
                //     ->required(),
                // Forms\Components\Textarea::make('notes')
                //     ->label('Примітки')
                //     ->columnSpanFull(),
           // ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Клієнт')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->colors([
                        'success' => 'продаж',
                        'warning' => 'постачання',
                        'danger' => 'переміщення',
                        'primary' => 'повернення',
                        'secondary' => 'списання',
                    ])
                    ->icons([
                        'heroicon-o-truck' => 'постачання',
                        'heroicon-o-shopping-cart' => 'переміщення',
                        'heroicon-o-shopping-cart' => 'продаж',
                        'heroicon-o-refresh' => 'повернення',
                        'heroicon-o-trash' => 'списання',
                    ]),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Статус оплати')
                    ->colors([
                        'success' => 'оплачено',
                        'warning' => 'частково оплачено',
                        'danger' => 'не оплачено',
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->label('Статус накладної'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Сума')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Постачальник')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Номер накладної')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Користувач')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Дата накладної')
                    ->date()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('due_date')
                //     ->label('Дата проведення')
                //     ->date()
                //     ->sortable(),

                Tables\Columns\TextColumn::make('paid')
                    ->label('Оплачено')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due')
                    ->label('Заборгованість')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('discount')
                //     ->label('Знижка')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('shipping')
                //     ->label('Доставка')
                //     ->numeric()
                //     ->sortable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InvoiceItemsRelationManager::class,
            InvoiceProductionItemsRelationManager::class,
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create'=> Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

/*

 Forms\Components\Wizard::make([
                    Step::make('Тип накладної')
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->label('Тип накладної')
                                ->options([
                                    'постачання'    => 'Постачання',
                                    'продаж'        => 'Продаж',
                                    'переміщення'      => 'переміщення',
                                    'повернення'      => 'повернення',
                                    'списання'      => 'Списання',
                                ])
                                ->required()
                                ->reactive(),
                        ]),
                    Step::make('Інформація про накладну')
                        ->schema([
                            Forms\Components\Select::make('supplier_id')
                                ->label('Постачальник')
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload()
                                ->visible(fn (callable $get) => $get('type') === 'постачання'),
                            Forms\Components\Select::make('customer_id')
                                ->label('Клієнт')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->visible(fn (callable $get) => $get('type') === 'продаж'),
                        ]),
                    Step::make('Позиції накладної')
                        ->schema([
                            Forms\Components\Select::make('warehouse_id')
                                ->label('Склад')
                                ->options(\App\Models\Warehouse::all()->pluck('name', 'id'))
                                ->required(),
                            Forms\Components\Repeater::make('items')
                                ->visible(fn (callable $get) => $get('type') === 'постачання')
                                ->label('Матеріали')
                                ->schema([
                                    Forms\Components\Select::make('material_id')
                                        ->label('Матеріал')
                                        ->options(\App\Models\Material::all()->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload(),
                                        //->visible(fn (callable $get) => in_array($get('type'), ['продаж','постачання', 'списання'])),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Кількість')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Вартість за одиницю')
                                        ->numeric()
                                        ->reactive()
                                        ->required(),
                                ])
                                ->columns(3),
                            Forms\Components\Repeater::make('items')
                                ->visible(fn (callable $get) => $get('type') === 'продаж')
                                ->label('Матеріали')
                                ->schema([
                                    Forms\Components\Select::make('material_id')
                                        ->label('Матеріал')
                                        ->options(\App\Models\Material::all()->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload(),
                                        //->visible(fn (callable $get) => in_array($get('type'), ['продаж','постачання', 'списання'])),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Кількість')
                                        ->numeric()
                                        ->required(),
                                ])
                                ->columns(2),
                        ]),
                ])->columnSpanFull()
 */
