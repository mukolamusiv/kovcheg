<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Швейна Майстерня Ковчег</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin: 0;
        }
        header p {
            font-size: 1rem;
            margin: 5px 0 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .about {
            text-align: center;
            margin-bottom: 40px;
        }
        .about h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .about p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #555;
        }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .gallery img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            margin-top: 40px;
        }
        footer p {
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Швейна Майстерня Ковчег</h1>
        <p>Елегантність у кожному стіжку</p>
    </header>
    <div class="container">
        <section class="about">
            <h2>Про Нас</h2>
            <p>Ласкаво просимо до Швейної Майстерні Ковчег, де майстерність поєднується з творчістю. Ми спеціалізуємося на створенні індивідуального одягу, ремонті та унікальних дизайнах, які відповідають вашому стилю. Дозвольте нам втілити вашу ідею в життя з точністю та елегантністю.</p>
        </section>
        <section class="gallery">
            <img src="https://via.placeholder.com/600x400?text=Елегантна+Сукня" alt="Елегантна Сукня">
            <img src="https://via.placeholder.com/600x400?text=Процес+Пошиття" alt="Процес Пошиття">
            <img src="https://via.placeholder.com/600x400?text=Індивідуальні+Дизайни" alt="Індивідуальні Дизайни">
            <img src="https://via.placeholder.com/600x400?text=Готові+Вироби" alt="Готові Вироби">
        </section>
    </div>
    <footer>
        <p>&copy; {{ date('Y') }} Швейна Майстерня Ковчег. Усі права захищено.</p>
    </footer>
</body>
</html>
