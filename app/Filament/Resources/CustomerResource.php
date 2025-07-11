<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Filament\Resources\CustomerResource\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\TransactionsEntriesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\TransactionRelationManager;
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

        //обмеження доступу
        public static function canViewAny(): bool
        {
            return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
        }

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

                Forms\Components\Repeater::make('customer_robe_sizes')
                        ->relationship('customerRobeSizes')
                        ->schema([
                            Forms\Components\TextInput::make('throat')
                                ->label('Горловина')
                                ->numeric()
                                ->nullable(),
                            Forms\Components\TextInput::make('front')
                                ->label('Перед')
                                ->nullable(),
                            Forms\Components\TextInput::make('front_type')
                                ->label('Тип переду')
                                ->nullable(),
                            Forms\Components\TextInput::make('back')
                                ->label('Зад')
                                ->nullable(),
                            Forms\Components\TextInput::make('epitrachelion_length')
                                ->label('Єпитрахиль – довжина')
                                ->nullable(),
                            Forms\Components\TextInput::make('epitrachelion_type')
                                ->label('Тип єпитрахилі')
                                ->nullable(),
                            Forms\Components\TextInput::make('cuff_type')
                                ->label('Тип нарукавника')
                                ->nullable(),
                            Forms\Components\Toggle::make('awards')
                                ->label('Нагороди')
                                ->nullable(),
                            Forms\Components\TextInput::make('tape')
                                ->label('Тасьма')
                                ->nullable(),
                            Forms\Components\Toggle::make('clasp')
                                ->label('Застібка')
                                ->nullable(),
                        ])
                        ->label('Розміри ризи клієнта')
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('updated_at', 'desc')
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
            InvoicesRelationManager::class,
           // TransactionRelationManager::class,
            TransactionsEntriesRelationManager::class,
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
