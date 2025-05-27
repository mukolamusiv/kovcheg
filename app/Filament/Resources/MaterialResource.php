<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Filament\Resources\MaterialResource\RelationManagers\WarehousesRelationManager;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


        //обмеження доступу
        public static function canViewAny(): bool
        {
            return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
        }

         //назва ресурсу
         protected static ?string $label = 'Матеріали';
         //protected static ?string $pluralLabel = 'Накладні';

         protected static ?string $navigationLabel = 'Матеріали';
         protected static ?string $navigationGroup = 'Сировина та склади';

         protected static ?string $modelLabel = 'Матеріал';

         protected static ?string $pluralModelLabel = 'Матеріали';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('manufacturer_code')
                    ->label('Код виробника')
                    ->maxLength(255),
                Forms\Components\Select::make('supplier_id')
                    ->label('Постачальник')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва постачальника')
                            ->required(),
                        Forms\Components\TextInput::make('contact')
                            ->label('Контактна інформація')
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('fabric_color')
                    ->label('Колір тканини')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Опис')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->label('Зображення')
                    ->image(),
                Forms\Components\Select::make('unit')
                    ->label('Одиниця виміру')
                    ->options([
                        'метри погонні' => 'Метри погонні',
                        'одиниці' => 'Одиниці',
                        'кг' => 'Кілограми',
                        'літри' => 'Літри',
                    ])
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->label('Категорія')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва категорії')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Опис')
                            ->maxLength(255),
                        Forms\Components\Select::make('parent_id')
                            ->label('Батьківська категорія')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Штрихкод')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Опис')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Зображення'),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Одиниця виміру'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категорія')
                    ->sortable(),
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
            ->defaultSort('created_at', 'desc')
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
            WarehousesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'view' => Pages\ViewMaterial::route('/{record}'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
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
