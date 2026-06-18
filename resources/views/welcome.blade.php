<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ovnicom Analytics Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-orb {
            0%, 100% { opacity: .20; transform: scale(1); }
            50%       { opacity: .35; transform: scale(1.05); }
        }
        @keyframes spin-slow {
            to { transform: rotate(360deg); }
        }
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }
        .animate-fade-up { animation: fadeUp .6s ease both; }
        .delay-100 { animation-delay: .10s; }
        .delay-200 { animation-delay: .20s; }
        .delay-300 { animation-delay: .30s; }
        .delay-400 { animation-delay: .40s; }
        .delay-500 { animation-delay: .50s; }
        .delay-600 { animation-delay: .60s; }
        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(90px);
            animation: pulse-orb 10s ease-in-out infinite;
            pointer-events: none;
        }
        .shimmer-text {
            background: linear-gradient(90deg, #fb923c 0%, #fbbf24 35%, #fb923c 65%, #fbbf24 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 3s linear infinite;
        }
        .card-glow:hover {
            box-shadow: 0 0 0 1px rgba(249,115,22,.3), 0 8px 32px rgba(249,115,22,.08);
        }
        .grid-bg {
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .ring-spin {
            animation: spin-slow 18s linear infinite;
        }
    </style>
</head>
<body style="background-color:#060912;" class="min-h-screen text-white overflow-x-hidden">

    {{-- ── Fondo ────────────────────────────────────────────────────────────── --}}
    <div class="fixed inset-0 grid-bg pointer-events-none"></div>
    <div class="orb w-[500px] h-[500px] top-[-150px] right-[-100px]"  style="background:rgba(249,115,22,.22); animation-delay:0s;"></div>
    <div class="orb w-[400px] h-[400px] bottom-[-100px] left-[-80px]" style="background:rgba(99,102,241,.18); animation-delay:4s;"></div>
    <div class="orb w-[300px] h-[300px] top-1/2 left-1/2"             style="background:rgba(249,115,22,.10); animation-delay:7s; transform:translate(-50%,-50%);"></div>

    {{-- ── Navbar ───────────────────────────────────────────────────────────── --}}
    <nav class="relative z-20 flex items-center justify-between px-6 sm:px-10 py-5 animate-fade-up">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/30">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-white text-sm tracking-tight">Ovnicom Analytics</span>
                <span class="hidden sm:inline ml-2 text-[10px] font-medium px-1.5 py-0.5 bg-orange-500/20 text-orange-400 rounded-md border border-orange-500/30">
                    Platform
                </span>
            </div>
        </div>

        @auth
        <a href="{{ url('/dashboard') }}"
           class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition shadow-lg shadow-orange-500/25">
            Ir al Dashboard
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
        @else
        <div class="flex items-center gap-3">
            <a href="{{ route('auth.microsoft') }}"
               class="hidden sm:flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 text-white text-sm font-medium rounded-xl transition">
                <svg width="14" height="14" viewBox="0 0 21 21" fill="none">
                    <rect x="1" y="1" width="9" height="9" fill="#F25022"/>
                    <rect x="11" y="1" width="9" height="9" fill="#7FBA00"/>
                    <rect x="1" y="11" width="9" height="9" fill="#00A4EF"/>
                    <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
                </svg>
                Microsoft
            </a>
            <a href="{{ route('login') }}"
               class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition shadow-lg shadow-orange-500/25">
                Iniciar sesión
            </a>
        </div>
        @endauth
    </nav>

    {{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
    <section class="relative z-10 flex flex-col items-center text-center px-6 pt-16 pb-20 sm:pt-24 sm:pb-28">

        {{-- Badge --}}
        <div class="animate-fade-up delay-100 inline-flex items-center gap-2 mb-6 px-3.5 py-1.5 bg-orange-500/10 border border-orange-500/25 rounded-full">
            <span class="w-1.5 h-1.5 bg-orange-400 rounded-full animate-pulse"></span>
            <span class="text-xs font-medium text-orange-300">Sistema interno · Ovnicom Communications</span>
        </div>

        {{-- Título --}}
        <h1 class="animate-fade-up delay-200 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight max-w-4xl">
            <span class="text-white">Todo lo que Ovnicom</span><br/>
            <span class="shimmer-text">necesita, en un solo lugar.</span>
        </h1>

        {{-- Subtítulo --}}
        <p class="animate-fade-up delay-300 mt-5 text-base sm:text-lg text-gray-400 max-w-xl leading-relaxed">
            Reportes MSP, inventario IT, dashboard de ventas, monitoreo de redes Cisco Meraki
            y gestión de circuitos carrier — unificados con inteligencia artificial.
        </p>

        {{-- CTAs --}}
        <div class="animate-fade-up delay-400 flex flex-col sm:flex-row items-center gap-3 mt-8">
            @auth
            <a href="{{ url('/dashboard') }}"
               class="flex items-center gap-2.5 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-2xl transition shadow-xl shadow-orange-500/25 text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Ir al Dashboard
            </a>
            @else
            <a href="{{ route('login') }}"
               class="flex items-center gap-2.5 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-2xl transition shadow-xl shadow-orange-500/25 text-sm">
                Iniciar sesión
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
            <a href="{{ route('auth.microsoft') }}"
               class="flex items-center gap-2.5 px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 text-white font-medium rounded-2xl transition text-sm">
                <svg width="16" height="16" viewBox="0 0 21 21" fill="none">
                    <rect x="1" y="1" width="9" height="9" fill="#F25022"/>
                    <rect x="11" y="1" width="9" height="9" fill="#7FBA00"/>
                    <rect x="1" y="11" width="9" height="9" fill="#00A4EF"/>
                    <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
                </svg>
                Entrar con Microsoft
            </a>
            @endauth
        </div>

        {{-- Stats --}}
        <div class="animate-fade-up delay-500 flex flex-wrap items-center justify-center gap-6 sm:gap-10 mt-12 text-center">
            @foreach([
                ['num' => '9',   'label' => 'Módulos'],
                ['num' => '10+', 'label' => 'Integraciones'],
                ['num' => 'API', 'label' => 'REST con Sanctum'],
                ['num' => '2FA', 'label' => 'Autenticación'],
            ] as $s)
            <div>
                <div class="text-2xl font-bold text-white">{{ $s['num'] }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $s['label'] }}</div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ── Módulos Grid ──────────────────────────────────────────────────────── --}}
    <section class="relative z-10 px-6 sm:px-10 pb-24 max-w-6xl mx-auto">

        <div class="text-center mb-10 animate-fade-up delay-300">
            <p class="text-xs font-semibold text-orange-400 uppercase tracking-widest mb-2">Módulos del sistema</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-white">Una plataforma, todo el negocio</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 animate-fade-up delay-400">
            @foreach([
                [
                    'icon'  => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'color' => 'bg-orange-500/15 text-orange-400 border-orange-500/20',
                    'dot'   => 'bg-orange-500',
                    'name'  => 'MSP Reports',
                    'desc'  => 'Importa tickets de SharePoint, genera PDFs individuales y masivos, envío por SendGrid.',
                    'badge' => 'Excel + PDF',
                ],
                [
                    'icon'  => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2',
                    'color' => 'bg-indigo-500/15 text-indigo-400 border-indigo-500/20',
                    'dot'   => 'bg-indigo-500',
                    'name'  => 'Dashboard Ventas',
                    'desc'  => 'KPIs desde Odoo: pipeline, ejecutivas, comisiones y reasignación de cuentas.',
                    'badge' => 'Odoo XML-RPC',
                ],
                [
                    'icon'  => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0',
                    'color' => 'bg-sky-500/15 text-sky-400 border-sky-500/20',
                    'dot'   => 'bg-sky-500',
                    'name'  => 'Meraki',
                    'desc'  => 'Monitoreo de dispositivos Cisco Meraki, licencias, alertas y estado en tiempo real.',
                    'badge' => 'Cisco API',
                ],
                [
                    'icon'  => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01',
                    'color' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                    'dot'   => 'bg-emerald-500',
                    'name'  => 'GLPI Inventario',
                    'desc'  => 'Inventario de activos IT: computadoras, switches, impresoras y más desde GLPI.',
                    'badge' => 'REST API',
                ],
                [
                    'icon'  => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
                    'color' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                    'dot'   => 'bg-amber-500',
                    'name'  => 'Control de Enlaces',
                    'desc'  => 'Circuitos carrier por país con auto-sync desde SharePoint y API REST propia.',
                    'badge' => 'SharePoint + API',
                ],
                [
                    'icon'  => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'color' => 'bg-purple-500/15 text-purple-400 border-purple-500/20',
                    'dot'   => 'bg-purple-500',
                    'name'  => 'META 2 + API MSP',
                    'desc'  => 'Metas de telefonía con streaming SSE y consulta de tickets en tiempo real.',
                    'badge' => 'SSE + Claude AI',
                ],
                [
                    'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                    'color' => 'bg-rose-500/15 text-rose-400 border-rose-500/20',
                    'dot'   => 'bg-rose-500',
                    'name'  => 'Encuestas',
                    'desc'  => 'Encuestas de satisfacción con tokens API, respuestas externas y exportación Excel.',
                    'badge' => 'API Pública',
                ],
                [
                    'icon'  => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'color' => 'bg-teal-500/15 text-teal-400 border-teal-500/20',
                    'dot'   => 'bg-teal-500',
                    'name'  => 'Usuarios y Roles',
                    'desc'  => 'CRUD de usuarios, roles con RBAC dinámico, 2FA con Google Authenticator y SSO.',
                    'badge' => '2FA + Microsoft SSO',
                ],
                [
                    'icon'  => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                    'color' => 'bg-cyan-500/15 text-cyan-400 border-cyan-500/20',
                    'dot'   => 'bg-cyan-500',
                    'name'  => 'API REST',
                    'desc'  => 'Endpoints autenticados con Sanctum para consumo externo: enlaces, tickets, reportes.',
                    'badge' => 'Bearer Token',
                ],
            ] as $mod)
            <div class="card-glow bg-white/[.03] border border-white/8 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 cursor-default">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl border flex items-center justify-center {{ $mod['color'] }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $mod['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-medium px-2 py-0.5 bg-white/5 border border-white/10 text-gray-400 rounded-full">
                        {{ $mod['badge'] }}
                    </span>
                </div>
                <h3 class="font-semibold text-white text-sm mb-1">{{ $mod['name'] }}</h3>
                <p class="text-xs text-gray-500 leading-relaxed">{{ $mod['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ── Footer ───────────────────────────────────────────────────────────── --}}
    <footer class="relative z-10 border-t border-white/5 px-6 sm:px-10 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 animate-fade-up delay-600">
        <div class="flex items-center gap-2.5">
            <div class="w-6 h-6 bg-orange-500 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <span class="text-xs text-gray-600 font-medium">Ovnicom Analytics Platform</span>
        </div>
        <p class="text-xs text-gray-700">© {{ date('Y') }} Ovnicom Communications · Sistema de uso interno</p>
        <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
            <span class="text-xs text-gray-600">Operativo</span>
        </div>
    </footer>

</body>
</html>
