<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPEMILU — Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#070707] text-gray-100 min-h-screen flex items-center justify-center relative">
    <div class="absolute inset-0 pointer-events-none"
         style="background-image: linear-gradient(rgba(230,57,70,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(230,57,70,0.04) 1px, transparent 1px); background-size: 60px 60px;"></div>
    @yield('content')
</body>
</html>