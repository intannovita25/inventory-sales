<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container text-center py-5">
    <div class="display-1 fw-bold text-primary">@yield('code')</div>
    <h2 class="mb-3">@yield('title')</h2>
    <p class="text-muted mb-4">@yield('message')</p>
    <a href="{{ url('/dashboard') }}" class="btn btn-primary">Ke Dashboard</a>
    <a href="javascript:history.back()" class="btn btn-outline-secondary">Kembali</a>
</div>
</body>
</html>
