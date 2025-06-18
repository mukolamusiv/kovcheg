<?php

namespace App\Filament\Resources\SuplinerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionsEntries';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_id')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')->label('ID Транзакції'),
                Tables\Columns\TextColumn::make('account_id')->label('ID Рахунку'),
                Tables\Columns\TextColumn::make('entry_type')->label('Тип Запису'),
                Tables\Columns\TextColumn::make('amount')->label('Сума'),
                Tables\Columns\TextColumn::make('created_at')->label('Дата Створення')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->label('Дата Оновлення')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
