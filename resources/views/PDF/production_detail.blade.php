<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Details</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .header, .section {
            margin-bottom: 20px;
        }
        .header h1, .section h2 {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table th {
            background-color: #f2f2f2;
        }
        .notes {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Деталі виробництва</h1>
            <p><strong>Назва:</strong> {{ $production->name }}</p>
            <p><strong>Опис:</strong> {{ $production->description }}</p>
            <p><strong>Статус:</strong> {{ $production->status }}</p>
            <p><strong>Тип:</strong> {{ $production->type }}</p>
            <p><strong>Клієнт:</strong> {{ $production->customer->name ?? 'Н/Д' }}</p>
            <p><strong>Кількість:</strong> {{ $production->quantity }}</p>
            <p><strong>Дата виготовлення:</strong> {{ $production->production_date ?? 'Н/Д' }}</p>
        </div>

        <div class="section">
            <h2>Розміри</h2>
            <table>
                <tr>
                    <th>Горловина</th>
                    <th>Переділ</th>
                    <th>Зад</th>
                    <th>Стегна</th>
                    <th>Довжина</th>
                    <th>Рукав</th>
                    <th>Плече</th>
                    <th>Коментар</th>
                </tr>
                <tr>
                    <td>{{ $production->productionSizes->throat ?? 'Н/Д' }}</td>
                    <td>{{ $production->productionSizes->redistribution ?? 'Н/Д' }}</td>
                    <td>{{ $production->productionSizes->behind ?? 'Н/Д' }}</td>
                    <td>{{ $production->productionSizes->hips ?? 'Н/Д' }}</td>
                    <td>{{ $production->productionSizes->length ?? 'Н/Д' }}</td>
                    <td>{{ $production->productionSizes->sleeve ?? 'Н/Д' }}</td>
                    <td>{{ $production->productionSizes->shoulder ?? 'Н/Д' }}</td>
                    <td>{{ $production->productionSizes->comment ?? 'Н/Д' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Матеріали</h2>
            <table>
                <thead>
                    <tr>
                        <th>Матеріал</th>
                        <th>Кількість</th>
                        <th>Одиниця</th>
                        <th>Фактична кількість</th>
                        <th>Виконано</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($production->productionMaterials as $material)
                        <tr>
                            <td>{{ $material->material->name }}</td>
                            <td>{{ $material->quantity }}</td>
                            <td>{{ $material->material->unit }}</td>
                            <td></td>
                            <td><input type="checkbox"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Етапи виробництва</h2>
            <table>
                <thead>
                    <tr>
                        <th>Етап</th>
                        <th>Опис</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Виконано</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($production->productionStages as $stage)
                        <tr>
                            <td>{{ $stage->name }}</td>
                            <td>{{ $stage->description }}</td>
                            <td>{{ $stage->status }}</td>
                            <td>{{ $stage->date ?? 'Н/Д' }}</td>
                            <td><input type="checkbox"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="notes">
            <p><strong>Примітки:</strong></p>
            <p>__________________________________________________________</p>
            <p>__________________________________________________________</p>
            <p>__________________________________________________________</p>
        </div>
    </div>
</body>
</html>
