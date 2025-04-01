<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   //назва ресурсу
   protected static ?string $label = 'Клієнти';
   //protected static ?string $pluralLabel = 'Накладні';

   protected static ?string $navigationLabel = 'Клієнти';
   protected static ?string $navigationGroup = 'Продажі';

   protected static ?string $modelLabel = 'Клієнт';

   protected static ?string $pluralModelLabel = 'Клієнти';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label('Ім\'я')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                ->label('Email')
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
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('size')
                        ->relationship('size')
                        ->schema([
                            Forms\Components\TextInput::make('throat')
                                ->label('Горло')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('redistribution')
                                ->label('Розподіл')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('behind')
                                ->label('Позаду')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('hips')
                                ->label('Стегна')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('length')
                                ->label('Довжина')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('sleeve')
                                ->label('Рукав')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('shoulder')
                                ->label('Плече')
                                ->numeric()
                                ->required(),
                            Forms\Components\Textarea::make('comment')
                                ->label('Коментар')
                                ->maxLength(255),
                        ])
                        ->label('Розміри клієнта')
                        ->createItemButtonLabel('Додати розмір')
                        ->columns(2),

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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
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
