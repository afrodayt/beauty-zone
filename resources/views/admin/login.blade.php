<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход в админку</title>
    @include('partials.favicon')
    @vite('resources/css/app.less')
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 mb-4">Вход в админку</h1>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input id="password" name="password" type="password" class="form-control" required>
                        </div>

                        <div class="form-check mb-4">
                            <input id="remember" name="remember" type="checkbox" class="form-check-input" value="1">
                            <label for="remember" class="form-check-label">Запомнить меня</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Войти</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
