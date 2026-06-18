<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Iniciar sesión — Ovnicom Analytics</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-8px); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: .15; }
            50%       { opacity: .30; }
        }
        .animate-float    { animation: float 6s ease-in-out infinite; }
        .animate-fade-up  { animation: fadeUp .5s ease both; }
        .delay-100 { animation-delay: .1s; }
        .delay-200 { animation-delay: .2s; }
        .delay-300 { animation-delay: .3s; }
        .delay-400 { animation-delay: .4s; }
        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(80px);
            animation: pulse-slow 8s ease-in-out infinite;
        }
    </style>
</head>
<body style="background-color:#060912;" class="min-h-screen flex overflow-hidden">

    {{-- ── Panel izquierdo (branding) ──────────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative flex-col justify-between p-12 overflow-hidden">

        {{-- Orbes de fondo --}}
        <div class="orb w-96 h-96 top-[-80px] left-[-80px]"  style="background:rgba(249,115,22,.35);"></div>
        <div class="orb w-72 h-72 bottom-24  right-8"         style="background:rgba(99,102,241,.25); animation-delay:3s;"></div>
        <div class="orb w-56 h-56 top-1/2    left-1/3"        style="background:rgba(249,115,22,.12); animation-delay:5s;"></div>

        {{-- Grid punteado sutil --}}
        <div class="absolute inset-0" style="background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);background-size:28px 28px;"></div>

        {{-- Logo --}}
        <div class="relative z-10 animate-fade-up">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-tight">Ovnicom Analytics</span>
            </div>
        </div>

        {{-- Hero text --}}
        <div class="relative z-10 space-y-8">
            <div class="animate-fade-up delay-100">
                <h2 class="text-4xl xl:text-5xl font-bold text-white leading-tight">
                    La plataforma<br/>
                    <span class="text-orange-400">central de operaciones</span><br/>
                    de Ovnicom.
                </h2>
                <p class="text-gray-400 text-lg mt-4 leading-relaxed max-w-md">
                    Reportes, inventario, ventas, redes y encuestas — todo en un solo lugar.
                </p>
            </div>

            {{-- Feature list --}}
            <div class="animate-fade-up delay-200 grid grid-cols-2 gap-3 max-w-lg">
                @foreach([
                    ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Reportes MSP'],
                    ['icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2', 'label' => 'Dashboard Ventas'],
                    ['icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01', 'label' => 'GLPI Inventario'],
                    ['icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0', 'label' => 'Meraki Redes'],
                    ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'label' => 'META 2 en Vivo'],
                    ['icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', 'label' => 'API REST'],
                ] as $f)
                <div class="flex items-center gap-2.5 bg-white/5 border border-white/8 rounded-xl px-3.5 py-2.5 backdrop-blur-sm">
                    <div class="w-6 h-6 bg-orange-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-300">{{ $f['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer branding --}}
        <div class="relative z-10 animate-fade-up delay-300">
            <p class="text-xs text-gray-600">© {{ date('Y') }} Ovnicom Communications · Sistema interno</p>
        </div>
    </div>

    {{-- ── Panel derecho (formulario) ──────────────────────────────────────────── --}}
    <div class="w-full lg:w-1/2 xl:w-2/5 flex items-center justify-center p-6 sm:p-10 relative">

        {{-- Línea separadora vertical sutil --}}
        <div class="hidden lg:block absolute left-0 inset-y-0 w-px bg-white/5"></div>

        <div class="w-full max-w-sm animate-fade-up delay-100">

            {{-- Logo mobile --}}
            <div class="flex lg:hidden items-center justify-center gap-3 mb-8">
                <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-base">Ovnicom Analytics</span>
            </div>

            {{-- Encabezado del form --}}
            <div class="mb-7">
                <h1 class="text-2xl font-bold text-white">Bienvenido de vuelta</h1>
                <p class="text-sm text-gray-500 mt-1">Ingresa tus credenciales para acceder</p>
            </div>

            {{-- Mensaje de estado --}}
            @if (session('status'))
            <div class="mb-5 flex items-start gap-2.5 p-3.5 bg-green-500/10 border border-green-500/30 rounded-xl text-sm text-green-400">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('status') }}
            </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">
                        Correo electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               placeholder="usuario@ovni.com" required autofocus autocomplete="username"
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl text-white placeholder-gray-600 transition
                                      bg-white/5 border focus:outline-none focus:ring-2
                                      {{ $errors->has('email') ? 'border-red-500/60 focus:ring-red-500/20' : 'border-white/10 focus:border-orange-500/60 focus:ring-orange-500/20' }}"/>
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div x-data="{ show: false }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                            Contraseña
                        </label>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-xs text-orange-400 hover:text-orange-300 transition font-medium">
                            ¿Olvidaste tu contraseña?
                        </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" :type="show ? 'text' : 'password'" name="password"
                               placeholder="••••••••" required autocomplete="current-password"
                               class="w-full pl-10 pr-11 py-2.5 text-sm rounded-xl text-white placeholder-gray-600 transition
                                      bg-white/5 border focus:outline-none focus:ring-2
                                      {{ $errors->has('password') ? 'border-red-500/60 focus:ring-red-500/20' : 'border-white/10 focus:border-orange-500/60 focus:ring-orange-500/20' }}"/>
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-600 hover:text-gray-400 transition">
                            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Recordar sesión --}}
                <div class="flex items-center gap-2.5">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="w-4 h-4 rounded border-gray-700 bg-white/5 text-orange-500 focus:ring-orange-500/30"/>
                    <label for="remember_me" class="text-sm text-gray-500 select-none cursor-pointer">
                        Mantener sesión iniciada
                    </label>
                </div>

                {{-- Botón submit --}}
                <button type="submit"
                        class="w-full py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-semibold rounded-xl transition text-sm shadow-lg shadow-orange-500/25 flex items-center justify-center gap-2">
                    Iniciar sesión
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>

            {{-- Separador --}}
            <div class="flex items-center gap-3 my-5">
                <div class="flex-1 h-px bg-white/8"></div>
                <span class="text-xs text-gray-600 font-medium">o continúa con</span>
                <div class="flex-1 h-px bg-white/8"></div>
            </div>

            {{-- Microsoft SSO --}}
            <a href="{{ route('auth.microsoft') }}"
               class="flex items-center justify-center gap-3 w-full py-2.5 px-4
                      bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20
                      text-white font-medium rounded-xl transition text-sm group">
                <svg width="18" height="18" viewBox="0 0 21 21" fill="none">
                    <rect x="1" y="1" width="9" height="9" fill="#F25022"/>
                    <rect x="11" y="1" width="9" height="9" fill="#7FBA00"/>
                    <rect x="1" y="11" width="9" height="9" fill="#00A4EF"/>
                    <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
                </svg>
                Entrar con Microsoft
            </a>

            <p class="text-center text-xs text-gray-700 mt-6">
                Solo para personal autorizado de Ovnicom
            </p>
        </div>
    </div>

</body>
</html>
