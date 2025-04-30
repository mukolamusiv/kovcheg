<?php

namespace App\Filament\Resources\TemplateProductionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StagesRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Етапи виробництва';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Назва етапу')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->label('Опис')
                    ->maxLength(255),
                Forms\Components\TextInput::make('paid_worker')
                    ->label('Оплата працівника')
                    ->numeric()
                    ->default(100.00)
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Виконавець')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва етапу')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Опис')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_worker')
                    ->label('Оплата працівника')
                    ->sortable()
                    ->money('UAH', true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Виконавець')
                    ->sortable()
                    ->searchable(),
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
