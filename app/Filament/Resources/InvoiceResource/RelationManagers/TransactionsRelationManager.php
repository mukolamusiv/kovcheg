<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

       // назва звзязку
       protected static ?string $title = 'Оплати';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Номер посилання'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Опис'),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->dateTime()
                    ->label('Дата транзакції'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Користувач'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Клієнт'),
                Tables\Columns\TextColumn::make('entries.amount')
                    ->label('Сума'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
