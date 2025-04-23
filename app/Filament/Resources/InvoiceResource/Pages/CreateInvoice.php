<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public function create(bool $another = false): void
    {
        // Перевірка валідності даних та розрахунок ціни продукту
        $this->validate();

        // Створення накладної
       // $invoice = $this->record::create($this->data);
       //dd($this->data);
        $invoice = InvoiceService::makeInvoice($this->data);
        // // Сповіщення про успішне створення
        // Notification::make()
        //     ->title('Створено нову накладну!')
        //     ->success()
        //     ->send();

        // Перенаправлення на сторінку перегляду або список записів
        $this->redirect(InvoiceResource::getUrl('view', ['record' => $invoice->id]));

        // Зупинка подальшого виконання
        return;
    }

}
