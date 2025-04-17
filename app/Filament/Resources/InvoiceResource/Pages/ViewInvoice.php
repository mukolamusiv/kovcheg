<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Helpers\InvoiceSectionBuilder;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

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
