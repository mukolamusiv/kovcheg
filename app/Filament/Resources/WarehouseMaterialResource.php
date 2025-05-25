<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseMaterialResource\Pages;
use App\Filament\Resources\WarehouseMaterialResource\RelationManagers;
use App\Models\WarehouseMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseMaterialResource extends Resource
{
    protected static ?string $model = WarehouseMaterial::class;

        //обмеження доступу
        public static function canViewAny(): bool
        {
            return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
        }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

      //назва ресурсу
      protected static ?string $label = 'Залишки на складі';
      //protected static ?string $pluralLabel = 'Накладні';

      protected static ?string $navigationLabel = 'Залишки на складі';
      protected static ?string $navigationGroup = 'Сировина та склади';

      protected static ?string $modelLabel = 'На складі';

      protected static ?string $pluralModelLabel = 'Залишки на складі';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warehouse_id')
                    ->label('Склад')
                    ->relationship('warehouse', 'name')
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва складу')
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Адреса')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->label('Опис')
                            ->maxLength(255),
                    ]),
                Forms\Components\Select::make('material_id')
                    ->label('Матеріал')
                    ->relationship('material', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва матеріалу')
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label('Одиниця виміру')
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Категорія')
                            ->relationship('category', 'name')
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Назва категорії')
                                    ->required(),
                            ]),
                    ]),
                Forms\Components\TextInput::make('quantity')
                    ->label('Кількість')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->label('Ціна')
                    ->required()
                    ->numeric()
                    ->prefix('₴'),
                Forms\Components\TextInput::make('description')
                    ->label('Опис')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->groups([
            Group::make('material.category.name')
                ->label('Категорія')
                ->collapsible(),
        ])
            ->columns([
                Tables\Columns\TextColumn::make('material.barcode')
                    ->label('Штрих-код')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('material.category.name')
                    ->label('Категорія')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Склад')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('material.name')
                    ->label('Матеріал')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('material.image')
                    ->label('Фото матеріалу')
                    ->circular(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кількість')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->money('UAH', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Загальна вартість')
                    ->getStateUsing(fn ($record) => $record->quantity * $record->price)
                    ->money('UAH', true),

                Tables\Columns\TextColumn::make('description')
                    ->label('Опис')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouseMaterials::route('/'),
            'create' => Pages\CreateWarehouseMaterial::route('/create'),
            'view' => Pages\ViewWarehouseMaterial::route('/{record}'),
            'edit' => Pages\EditWarehouseMaterial::route('/{record}/edit'),
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
