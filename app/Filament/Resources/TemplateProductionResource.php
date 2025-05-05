<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateProductionResource\Pages;
use App\Filament\Resources\TemplateProductionResource\RelationManagers;
use App\Filament\Resources\TemplateProductionResource\RelationManagers\MaterialsRelationManager;
use App\Filament\Resources\TemplateProductionResource\RelationManagers\StagesRelationManager;
use App\Models\TemplateProduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TemplateProductionResource extends Resource
{
    protected static ?string $model = TemplateProduction::class;


        //обмеження доступу
        public static function canViewAny(): bool
        {
            return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
        }

    protected static ?string $navigationIcon = 'heroicon-s-cube-transparent';

    protected static ?string $navigationGroup = 'Виробництво';
    protected static ?string $navigationLabel = 'Шаблони виробництв';
    protected static ?string $label = 'Шаблон виробництва';
    protected static ?string $pluralLabel = 'Шаблони виробництв';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
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
            MaterialsRelationManager::class,
            StagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplateProductions::route('/'),
            'create' => Pages\CreateTemplateProduction::route('/create'),
            'edit' => Pages\EditTemplateProduction::route('/{record}/edit'),
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
