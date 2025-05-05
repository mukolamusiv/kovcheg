<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Helpers\InvoiceSectionBuilder;
use Filament\Actions;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;


use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Table;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
                ->columns(12)
                ->schema([
                    //dd($this->getRecord()),
                    InvoiceSectionBuilder::buildSection($this->getRecord()),

                    //InvoiceSectionBuilder::buildSection($record->invoice_off,' СПИСАННЯ'),
                ]);
    }
}
