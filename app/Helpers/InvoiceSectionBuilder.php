<?php

namespace App\Helpers;



use App\Filament\Resources\ProductionResource;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceProductionItem;
use App\Models\Material;
use App\Models\Production;
use App\Models\ProductionSize;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use App\Traits\Filament\HasInvoiceSection;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Fieldset;

use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Closure;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Set;

use Filament\Infolists\Components\Actions as BAction;
use Filament\Infolists\Components\Tabs;
use Pest\ArchPresets\Custom;
use Filament\Support\Enums\Alignment;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use Filament\Infolists\Components\Split;

class InvoiceSectionBuilder
{

    public static function buildSection(Invoice $invoice, $type = '')
    {
        return Section::make('Накладна № '. $invoice->invoice_number.' '.$type)
            ->label('Накладна №'. $invoice->invoice_number)
            ->columnSpanFull()
            ->columns(12)
            ->schema([
                BAction::make([
                    Action::make('move_invoice' . $invoice->id)
                            ->label('Провести накладну')
                            ->icon('heroicon-o-check')
                            ->visible(fn () => $invoice->status === 'створено')
                            ->color('success')
                            ->action(fn () => InvoiceService::moveInvoiceToConducted($invoice)),
                        Action::make('cancel_invoice' . $invoice->id)
                            ->label('Скасувати проведення')
                            ->visible(fn () => $invoice->status === 'проведено')
                            ->icon('heroicon-o-x-mark')
                            ->color('danger')
                            ->action(fn () => InvoiceService::moveInvoiceToCreated($invoice)),
                        Action::make('printInvoice_' . $invoice->id)
                            ->label('Надрукувати накладну')
                            //->visible(fn () => $invoice->status === 'проведено')
                            ->icon('heroicon-o-printer')
                            ->color('info')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                        Action::make('add_discount' . $invoice->id)
                            ->label('Додати знижку')
                            //->visible(fn () => $invoice->status === 'створено')
                            ->icon('heroicon-o-percent-badge')
                            ->color('warning')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                        Action::make('add_discount' . $invoice->id)
                            ->label('Додати доставку')
                            //->visible(fn () => $invoice->status === 'створено')
                            ->icon('heroicon-o-truck')
                            ->color('warning')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                        Action::make('pay' . $invoice->id)
                            ->label('Оплатити')
                            //->visible(fn () => $invoice->status === 'проведено')
                            ->icon('heroicon-o-printer')
                            ->color('success')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),


                ])->columnSpanFull(),

                Fieldset::make('Інформація про накладну')
                       // ->description('Загально')
                        ->columns(2)
                        ->columnSpan(6, 12)
                        ->schema([
                        TextEntry::make('invoice_number')
                            ->label('Номер накладної')
                            ->default($invoice->invoice_number)
                            ->badge()
                            ->color('info'),
                        TextEntry::make('invoice_date')
                            ->label('Дата накладної')
                            ->default($invoice->invoice_date)
                            ->badge()
                            ->color('info'),
                        TextEntry::make('type')->label('Тип')->default($invoice->type)->badge()->color('primary'),
                        TextEntry::make('status')->label('Статус')->default($invoice->status)->badge()->color(fn () => match ($invoice->status) {
                            'створено' => 'info',
                            'проведено' => 'success',
                            'скасовано' => 'danger',
                        }),
                        TextEntry::make('invoice.notes')
                            ->label('Примітки'),
                        ]),
                Fieldset::make('Фінанси')
                        //->description('Базова інформація про накладну')
                        ->columns(2)
                        ->columnSpan(6, 12)
                        ->schema([
                            TextEntry::make('total')->label('Сума')->default($invoice->total)->badge()->color('success'),
                            TextEntry::make('paid')->label('Оплачено')->default($invoice->paid)->badge()->color('warning'),
                            TextEntry::make('due')->label('Заборгованість')->default($invoice->due)->badge()->color('danger'),
                            TextEntry::make('discount')->label('Знижка')->default($invoice->discount)->badge()->color('success'),
                            TextEntry::make('shipping')->label('Доставка')->default($invoice->shipping)->badge()->color('warning'),
                            TextEntry::make('payment_status')
                                ->badge()
                                ->label('Статус оплати')
                                ->default($invoice->payment_status)
                                ->color(fn () => match ($invoice->payment_status) {
                                'завдаток' => 'info',
                                'частково оплачено' => 'warning',
                                'оплачено' => 'success',
                                'не оплачено' => 'danger',
                                }),
                        ]),
                    //fn() => $invoice->type === 'продаж' ?  : null,
                    //isset($invoice->customer) ? self::buildCustumerInvoice($invoice) : array(),
                    self::buildCustumerInvoice($invoice),
                    self::buildSupplinerInvoice($invoice),
                    self::buildSectionInvoiceProductionItems($invoice),
                    self::buildSectionInvoiceMaterialItems($invoice),
                ]);
    }





    public static function buildCustumerInvoice($invoice){

        if(!isset($invoice->customer)){
            return Fieldset::make('Інформація про отримувача')
            //->description('Базова інформація про накладну')
            ->columns(2)
            ->hidden(true)
            ->columnSpanFull()
            //->columnSpan(6, 12)
            ->schema([]);
        }
        return  Fieldset::make('Інформація про отримувача')
                        //->description('Базова інформація про накладну')
                        ->columns(2)
                        ->hidden(!isset($invoice->customer))
                        ->columnSpanFull()
                        //->columnSpan(6, 12)
                        ->schema([
                            TextEntry::make('customer.name')
                                ->label('Замовник')
                                ->default($invoice->customer->name)
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('customer.phone')
                                ->label('Телефон замовника')
                                ->default($invoice->customer->phone)
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('customer.email')
                                ->label('Email замовника')
                                ->default($invoice->customer->email)
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('customer.address')
                                ->label('Адреса замовника')
                                ->default($invoice->customer->address)
                                ->badge()
                                ->color('primary'),
                            // TextEntry::make('user.name')
                            //     ->label('Менеджер')
                            //     ->default($invoice->user->name)
                            //     ->badge()
                            //     ->color('primary'),
                            // TextEntry::make('warehouse.name')
                            //     ->label('Склад')
                            //     //->default($invoice->warehouse->name)
                            //     ->badge()
                            //     ->color('primary'),
                        ]);
    }



    public static function buildSupplinerInvoice($invoice){

        if(!isset($invoice->supplier)){
            return Fieldset::make('Інформація про постачальника')
            //->description('Базова інформація про накладну')
            ->columns(2)
            ->hidden(true)
            ->columnSpanFull()
            //->columnSpan(6, 12)
            ->schema([]);
        }
        return  Fieldset::make('Інформація про постачальника')
                        //->description('Базова інформація про накладну')
                        ->columns(2)
                        ->hidden(!isset($invoice->supplier))
                        ->columnSpanFull()
                        //->columnSpan(6, 12)
                        ->schema([
                            TextEntry::make('customer.name')
                                ->label('Замовник')
                                ->default($invoice->supplier->name)
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('customer.phone')
                                ->label('Телефон замовника')
                                ->default($invoice->supplier->phone)
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('customer.email')
                                ->label('Email замовника')
                                ->default($invoice->supplier->email)
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('customer.address')
                                ->label('Адреса замовника')
                                ->default($invoice->supplier->address)
                                ->badge()
                                ->color('primary'),
                            // TextEntry::make('user.name')
                            //     ->label('Менеджер')
                            //     ->default($invoice->user->name)
                            //     ->badge()
                            //     ->color('primary'),
                            // TextEntry::make('warehouse.name')
                            //     ->label('Склад')
                            //     //->default($invoice->warehouse->name)
                            //     ->badge()
                            //     ->color('primary'),
                        ]);
    }


    public static function buildSectionInvoiceProductionItems(Invoice $invoice)
    {

        $data  = [];
        foreach ($invoice->invoiceProductionItems as $item) {
            $data[] = Fieldset::make('Замовлення '. $item->production->id)
            ->columns(2)
            ->columnSpanFull()
            ->schema([
                BAction::make([
                    Action::make('pay' . $invoice->id)
                        ->label('Змінити')
                        //->visible(fn () => $invoice->status === 'проведено')
                        ->icon('heroicon-o-document-plus')
                        ->color('info')
                        ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                    Action::make('pay' . $invoice->id)
                        ->label('Оновити')
                        //->visible(fn () => $invoice->status === 'проведено')
                        ->icon('heroicon-o-document-plus')
                        ->color('warning')
                        ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                    Action::make('pay' . $invoice->id)
                        ->label('Видалити')
                        //->visible(fn () => $invoice->status === 'проведено')
                        ->icon('heroicon-o-document-plus')
                        ->color('danger')
                        ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                ]),
                TextEntry::make('production.name')
                    ->label('Назва замовлення')
                    ->default($item->production->name)
                    ->badge()
                    ->color('primary'),
                TextEntry::make('price')
                    ->label('Вартість замовлення')
                    ->default($invoice->price)
                    ->badge()
                    ->color('primary'),
                TextEntry::make('quantity')
                    ->label('Кількість')
                    ->default($item->quantity)
                    ->badge()
                    ->color('primary'),
                TextEntry::make('total')
                    ->label('Сума')
                    ->default($item->total)
                    ->badge()
                    ->color('primary'),
            ]);
        }


        return Section::make('Замовлення')
            ->description('Замовлення в накладній')
            ->columns(2)
            ->columnSpanFull()
            ->headerActions([
                Action::make('pay' . $invoice->id)
                    ->label('Додати замовлення')
                    //->visible(fn () => $invoice->status === 'проведено')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                ])
                ->schema(
                    $data
               );
    }


    public static function buildSectionInvoiceMaterialItems(Invoice $invoice)
    {

        $data  = [];
        foreach ($invoice->invoiceItems as $item) {
            $data[] = Fieldset::make('Матеріал '. $item->material->name)
            ->columns(2)
            //->columnSpan(6, 12)
            ->columnSpanFull()
            ->schema([
                TextEntry::make('material.name')
                    ->label('Назва матеріалу')
                    ->default($item->material->name)
                    ->badge()
                    ->color('primary'),
                TextEntry::make('price')
                    ->label('Вартість матеріалу')
                    ->default($invoice->price)
                    ->badge()
                    ->color('primary'),
                TextEntry::make('quantity')
                    ->label('Кількість')
                    ->default($item->quantity)
                    ->badge()
                    ->color('primary'),
                TextEntry::make('total')
                    ->label('Сума')
                    ->default($item->total)
                    ->badge()
                    ->color('primary'),
            ]);
        }


        return Section::make('Матеріали')
            ->description('Матеріали в накладній')
            ->columns(2)
            ->columnSpanFull()
            ->headerActions([
                Action::make('pay' . $invoice->id)
                    ->label('Додати матеріал')
                    //->visible(fn () => $invoice->status === 'проведено')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                ])
                ->schema(
                    $data
               );
    }
}

