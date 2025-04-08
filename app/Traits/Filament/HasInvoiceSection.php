<?php

namespace App\Traits\Filament;

//use Filament\Forms\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
//use Filament\Forms\Components\RepeatableEntry;
//use Filament\Tables\Actions\Action;
//use Filament\Actions;
use Filament\Infolists\Components\Actions; //as BAction;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Infolists\Components\Actions\Action as ActionsAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Support\Enums\Alignment;

trait HasInvoiceSection
{
    public function getInvoiceSection(?Invoice $invoice = null)
    {
        return Section::make('Накладна та фінанси')
            //->icon('heroicon-m-shopping-bag')
            //->compact()
            ->visible(fn () => $invoice !== null)
           // ->getRecord($invoice)
            //->columns(5)
            ->headerActions([
                // ActionsAction::make('reconcileFinances_' . $invoice->id)
                //     ->label('Перерахувати фінанси')
                //     ->icon('heroicon-s-variable')
                //     ->color('danger')
                //     ->action(fn () => $invoice->reconcileFinances()),
                // ActionsAction::make('customer_pay_' . $invoice->id)
                //     ->label('Внести оплату')
                //     ->icon('heroicon-s-currency-euro')
                //     ->color('success')
                //     ->requiresConfirmation()
                //     ->form([
                //         Select::make('account_id')
                //             ->label('Гроші на рахунок')
                //             ->options(Account::query()->whereNull('owner_id')->pluck('name', 'id'))
                //             ->required(),

                //         TextInput::make('amount')
                //             ->numeric()
                //             ->maxValue($invoice->due ?? 100)
                //             ->default($invoice->due ?? 100)
                //             ->label('Сума'),

                //         TextInput::make('description'),
                //     ])
                //     ->action(fn ($data) => Transaction::customer_pay_transaction($data, $invoice->customer, $invoice)),
            ])
            ->collapsed(false)
            ->columnSpanFull()
            ->columns(12)
            ->schema([
                Section::make('Інформація про накладну')
                    ->description('Загально')
                    ->columns(2)
                //->backgroundColor('gray')
                //->collapsed(true)
                //->columnSpan(min(6, 12))
                //->columnSpanFull(5)
                ->columnSpan(6, 12)
                ->footerActions([
                    ActionsAction::make('move_invoice' . $invoice->id)
                        ->label('Провести накладну')
                        ->icon('heroicon-o-check')
                        ->visible(fn () => $invoice->status === 'створено')
                        ->color('success')
                        ->action(fn () => $invoice->moveInvoice()),
                    ActionsAction::make('cancel_invoice' . $invoice->id)
                        ->label('Скасувати проведення')
                        ->visible(fn () => $invoice->status === 'проведено')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn () => $invoice->cancelInvoice()),
                    ActionsAction::make('printInvoice_' . $invoice->id)
                        ->label('Надрукувати накладну')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                ])
                ->footerActionsAlignment(Alignment::Center)

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

                Section::make('Фінанси')
                    ->description('Базова інформація про накладну')
                    ->columns(2)
                    ->columnSpan(6, 12)
                   // ->columnSpan(min(6, 12))
                  //  ->columnSpanFull(5)
                    ->footerActions([
                        ActionsAction::make('add_discount' . $invoice->id)
                            ->label('Додати знижку')
                            ->icon('heroicon-o-printer')
                            ->color('warning')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                        ActionsAction::make('pay' . $invoice->id)
                            ->label('Оплатити')
                            ->icon('heroicon-o-printer')
                            ->color('success')
                            ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                    ])
                    ->footerActionsAlignment(Alignment::Center)

                    ->schema([
                        TextEntry::make('total')->label('Сума')->default($invoice->total)->badge()->color('success'),
                        TextEntry::make('paid')->label('Оплачено')->default($invoice->paid)->badge()->color('warning'),
                        TextEntry::make('due')->label('Заборгованість')->default($invoice->due)->badge()->color('danger'),
                        TextEntry::make('discount')->label('Знижка')->default($invoice->discount)->badge()->color('success'),
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
    //                 public function invoiceProductionItems()
    // {
    //     return $this->hasMany(InvoiceProductionItem::class);
    // }
                    RepeatableEntry::make('produc')
                        ->default(fn () => $invoice->invoiceProductionItems ?? [])
                        ->label('Позиції виробництва')
                        //->label('Позиції замовлення')
                        ->columnSpan(12)
                        ->columns(4)
                        ->schema([
                            TextEntry::make('quantity')->label('Кількість'),
                            TextEntry::make('price')->label('Ціна'),
                            TextEntry::make('total')->label('Сума'),
                            TextEntry::make('production.name')->label('Виробництво'),
                            TextEntry::make('production.description')->label('Опис'),
                            TextEntry::make('production.status')->label('Статус'),
                            TextEntry::make('production.type')->label('Тип'),
                            // TextEntry::make('description'),
                            // TextEntry::make('debet.amount'),
                            // TextEntry::make('transaction_date'),
                        ]),
                    RepeatableEntry::make('invoiceItems')
                        //->default(fn () => $invoice->invoiceItems ?? [])
                        // ->viewData(
                        //     ['invoiceItems' => $invoice->invoiceItems],
                        // )
                        ->label('Позиції накладної')
                        ->columns(4)
                        //->collapsed(true)
                        ->columnSpanFull()
                        //->columnSpan()
                        //->description('Матеріали накладної')
                        ->schema([
                            TextEntry::make('quantity')->label('Кількість'),
                            TextEntry::make('price')->label('Ціна'),
                            TextEntry::make('total')->label('Сума'),
                        ]),

                        Section::make('Транзакції')
                        ->description('Усі транзакції накладної')
                        ->collapsed(true)
                        ->columns(2)
                        ->columnSpanFull()
                        ->footerActions([
                            ActionsAction::make('add_discount' . $invoice->id)
                                ->label('Додати знижку')
                                ->icon('heroicon-o-printer')
                                ->color('warning')
                                ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                            ActionsAction::make('pay' . $invoice->id)
                                ->label('Оплатити')
                                ->icon('heroicon-o-printer')
                                ->color('success')
                                ->url(fn () => route('invoice.pdf', ['invoice' => $invoice->id])),
                        ])

                        ->schema([
                            RepeatableEntry::make('transactions')
                                ->label('Транзакції')
                                ->columnSpan(12)
                                ->columns(4)
                                ->schema([
                                    TextEntry::make('reference_number'),
                                    TextEntry::make('description'),
                                    TextEntry::make('debet.amount'),
                                    TextEntry::make('transaction_date'),
                                ]),
                        ]),

            ]);
    }
}
