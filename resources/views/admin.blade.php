<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Beauté Zone — Админка</title>
    @vite(['resources/css/admin.less', 'resources/js/admin/app.js'])
    <script>
        window.__ADMIN_USER__ = @json([
            'name' => auth()->user()?->name,
            'email' => auth()->user()?->email,
        ]);
    </script>
</head>
<body>
<div id="admin-app"></div>
</body>
</html>
