<?php

namespace App\Filament\Resources\ТResource\Pages;

use App\Filament\Resources\ТResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Resources\Pages\ViewRecord;

class CustomProfilePage extends ViewRecord implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = User::class;
    protected static ?string $navigationLabel = null;
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Мій профіль';
    protected static string $view = 'filament-panels::components.pages.page'; // <- default layout view

    protected static ?string $slug = 'profile'; // URL буде /administrator/profile
    protected static ?string $routeName = 'filament.administrator.auth.profile';

    public function getFormStatePath(): string
    {
        return 'data';
    }
}
