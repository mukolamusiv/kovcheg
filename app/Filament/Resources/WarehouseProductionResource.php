<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseProductionResource\Pages;
use App\Filament\Resources\WarehouseProductionResource\RelationManagers;
use App\Models\WarehouseProduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseProductionResource extends Resource
{
    protected static ?string $model = WarehouseProduction::class;


              //назва ресурсу
   protected static ?string $label = 'Готова продукція';
   protected static ?string $pluralLabel = 'Готова продукція';

   protected static ?string $navigationLabel = 'Готова продукція';
   protected static ?string $navigationGroup = 'Продажі';


    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warehouse_id')
                    ->label('Склад')
                    ->relationship('warehouse', 'name')
                    ->required(),
                Forms\Components\Select::make('production_id')
                    ->label('Готова продукція')
                    ->relationship('production', 'name')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Кількість')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('price')
                    ->label('Ціна')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                // Forms\Components\TextInput::make('production.customer.name')
                //     ->label('Замовник')
                //     ->required(),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('production.customer.name')
                    ->label('Замовник')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('production.name')
                    ->label('Готова продукція')
                    ->sortable()
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кількість')
                    ->sortable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->sortable()
                    ->numeric()
                    ->sortable()
                    ->prefix('₴')
                    ->money()
                    ->sortable(),

                //     ->numeric()
                Tables\Columns\TextColumn::make('description')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Склад')
                    ->sortable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouseProductions::route('/'),
            'create' => Pages\CreateWarehouseProduction::route('/create'),
            'view' => Pages\ViewWarehouseProduction::route('/{record}'),
            'edit' => Pages\EditWarehouseProduction::route('/{record}/edit'),
        ];
    }
}
