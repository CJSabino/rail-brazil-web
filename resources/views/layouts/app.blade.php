<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rail Brazil - @yield('titulo', 'Simulador')</title>

    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('css')
</head>

<body class="min-h-screen bg-[#f8f7f4] text-slate-900 font-sans flex flex-col overflow-x-hidden">

    <!-- HEADER -->
    <header id="main-header"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-8">

            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded bg-slate-900 flex items-center justify-center">
                    <svg viewBox="0 0 16 16" fill="none" class="w-4 h-4">
                        <rect x="1" y="7" width="14" height="2" fill="#f59e0b" rx="1" />
                        <rect x="3" y="4" width="2" height="8" fill="#f59e0b" rx="1" />
                        <rect x="11" y="4" width="2" height="8" fill="#f59e0b" rx="1" />
                    </svg>
                </div>
                <a href="{{ route('home') }}">
                    <span class="font-bold text-slate-900 tracking-tight text-lg leading-none block">Rail Brazil</span>
                    <span
                        class="text-[10px] font-mono text-slate-400 leading-none block tracking-widest uppercase">Simulador
                        Logístico</span>
                </a>
            </div>

            <!-- Navegação Desktop -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ route('home') }}"
                    class="text-sm font-medium text-slate-600 hover:text-amber-600 transition-colors">Início</a>
                <a href="{{ route('simulador') }}"
                    class="text-sm font-medium text-slate-600 hover:text-amber-600 transition-colors">Simulação</a>
                <a href="{{ route('empresa') }}"
                    class="text-sm font-medium text-slate-600 hover:text-amber-600 transition-colors">Informações</a>
            </nav>

            <!-- Ações / Autenticação -->
            <div class="hidden lg:flex items-center gap-4">
                @auth
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-slate-700">
                            <i data-lucide="user" class="w-4 h-4 text-amber-500"></i>
                            <span class="text-sm font-bold">Olá, {{ Auth::user()->name }}</span>
                        </div>

                        <!-- BOTÃO DE PERFIL -->
                        <a href="{{ route('profile.edit') }}"
                            class="text-sm font-medium text-slate-500 hover:text-amber-600 transition-colors">Meu Perfil</a>

                        <a href="{{ route('dashboard') }}"
                            class="text-sm font-medium text-slate-500 hover:text-amber-600 transition-colors">Dashboard</a>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-sm font-medium text-red-500 hover:text-red-700 transition-colors">Sair</button>
                        </form>
                    </div>
                @else
                    <!-- Se for Visitante -->
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}"
                            class="text-sm font-bold text-slate-600 hover:text-amber-600 transition-colors">Entrar</a>
                        <a href="{{ route('register') }}"
                            class="bg-amber-500 hover:bg-amber-400 text-slate-900 px-5 py-2 rounded-lg text-sm font-bold transition-colors">
                            Criar Conta
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Botão Mobile -->
            <button class="md:hidden text-slate-900">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
    </header>

    <!-- CONTEÚDO  -->
    <main class="flex-grow pt-16">
        <!-- O Breeze vai renderizar as telas de Login/Register aqui dentro -->
        {{ $slot ?? '' }}

        <!-- vai renderizar a Home e o Simulador aqui dentro -->
        @yield('conteudo')
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">

                <!-- Endereço -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 mb-4 tracking-tight">Nosso Endereço</h3>
                    <address class="not-italic text-sm text-slate-500 space-y-2 leading-relaxed">
                        <p>Ourinhos - SP<br></p>
                        <p class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            <a href="tel:+5514999999999" class="hover:text-amber-600 transition-colors">(14)
                                99999-9999</a>
                        </p>
                        <p class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            <a href="mailto:contato@railbrazil.com.br"
                                class="hover:text-amber-600 transition-colors">contato@railbrazil.com.br</a>
                        </p>
                    </address>
                </div>

                <!-- Navegação Footer -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 mb-4 tracking-tight">Navegação</h3>
                    <nav>
                        <ul class="space-y-2 text-sm text-slate-500">
                            <li><a href="{{ route('home') }}" class="hover:text-amber-600 transition-colors">Início</a>
                            </li>
                            <li><a href="{{ route('simulador') }}"
                                    class="hover:text-amber-600 transition-colors">Simulador de Rotas</a></li>
                            <li><a href="{{ route('empresa') }}" class="hover:text-amber-600 transition-colors">Sobre o
                                    Projeto</a></li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Rodapé Inferior -->
            <div class="pt-8 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
                <small class="text-xs text-slate-400 font-mono">© 2026 Rail Brazil — Todos os direitos
                    reservados</small>
                <nav aria-label="Links de política">
                    <ul class="flex items-center gap-6 text-xs text-slate-400 font-mono">
                        <li><a href="#" class="hover:text-slate-600 transition-colors">Política de privacidade</a></li>
                        <li><a href="#" class="hover:text-slate-600 transition-colors">Termos de uso</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </footer>

    <!-- Scripts Base -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    <!-- Scripts Específicos -->
    @yield('scripts')
</body>

</html>