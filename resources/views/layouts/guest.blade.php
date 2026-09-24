<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rail Brazil - Autenticação</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="m-0 font-sans bg-gray-100">
    <!-- Lado Login -->
    <div class="flex h-screen">
        <section
            class="w-full lg:flex-[0_0_35%] xl:flex-[0_0_31%] flex justify-center items-center bg-white relative z-20 shadow-[25px_0_60px_rgba(0,0,0,0.4)] px-6">
            <div class="w-full max-w-[390px]">
                {{ $slot }}
            </div>
        </section>

        <!-- Lado Illustration -->
        <section class="hidden lg:flex flex-1 bg-slate-50 justify-center items-center p-0 relative z-10">
            <img src="https://images.pexels.com/photos/1598075/pexels-photo-1598075.jpeg" alt="Trem de Carga"
                class="w-full h-screen block object-cover object-left">
        </section>

    </div>
</body>

</html>