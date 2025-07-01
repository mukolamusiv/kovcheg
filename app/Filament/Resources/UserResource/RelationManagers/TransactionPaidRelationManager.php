<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionPaidRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionPaid';

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
                Tables\Columns\TextColumn::make('transaction_id')->label('Transaction ID'),
                Tables\Columns\TextColumn::make('transaction.reference_number')->label('Reference Number'),
                Tables\Columns\TextColumn::make('transaction.description')->label('Опис')->limit(50),
                Tables\Columns\TextColumn::make('transaction.transaction_date')->label('Transaction Date')->date(),
                Tables\Columns\TextColumn::make('transaction.status')->label('Status'),
                Tables\Columns\TextColumn::make('entry_type')->label('User ID'),
                Tables\Columns\TextColumn::make('amount')->label('Сума')
                    ->money('uan', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime(),
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
