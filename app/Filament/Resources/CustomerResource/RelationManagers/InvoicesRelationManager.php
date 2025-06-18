<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('Номер рахунку')
                    ->url(fn ($record) => route('filament.administrator.resources.invoices.view', ['record' => $record->id]))
                    ->searchable(),
                //Tables\Columns\TextColumn::make('customer_id')->label('Клієнт')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->label('Користувач')->sortable(),
                //Tables\Columns\TextColumn::make('supplier_id')->label('Постачальник')->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')->label('Склад')->sortable(),
                Tables\Columns\TextColumn::make('invoice_date')->label('Дата рахунку')->date()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->label('Термін оплати')->date()->sortable(),
                Tables\Columns\TextColumn::make('total')->label('Сума')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('paid')->label('Оплачено')->money('USD')->sortable(),
                //Tables\Columns\TextColumn::make('due')->label('Заборгованість')->money('USD')->sortable(),
                //Tables\Columns\TextColumn::make('discount')->label('Знижка')->money('USD')->sortable(),
                //Tables\Columns\TextColumn::make('shipping')->label('Доставка')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Тип')->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->label('Статус оплати')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Статус')->sortable(),
                //Tables\Columns\TextColumn::make('notes')->label('Примітки')->limit(50),
                Tables\Columns\TextColumn::make('id')
                    ->label('Переглянути рахунок')
                    ->url(fn ($record) => route('filament.administrator.resources.invoices.view', ['record' => $record->id]))
                    ->openUrlInNewTab(),
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
