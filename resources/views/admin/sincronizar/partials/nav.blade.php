<div class="flex gap-1 bg-gray-100 dark:bg-gray-700 rounded-xl p-1 w-fit">
    <a href="{{ route('admin.sincronizar.index') }}"
       class="px-4 py-1.5 text-sm font-medium rounded-lg transition
              {{ request()->routeIs('admin.sincronizar.index')
                  ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
        Coincidencias
    </a>
    <a href="{{ route('admin.sincronizar.sin-coincidencia') }}"
       class="px-4 py-1.5 text-sm font-medium rounded-lg transition
              {{ request()->routeIs('admin.sincronizar.sin-coincidencia')
                  ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
        Sin coincidencia
    </a>
    <a href="{{ route('admin.sincronizar.ejecutar') }}"
       class="px-4 py-1.5 text-sm font-medium rounded-lg transition
              {{ request()->routeIs('admin.sincronizar.ejecutar')
                  ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
        Ejecutar
    </a>
</div>
