<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Sincronizar</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Comparación manual de clientes</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400">
                    {{ count($odooSinMatch) }} sin match en Odoo
                </span>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    {{ count($mspTodos) }} clientes MSP
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @include('admin.sincronizar.partials.nav')

            @if (session('error'))
                <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Buscadores independientes --}}
            <div class="grid grid-cols-2 gap-4">
                <input type="text" placeholder="Buscar en Odoo..."
                    class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    oninput="filtrarOdoo(this.value)">
                <input type="text" placeholder="Buscar en MSP..."
                    class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    oninput="filtrarMsp(this.value)">
            </div>

            <div class="grid grid-cols-2 gap-4 items-start">

                {{-- ODOO sin match --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-yellow-50/60 dark:bg-yellow-900/10">
                        <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-400">Odoo — sin coincidencia</span>
                        <span id="count-odoo" class="text-xs text-yellow-600 dark:text-yellow-500">{{ count($odooSinMatch) }} registros</span>
                    </div>
                    <div class="overflow-y-auto max-h-[70vh]">
                        <table class="w-full text-sm text-left">
                            <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700/90 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                <tr>
                                    <th class="px-4 py-2.5 w-10">#</th>
                                    <th class="px-4 py-2.5">Nombre Odoo</th>
                                    <th class="px-4 py-2.5">Número de cuenta</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($odooSinMatch as $i => $fila)
                                    <tr class="hover:bg-yellow-50/40 dark:hover:bg-yellow-900/10 transition fila-odoo"
                                        data-nombre="{{ strtolower($fila['odoo_nombre']) }}"
                                        data-cuenta="{{ strtolower($fila['numero_cuenta']) }}">
                                        <td class="px-4 py-2 text-gray-400 text-xs tabular-nums">{{ $i + 1 }}</td>
                                        <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $fila['odoo_nombre'] }}</td>
                                        <td class="px-4 py-2 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $fila['numero_cuenta'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400 text-sm">Sin registros</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="sin-odoo" class="hidden px-4 py-8 text-center text-gray-400 text-sm">Sin resultados.</div>
                </div>

                {{-- MSP todos --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-blue-50/60 dark:bg-blue-900/10">
                        <span class="text-sm font-semibold text-blue-700 dark:text-blue-400">MSP — todos los clientes</span>
                        <span id="count-msp" class="text-xs text-blue-600 dark:text-blue-500">{{ count($mspTodos) }} registros</span>
                    </div>
                    <div class="overflow-y-auto max-h-[70vh]">
                        <table class="w-full text-sm text-left">
                            <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700/90 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                <tr>
                                    <th class="px-4 py-2.5 w-10">#</th>
                                    <th class="px-4 py-2.5">Nombre MSP</th>
                                    <th class="px-4 py-2.5">Reference ID</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($mspTodos as $i => $cliente)
                                    <tr class="hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition fila-msp"
                                        data-nombre="{{ strtolower($cliente['CustomerName'] ?? '') }}"
                                        data-cuenta="{{ strtolower($cliente['ReferenceId'] ?? '') }}">
                                        <td class="px-4 py-2 text-gray-400 text-xs tabular-nums">{{ $i + 1 }}</td>
                                        <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $cliente['CustomerName'] ?? '—' }}</td>
                                        <td class="px-4 py-2 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $cliente['ReferenceId'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400 text-sm">Sin registros</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="sin-msp" class="hidden px-4 py-8 text-center text-gray-400 text-sm">Sin resultados.</div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function filtrarOdoo(q) {
            q = q.toLowerCase().trim();
            let vis = 0;
            document.querySelectorAll('.fila-odoo').forEach(f => {
                const ok = !q || f.dataset.nombre.includes(q) || f.dataset.cuenta.includes(q);
                f.classList.toggle('hidden', !ok);
                if (ok) vis++;
            });
            document.getElementById('sin-odoo').classList.toggle('hidden', vis > 0);
        }
        function filtrarMsp(q) {
            q = q.toLowerCase().trim();
            let vis = 0;
            document.querySelectorAll('.fila-msp').forEach(f => {
                const ok = !q || f.dataset.nombre.includes(q) || f.dataset.cuenta.includes(q);
                f.classList.toggle('hidden', !ok);
                if (ok) vis++;
            });
            document.getElementById('sin-msp').classList.toggle('hidden', vis > 0);
        }
    </script>
</x-app-layout>
