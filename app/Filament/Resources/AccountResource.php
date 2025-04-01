<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-euro';

     //назва ресурсу
     protected static ?string $label = 'Фінансові рахунки';

     //protected static ?string $pluralLabel = 'Накладні';

     protected static ?string $navigationLabel = 'Фінансові рахунки';
     protected static ?string $navigationGroup = 'Фінанси';

     protected static ?string $modelLabel = 'Рахунок';

     protected static ?string $pluralModelLabel = 'Рахунки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Назва гаманця')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                ->label('Опис')
                    ->maxLength(255),
                Forms\Components\TextInput::make('balance')
                    ->label('Баланс')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\Select::make('account_type')
                    ->label('Тип рахунку')
                    ->required()
                    ->options([
                        'актив' => 'Актив',
                        'пасив' => 'Пасив',
                        'дохід' => 'Дохід',
                        'витрати' => 'Витрати',
                    ]),
                Forms\Components\Select::make('account_category')
                    ->label('Категорія рахунку')
                    ->required()
                    ->options([
                        'готівка' => 'Готівка',
                        'банк' => 'Банк',
                        'клієнт' => 'Клієнт',
                        'постачальник' => 'Постачальник',
                        'інше' => 'Інше',
                    ]),
                Forms\Components\Select::make('currency')
                    ->label('Валюта')
                    ->required()
                    ->options([
                        'UAH' => 'UAH',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                    ]),
                // Forms\Components\TextInput::make('owner_id')
                //     ->numeric(),
                // Forms\Components\TextInput::make('owner_type')
                //     ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва гаманця')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Опис')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Баланс')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->label('Тип рахунку'),
                Tables\Columns\TextColumn::make('account_category')
                    ->label('Категорія рахунку'),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Валюта'),
                Tables\Columns\TextColumn::make('owner_id')
                    ->label('ID Власника')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('owner_type')
                //     ->label('Тип Власника')
                //     ->searchable(),
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
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'view' => Pages\ViewAccount::route('/{record}'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
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
