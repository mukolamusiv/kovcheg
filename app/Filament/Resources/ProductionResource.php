<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionResource\Pages;
use App\Filament\Resources\ProductionResource\RelationManagers;
use App\Filament\Resources\ProductionResource\RelationManagers\ProductionMaterialRelationManager;
use App\Filament\Resources\ProductionResource\RelationManagers\ProductionStagesRelationManager;
use App\Models\Production;
use App\Models\TemplateProduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionResource extends Resource
{
    protected static ?string $model = Production::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    //обмеження доступу
    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
    }

          //назва ресурсу
   protected static ?string $label = 'Виробництво';
   //protected static ?string $pluralLabel = 'Накладні';

   protected static ?string $navigationLabel = 'Виробництво';
   protected static ?string $navigationGroup = 'Виробництво';

   protected static ?string $modelLabel = 'Виробництво';

   protected static ?string $pluralModelLabel = 'Виробництво';

    public static function form(Form $form): Form
    {
        return $form
        ->columns(1)
            ->schema([

                            Forms\Components\Select::make('template_productions_id')
                                ->label('Шаблон виробництва')
                                ->options(TemplateProduction::pluck('name', 'id'))
                                ->searchable()
                                ->preload()->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state === 'на продаж') {
                                        $set('customer_id', null);
                                    }
                                }),

                            Forms\Components\Select::make('warehouse_id')
                                    ->label('Виберіть склад')
                                    ->options(\App\Models\Warehouse::pluck('name', 'id'))
                                    ->required(),

                            // Forms\Components\Select::make('type')
                            //     ->label('Тип виробництва')
                            //     ->required()
                            //     ->default('замовлення')
                            //     ->options([
                            //         'замовлення' => 'замовлення',
                            //         'на продаж' => 'на продаж',
                            //     ])
                            //     ->reactive()
                            //     ->afterStateUpdated(function ($state, callable $set) {
                            //         if ($state === 'на продаж') {
                            //             $set('customer_id', null);
                            //         }
                            //     }),
                            Forms\Components\Select::make('customer_id')
                                ->label('Клієнт')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                //->hidden(fn ($get) => $get('type') === 'на продаж')
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('email')
                                        ->email()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('phone')
                                        ->tel()
                                        ->maxLength(255),
                                ]),

                            Forms\Components\TextInput::make('name')
                                ->label('Назва виробу')
                                ->required()
                                ->reactive()
                                ->hidden(fn ($get) => $get('template_productions_id'))
                                ->maxLength(255),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Кількість одиниць')
                                ->required()
                                ->default(1),
                            Forms\Components\Textarea::make('description')
                                ->label('Опис')
                                ->maxLength(255),
                            Forms\Components\Repeater::make('productionStages')
                                ->label('Етапи виробництва')
                                ->hidden(fn ($get) => $get('template_productions_id'))
                                ->relationship('productionStages')
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Назва етапу')
                                        ->required(),
                                    Forms\Components\TextInput::make('paid_worker')
                                        ->numeric()
                                        ->default(200)
                                        ->label('Оплата працівника')
                                        ->required(),
                                    Forms\Components\Select::make('user_id')
                                        ->label('Працівник')
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\Textarea::make('description')
                                        ->label('Опис')
                                        ->maxLength(65535),
                                ])
                                ->createItemButtonLabel('Додати етап'),

                                        // ->preload(),
                            Forms\Components\Repeater::make('productionMaterials')
                                ->label('Матеріали для виробництва')
                                ->relationship('productionMaterials')
                                ->hidden(fn ($get) => $get('template_productions_id'))
                                ->schema([
                                    Forms\Components\Select::make('material_id')
                                        ->label('Матеріал')
                                        ->relationship('material', 'name')
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Кількість')
                                        ->required()
                                        ->numeric(),
                                    // Forms\Components\TextInput::make('price')
                                    //     ->label('Ціна за одиницю')
                                    //     ->required()
                                    //     ->numeric(),
                                    Forms\Components\Textarea::make('description')
                                        ->label('Опис')
                                        ->maxLength(65535),
                                    // Forms\Components\DatePicker::make('date_writing_off')
                                    //     ->label('Дата списання'),
                                ])
                                ->createItemButtonLabel('Додати матеріал'),
                    // Forms\Components\Wizard\Step::make('Перевірка')
                    //     ->schema([
                    //         Forms\Components\ViewField::make('name')
                    //             ->label('Назва виробу'),
                    //         Forms\Components\ViewField::make('description')
                    //             ->label('Опис'),
                    //         Forms\Components\ViewField::make('status')
                    //             ->label('Статус'),
                    //         Forms\Components\ViewField::make('type')
                    //             ->label('Тип виробництва'),
                    //         Forms\Components\ViewField::make('customer_id')
                    //             ->label('Клієнт')
                    //             ->formatStateUsing(fn ($state) => $state ? \App\Models\Customer::find($state)->name : 'Не вказано'),
                    //         // Forms\Components\ViewField::make('productionStages')
                    //         //     ->label('Етапи виробництва')
                    //         //     ->formatStateUsing(fn ($state) => collect($state)->pluck('name')->join(', ')),
                    //         // Forms\Components\ViewField::make('productionMaterials')
                    //         //     ->label('Матеріали')
                    //         //     ->formatStateUsing(fn ($state) => collect($state)->pluck('material_id')->map(fn ($id) => \App\Models\Material::find($id)->name)->join(', ')),
                    //     ]),

            ]);
            //]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Замовник')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва виробу')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->color(function ($state) {
                        return match ($state) {
                            'створено' => 'info',
                            'в роботі' => 'warning',
                            'виготовлено' => 'success',
                            'скасовано' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pay')
                    ->label('Оплата')
                    ->hidden(true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Опис')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice.number')
                    ->label('Номер накладної')
                    ->searchable(),

                // Tables\Columns\TextColumn::make('user_id')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('quantity')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Варітсть')
                    ->money('UAN')
                    ->visible(fn () => auth()->user()->role == 'admin' || auth()->user()->role== 'manager')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('production_date')
                //     ->date()
                //     ->sortable(),
               // Tables\Columns\ImageColumn::make('image'),
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
            // ProductionMaterialRelationManager::class,
            // ProductionStagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductions::route('/'),
            'create' => Pages\CreateProduction::route('/create'),
            'view' => Pages\ViewProduction::route('/{record}'),
            'edit' => Pages\EditProduction::route('/{record}/edit'),
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
