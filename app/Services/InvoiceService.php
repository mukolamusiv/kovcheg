<?php

namespace App\Services;

use App\Models\Invoice;
use Filament\Notifications\Notification;

class InvoiceService
{
    public static function moveInvoiceToConducted(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'проведено',
        ]);
        $invoice->save();
        Notification::make()
                ->title('Накладна проведена!')
                ->body('Потрібно здіснити дії на складі чи касі згідно накладної')
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();
    }

    public static function moveInvoiceToCreated(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'створено',
        ]);
        $invoice->save();
        Notification::make()
                ->title('Накладна скасована!')
                ->body('Повернути усе на місце що було на складі чи касі перед накладною')
                ->icon('heroicon-o-check-circle')
                ->warning()
                ->send();
    }


    public static function addInvoiceDiscount($invoice, $discount)
    {
        $invoice->update([
            'discount' => $discount,
        ]);
        $invoice->save();
    }

}

