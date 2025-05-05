<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        /* body { font-family: DejaVu Sans, sans-serif; } */
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .details, .items, .transactions {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details td, .items th, .items td, .transactions th, .transactions td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .items th, .transactions th {
            background-color: #f2f2f2;
        }
        .totals {
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Накладна</h1>
            <p>Номер накладної: {{ $invoice->invoice_number }}</p>
            <p>Дата: {{ $invoice->invoice_date }}</p>
        </div>

        <table class="details">
            <tr>
            <td><strong>Клієнт:</strong> {{ $invoice->customer->name ?? 'Н/Д' }}</td>
            <td><strong>Постачальник:</strong> {{ $invoice->supplier->name ?? 'Н/Д' }}</td>
            </tr>
            <tr>
            <td><strong>Статус:</strong> {{ $invoice->status }}</td>
            <td><strong>Статус оплати:</strong> {{ $invoice->payment_status }}</td>
            </tr>
            <tr>
            <td colspan="2">
                <strong>Деталі клієнта:</strong><br>
                <strong>Телефон:</strong> {{ $invoice->customer->phone ?? 'Н/Д' }}<br>
                <strong>Email:</strong> {{ $invoice->customer->email ?? 'Н/Д' }}<br>
                <strong>Адреса:</strong> {{ $invoice->customer->address ?? 'Н/Д' }}
            </td>
            </tr>
        </table>

        <h3>Позиції</h3>
        <table class="items">
            <thead>
            <tr>
                <th>#</th>
                <th>Опис</th>
                <th>Кількість</th>
                <th>Ціна за одиницю</th>
                <th>Сума</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($invoice->invoiceItems as $index => $item)
                <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->material->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
            @foreach ($invoice->invoiceProductionItems as $index => $productionItem)
                <tr>
                <td>{{ $loop->iteration }}</td>
                <td>Виробництво: {{ $productionItem->production->name ?? 'Н/Д' }}</td>
                <td>{{ $productionItem->quantity }}</td>
                <td>{{ number_format($productionItem->price, 2) }}</td>
                <td>{{ number_format($productionItem->total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <h3>Транзакції</h3>
        <table class="transactions">
            <thead>
            <tr>
                <th>#</th>
                <th>Дата</th>
                <th>Тип</th>
                <th>Сума</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($invoice->transactions as $index => $transaction)
                <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction->transaction_date }}</td>
                <td>{{ $transaction->entries->first()->entry_type ?? 'Н/Д' }}</td>
                <td>{{ number_format($transaction->entries->first()->amount ?? 0, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="totals">
            <p><strong>Проміжний підсумок:</strong> {{ number_format($invoice->total, 2) }}</p>
            <p><strong>Оплачено:</strong> {{ number_format($invoice->paid, 2) }}</p>
            <p><strong>Заборгованість:</strong> {{ number_format($invoice->due, 2) }}</p>
        </div>

        <div class="footer">
            <p>Дякуємо за співпрацю!</p>
        </div>
    </div>
</body>
</html>
