<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Sincronizar</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Cruce de clientes Odoo vs MSP Manager</p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-cyan-50 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400">
                {{ count($filas) }} registros
            </span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('error'))
                <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow overflow-hidden">

                {{-- Toolbar --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-3">
                    <input
                        type="text"
                        placeholder="Buscar por nombre o número..."
                        class="w-full sm:w-80 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                        oninput="filtrar(this.value)"
                    >
                    <select onchange="filtrarTipo(this.value)"
                        class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">Todos los tipos</option>
                        <option value="exacto">Exacto</option>
                        <option value="fuzzy">Por nombre (~75%)</option>
                        <option value="odoo_only">Solo en Odoo</option>
                        <option value="msp_only">Solo en MSP</option>
                    </select>
                </div>

                {{-- Tabla --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 w-10">#</th>
                                <th class="px-4 py-3">Nombre Odoo</th>
                                <th class="px-4 py-3">Número de cuenta</th>
                                <th class="px-4 py-3">Nombre MSP</th>
                                <th class="px-4 py-3">Reference ID</th>
                                <th class="px-4 py-3 text-center">Similitud</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-body" class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($filas as $i => $fila)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition fila
                                    @if($fila['tipo'] === 'fuzzy')    bg-indigo-50/40 dark:bg-indigo-900/10
                                    @elseif($fila['tipo'] === 'odoo_only') bg-yellow-50/40 dark:bg-yellow-900/10
                                    @elseif($fila['tipo'] === 'msp_only')  bg-blue-50/40 dark:bg-blue-900/10
                                    @endif"
                                    data-buscar="{{ strtolower($fila['odoo_nombre'] . ' ' . $fila['msp_nombre'] . ' ' . $fila['numero_cuenta'] . ' ' . $fila['reference_id']) }}"
                                    data-tipo="{{ $fila['tipo'] }}">

                                    <td class="px-4 py-2.5 text-gray-400 dark:text-gray-500 tabular-nums text-xs">{{ $i + 1 }}</td>

                                    <td class="px-4 py-2.5 font-medium text-gray-800 dark:text-gray-100">
                                        {{ $fila['odoo_nombre'] }}
                                    </td>

                                    <td class="px-4 py-2.5 font-mono text-xs text-gray-600 dark:text-gray-300">
                                        {{ $fila['numero_cuenta'] }}
                                    </td>

                                    <td class="px-4 py-2.5 text-gray-800 dark:text-gray-100">
                                        {{ $fila['msp_nombre'] }}
                                    </td>

                                    <td class="px-4 py-2.5 font-mono text-xs text-gray-600 dark:text-gray-300">
                                        {{ $fila['reference_id'] }}
                                    </td>

                                    <td class="px-4 py-2.5 text-center">
                                        @if ($fila['similitud'] !== null)
                                            @php
                                                $sim = $fila['similitud'];
                                                $color = match(true) {
                                                    $sim >= 90 => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                    $sim >= 75 => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                                    default    => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                                };
                                            @endphp
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $color }}">
                                                {{ $sim }}%
                                            </span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="sin-resultados" class="hidden px-6 py-10 text-center text-gray-400 dark:text-gray-500 text-sm">
                    No hay resultados para tu búsqueda.
                </div>
            </div>

            {{-- Leyenda --}}
            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600"></span>
                    Coincidencia exacta (account_no = ReferenceId)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm bg-indigo-100 dark:bg-indigo-900/30 border border-indigo-300 dark:border-indigo-700"></span>
                    Coincidencia por nombre ≥ 75%
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700"></span>
                    Solo en Odoo
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm bg-blue-100 dark:bg-blue-900/30 border border-blue-300 dark:border-blue-700"></span>
                    Solo en MSP
                </span>
            </div>

        </div>
    </div>

    <script>
        let filtroTexto = '';
        let filtroTipo  = '';

        function aplicar() {
            let filas    = document.querySelectorAll('.fila');
            let visibles = 0;
            filas.forEach(f => {
                const textoOk = !filtroTexto || f.dataset.buscar.includes(filtroTexto);
                const tipoOk  = !filtroTipo  || f.dataset.tipo === filtroTipo;
                const visible = textoOk && tipoOk;
                f.classList.toggle('hidden', !visible);
                if (visible) visibles++;
            });
            document.getElementById('sin-resultados').classList.toggle('hidden', visibles > 0);
        }

        function filtrar(q)      { filtroTexto = q.toLowerCase().trim(); aplicar(); }
        function filtrarTipo(t)  { filtroTipo  = t; aplicar(); }
    </script>
</x-app-layout>
