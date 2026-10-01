<!doctype html>
<html lang="ka">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name', 'საბავშვო ბაღების მართვის სისტემა') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/public-system.css') }}?v={{ file_exists(public_path('css/public-system.css')) ? filemtime(public_path('css/public-system.css')) : '1' }}">
</head>
<body class="service-error-page">
    <main class="service-error-card">
        <span class="service-error-code">@yield('code')</span>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="service-error-actions"><a href="{{ url('/') }}">მთავარ გვერდზე დაბრუნება</a></div>
    </main>
</body>
</html>
