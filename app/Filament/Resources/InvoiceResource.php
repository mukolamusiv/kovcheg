<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceItemsRelationManager;
use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceProductionItemsRelationManager;
use App\Filament\Resources\InvoiceResource\RelationManagers\TransactionsRelationManager;
use App\Models\Invoice;
use Filament\Forms;
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
                // Forms\Components\TextInput::make('invoice_number')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Номер накладної')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
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
                    ->label('Статус накладної'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Сума')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Постачальник')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Клієнт')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Користувач')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Дата накладної')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Дата проведення')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid')
                    ->label('Оплачено')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due')
                    ->label('Заборгованість')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Знижка')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping')
                    ->label('Доставка')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус накладної'),
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
            ->defaultSort('created_at', 'desc')
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
            TransactionsRelationManager::class,
            InvoiceItemsRelationManager::class,
            InvoiceProductionItemsRelationManager::class,
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
