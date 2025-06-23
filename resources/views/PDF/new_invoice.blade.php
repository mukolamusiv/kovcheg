<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Накладна №{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            margin: 40px;
        }

        h1, h2, h3 {
            margin: 5px 0;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        td, th {
            border: 1px solid #000;
            padding: 6px;
        }

        .no-border {
            border: none;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature div {
            width: 45%;
        }

        .small {
            font-size: 12px;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <table class="no-border">
        <tr class="no-border">
            <td class="no-border">
                {{-- <div><strong>Організація:</strong> ________________________________________</div> --}}
                <div><strong>Постачальник:</strong> {{ $fop->name ?? '—' }}</div>
                <div><strong>ІПН:</strong> {{ $fop->ipn ?? '—' }}</div>
                <div><strong>Адреса:</strong> {{ $fop->address ?? 'м. Львів, вул. Грінченка 12В' }}</div>
                <div><strong>Р/рахунок:</strong> {{ $fop->iban ?? 'UA243052990000026008011027721' }}</div>
                <div><strong>в {{ $fop->bank_name ?? 'АТ КБ «ПРИВАТБАНК»' }}</strong></div>
                <div><strong>МФО {{ $fop->bank_code ?? 'АТ КБ «ПРИВАТБАНК»' }}</strong></div>
                <div><strong>Тел.:</strong> {{ $fop->phone ?? '+380964668317' }}</div>
            </td>
            <td class="no-border text-right">
                <h2>НАКЛАДНА</h2>
                <div><strong>№</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>від</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d.m.Y') }}</div>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td><strong>Одержувач:</strong> {{ $invoice->customer->name ?? '—' }}</td>
        </tr>
        {{-- <tr>
            <td>
                р/р {{ $invoice->customer->bank_account ?? 'UA083052990000026006031003578' }}<br>
                у банку КБ «ПРИВАТБАНК», м. Дніпропетровськ<br>
                код за ДРФО {{ $invoice->customer->ipn ?? '—' }}, ІПН {{ $invoice->customer->ipn ?? '—' }}
            </td>
        </tr> --}}
    </table>

    <table>
        <thead>
            <tr class="text-center">
                <th>№</th>
                <th>Найменування товару</th>
                <th>Одиниця виміру</th>
                <th>Кількість</th>
                <th>Ціна, грн</th>
                <th>Сума, грн</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($invoice->invoiceItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->material->name ?? '—' }}</td>
                    <td class="text-center">шт.</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @php $total += $item->total; @endphp
            @endforeach
            @foreach($invoice->invoiceProductionItems as $data => $item)
                <tr>
                    <td class="text-center">{{ $data + 1 }}</td>
                    <td>{{ $item->production->name ?? '—' }}</td>
                    <td class="text-center">шт.</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @php $total += $item->total; @endphp
            @endforeach
        </tbody>
    </table>

    <table>
        <tr>
            <td class="no-border" colspan="6">
                <strong>Загальна сума, що підлягає сплаті:</strong>
                {{ $total_in_words ?? '—' }}
            </td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><strong>Разом:</strong></td>
            <td class="text-right">{{ number_format($total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><strong>Без ПДВ</strong></td>
            <td class="text-right">—</td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><strong>ПДВ 20%</strong></td>
            <td class="text-right">—</td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><strong>Всього з ПДВ:</strong></td>
            <td class="text-right">{{ number_format($total, 2) }}</td>
        </tr>
    </table>

    <div class="signature">
        <div>
            <strong>Відвантажив(ла):</strong> _______________________
        </div>
        <br>
        <br>
        <br>
        <div class="text-right">
            <strong>Отримав(ла):</strong> _______________________
        </div>
    </div>
</body>
</html>
