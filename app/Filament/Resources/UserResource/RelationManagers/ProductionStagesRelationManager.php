<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionStagesRelationManager extends RelationManager
{
    protected static string $relationship = 'production_stages';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Етапи виробництва';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Назва'),
                Tables\Columns\TextColumn::make('description')->label('Опис'),
                Tables\Columns\TextColumn::make('status')->label('Статус'),
                Tables\Columns\TextColumn::make('production.name')->label('Виробництво'),
                Tables\Columns\TextColumn::make('updated_at')->label('Дата')->date(),
                Tables\Columns\BooleanColumn::make('paid_worker')->label('Оплачений працівник'),
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
            ->defaultSort('updated_at', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
