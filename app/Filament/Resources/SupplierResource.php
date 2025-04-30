<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

      //назва ресурсу
   protected static ?string $label = 'Постачальники';
   //protected static ?string $pluralLabel = 'Накладні';

   protected static ?string $navigationLabel = 'Постачальники';
   protected static ?string $navigationGroup = 'Продажі';

   protected static ?string $modelLabel = 'Постачальник';

   protected static ?string $pluralModelLabel = 'Постачальники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Ім\'я')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('Адреса')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Нотатки')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('BankDetails')
                        ->label('Банківські реквізити')
                        ->relationship('BankDetails')
                        ->schema([
                            Forms\Components\TextInput::make('bank_name')
                                ->label('Назва банку')
                                ->numeric( )
                                ->maxLength(255),
                            Forms\Components\TextInput::make('bank_account')
                                ->label('Банківський рахунок')
                                ->numeric( )
                                ->maxLength(255),
                            Forms\Components\TextInput::make('bank_code')
                                ->label('Код банку')
                                ->numeric( )
                                ->maxLength(255),
                            Forms\Components\TextInput::make('bank_address')
                                ->label('Адреса банку')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('bank_swift')
                                ->label('SWIFT код')
                                ->numeric( )
                                ->maxLength(255),
                            Forms\Components\TextInput::make('bank_iban')
                                ->label('IBAN')
                                ->numeric( )
                                ->maxLength(255),
                            Forms\Components\TextInput::make('bank_card_number')
                                ->label('Номер картки')
                                ->numeric( )
                                ->maxLength(255),
                            Forms\Components\TextInput::make('bank_card_name')
                                ->label('Ім\'я на картці')
                                ->maxLength(255),
                        ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
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
