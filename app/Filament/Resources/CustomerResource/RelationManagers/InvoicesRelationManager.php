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
                Tables\Columns\TextColumn::make('invoice_number')->label('Invoice Number')->searchable(),
                Tables\Columns\TextColumn::make('customer_id')->label('Customer')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->label('User')->sortable(),
                Tables\Columns\TextColumn::make('supplier_id')->label('Supplier')->sortable(),
                Tables\Columns\TextColumn::make('warehouse_id')->label('Warehouse')->sortable(),
                Tables\Columns\TextColumn::make('invoice_date')->label('Invoice Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->label('Due Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('total')->label('Total')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('paid')->label('Paid')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('due')->label('Due')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('discount')->label('Discount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('shipping')->label('Shipping')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->label('Payment Status')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->sortable(),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')->label('Deleted At')->dateTime()->sortable()->hidden(fn ($record) => !$record->trashed()),
                Tables\Columns\TextColumn::make('id')
                    ->label('View Invoice')
                    ->url(fn ($record) => route('filament.resources.invoices.view', ['record' => $record->id]))
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
