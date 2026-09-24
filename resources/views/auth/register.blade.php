<x-guest-layout>
    <!-- Logo (login-imglogo) -->
    <div class="mb-10 text-center font-inter">
        <div class="flex items-center justify-center mb-5">
            <div class="w-[60px] h-[60px] rounded-lg inline-flex items-center justify-center drop-shadow-md align-middle bg-slate-900">
                <svg viewBox="0 0 16 16" fill="none" class="w-8 h-8">
                    <rect x="1" y="7" width="14" height="2" fill="#f59e0b" rx="1" />
                    <rect x="3" y="4" width="2" height="8" fill="#f59e0b" rx="1" />
                    <rect x="11" y="4" width="2" height="8" fill="#f59e0b" rx="1" />
                </svg>
            </div>
            <span class="font-bold text-slate-800 tracking-tight align-middle text-[2.5rem] ml-4">Rail Brazil</span>
        </div>
        <p class="font-medium text-slate-500 text-sm">Crie sua conta para simular rotas</p>
    </div>

    <!-- Register Form -->
    <form action="{{ route('register') }}" method="POST" class="flex flex-col font-inter">
        @csrf

        <!-- Campo Nome -->
        <div class="mb-4">
            <label for="name" class="block mb-1 text-sm font-semibold text-slate-600">Nome Completo</label>
            <div class="flex bg-gray-100 rounded-md overflow-hidden">
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Seu nome" required autofocus autocomplete="name"
                       class="flex-1 border-none bg-transparent p-3 outline-none focus:ring-0 text-slate-800">
                <span class="bg-slate-900 text-white w-[45px] flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </span>
            </div>
            @error('name')
                <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Campo Email -->
        <div class="mb-4">
            <label for="email" class="block mb-1 text-sm font-semibold text-slate-600">Endereço Email</label>
            <div class="flex bg-gray-100 rounded-md overflow-hidden">
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nome@email.com" required autocomplete="username"
                       class="flex-1 border-none bg-transparent p-3 outline-none focus:ring-0 text-slate-800">
                <span class="bg-slate-900 text-white w-[45px] flex items-center justify-center">
                    <i class="fas fa-envelope"></i>
                </span>
            </div>
            @error('email')
                <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Campo Senha -->
        <div class="mb-4">
            <label for="password" class="block mb-1 text-sm font-semibold text-slate-600">Senha</label>
            <div class="flex bg-gray-100 rounded-md overflow-hidden">
                <input type="password" id="password" name="password" placeholder="Sua Senha secreta" required autocomplete="new-password"
                       class="flex-1 border-none bg-transparent p-3 outline-none focus:ring-0 text-slate-800">
                <span class="bg-slate-900 text-white w-[45px] flex items-center justify-center">
                    <i class="fas fa-lock"></i>
                </span>
            </div>
            @error('password')
                <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Campo Confirmar Senha -->
        <div class="mb-4">
            <label for="password_confirmation" class="block mb-1 text-sm font-semibold text-slate-600">Confirmar Senha</label>
            <div class="flex bg-gray-100 rounded-md overflow-hidden">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repita sua senha" required autocomplete="new-password"
                       class="flex-1 border-none bg-transparent p-3 outline-none focus:ring-0 text-slate-800">
                <span class="bg-slate-900 text-white w-[45px] flex items-center justify-center">
                    <i class="fas fa-check-circle"></i>
                </span>
            </div>
            @error('password_confirmation')
                <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Botão Registar -->
        <button type="submit" class="bg-slate-900 font-bold text-white p-3 rounded-md w-full mt-4 shadow-[0_4px_10px_rgba(15,23,42,0.3)] hover:bg-slate-800 transition-colors">
            Criar Conta
        </button>

        <div class="text-center mt-6 text-sm text-slate-500">
            <span>Já possui uma conta?</span> 
            <a href="{{ route('login') }}" class="text-amber-500 font-bold no-underline hover:underline">Faça login</a>
        </div>
    </form>
</x-guest-layout>