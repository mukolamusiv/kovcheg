<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Widgets\StockAndFinanceChart;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Contracts\Support\Htmlable;

class UserProfilePage extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([

                FileUpload::make('profile_photo_path')
                            ->image()
                            ->directory('profile-photos')
                            ->maxSize(2048),
                TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                TextInput::make('address')
                            ->maxLength(255),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Профіль користувача');
    }

    public function getWidgetData(): array
    {
        return [
            StockAndFinanceChart::class
        ];
    }
}
