<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FopResource\Pages;
use App\Filament\Resources\FopResource\RelationManagers;
use App\Models\Fop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FopResource extends Resource
{
    protected static ?string $model = Fop::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    //обмеження доступу
    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Назва ФОП'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->nullable()
                    ->label('Email ФОП'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->nullable()
                    ->label('Телефон ФОП'),
                Forms\Components\TextInput::make('ipn')
                    ->numeric()
                    ->nullable()
                    ->label('Ідентифікаційний код ФОП'),
                Forms\Components\TextInput::make('address')
                    ->nullable()
                    ->label('Адреса ФОП'),
                Forms\Components\TextInput::make('iban')
                    ->nullable()
                    ->label('Рахунок IBAN ФОП'),
                Forms\Components\TextInput::make('bank_name')
                    ->nullable()
                    ->label('Назва банку ФОП'),
                Forms\Components\TextInput::make('bank_code')
                    ->nullable()
                    ->label('Код банку ФОП'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва ФОП')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email ФОП')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ipn')
                    ->label('Ідитифікаційний код ФОП')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон ФОП')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Адреса ФОП')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('iban')
                    ->label('Рахунок IBAN ФОП')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Назва банку ФОП')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank_code')
                    ->label('Код банку ФОП')
                    ->searchable()
                    ->sortable(),
            ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFops::route('/'),
            'create' => Pages\CreateFop::route('/create'),
            'view' => Pages\ViewFop::route('/{record}'),
            'edit' => Pages\EditFop::route('/{record}/edit'),
        ];
    }
}
