<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">

            {{-- Left: Logo + nav links --}}
            <div class="flex items-center gap-8">

                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                    <img src="{{ asset('favicon.png') }}" alt="Ovnicom" class="h-8 w-auto">
                </a>

                {{-- Nav links --}}
                <div class="hidden sm:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}"
                       style="font-size:.82rem; font-weight:500; padding:.35rem .75rem; border-radius:.5rem; transition:all .15s;
                              color: {{ request()->routeIs('dashboard') ? 'var(--ovni-orange)' : 'var(--text-secondary)' }};
                              background: {{ request()->routeIs('dashboard') ? 'rgba(232,97,10,.09)' : 'transparent' }};"
                       onmouseenter="if(!this.getAttribute('data-active')) { this.style.background='var(--surface-hover)'; this.style.color='var(--text-primary)'; }"
                       onmouseleave="if(!this.getAttribute('data-active')) { this.style.background='transparent'; this.style.color='var(--text-secondary)'; }"
                       {{ request()->routeIs('dashboard') ? 'data-active=true' : '' }}>
                        Dashboard
                    </a>

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}"
                       style="font-size:.82rem; font-weight:500; padding:.35rem .75rem; border-radius:.5rem; transition:all .15s;
                              color: {{ request()->routeIs('admin.users.*') ? 'var(--ovni-orange)' : 'var(--text-secondary)' }};
                              background: {{ request()->routeIs('admin.users.*') ? 'rgba(232,97,10,.09)' : 'transparent' }};"
                       onmouseenter="if(!this.getAttribute('data-active')) { this.style.background='var(--surface-hover)'; this.style.color='var(--text-primary)'; }"
                       onmouseleave="if(!this.getAttribute('data-active')) { this.style.background='transparent'; this.style.color='var(--text-secondary)'; }"
                       {{ request()->routeIs('admin.users.*') ? 'data-active=true' : '' }}>
                        Usuarios
                    </a>
                    @endif

                    <a href="{{ route('admin.sincronizar.index') }}"
                       style="font-size:.82rem; font-weight:500; padding:.35rem .75rem; border-radius:.5rem; transition:all .15s;
                              color: {{ request()->routeIs('admin.sincronizar.*') ? 'var(--ovni-orange)' : 'var(--text-secondary)' }};
                              background: {{ request()->routeIs('admin.sincronizar.*') ? 'rgba(232,97,10,.09)' : 'transparent' }};"
                       onmouseenter="if(!this.getAttribute('data-active')) { this.style.background='var(--surface-hover)'; this.style.color='var(--text-primary)'; }"
                       onmouseleave="if(!this.getAttribute('data-active')) { this.style.background='transparent'; this.style.color='var(--text-secondary)'; }"
                       {{ request()->routeIs('admin.sincronizar.*') ? 'data-active=true' : '' }}>
                        Sincronizar
                    </a>
                </div>
            </div>

            {{-- Right: theme toggle + user dropdown --}}
            <div class="hidden sm:flex items-center gap-2">

                {{-- Theme toggle --}}
                <button onclick="toggleTheme()" title="Cambiar tema"
                        style="width:2.1rem;height:2.1rem;border-radius:.5rem;border:1px solid var(--surface-border);
                               background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;
                               color:var(--text-secondary);transition:all .15s;"
                        onmouseenter="this.style.background='var(--surface-hover)';this.style.color='var(--text-primary)'"
                        onmouseleave="this.style.background='transparent';this.style.color='var(--text-secondary)'">
                    {{-- Moon (show in light mode) --}}
                    <svg class="icon-moon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    {{-- Sun (show in dark mode) --}}
                    <svg class="icon-sun w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                {{-- Divider --}}
                <div style="width:1px;height:1.25rem;background:var(--surface-border)"></div>

                {{-- User dropdown --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button style="display:inline-flex;align-items:center;gap:.5rem;padding:.35rem .65rem;border-radius:.6rem;
                                       border:1px solid transparent;background:transparent;cursor:pointer;
                                       font-size:.82rem;font-weight:500;color:var(--text-secondary);transition:all .15s;"
                                onmouseenter="this.style.background='var(--surface-hover)';this.style.borderColor='var(--surface-border)';this.style.color='var(--text-primary)'"
                                onmouseleave="this.style.background='transparent';this.style.borderColor='transparent';this.style.color='var(--text-secondary)'">
                            {{-- Avatar initials --}}
                            <span style="width:1.6rem;height:1.6rem;border-radius:.4rem;
                                         background:linear-gradient(135deg,var(--ovni-orange),#f97316);
                                         display:inline-flex;align-items:center;justify-content:center;
                                         color:#fff;font-size:.62rem;font-weight:700;flex-shrink:0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </span>
                            <span>{{ Auth::user()->name }}</span>
                            <svg style="width:.875rem;height:.875rem;flex-shrink:0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- User info header --}}
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ Auth::user()->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fa-solid fa-circle-user fa-fw mr-1.5 opacity-60"></i> {{ __('Perfil') }}
                        </x-dropdown-link>

                        @if(auth()->user()->isAdmin())
                        <x-dropdown-link :href="route('admin.roles.index')">
                            <i class="fa-solid fa-shield-halved fa-fw mr-1.5 opacity-60"></i> Roles y Permisos
                        </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                style="color:#ef4444 !important">
                                <i class="fa-solid fa-arrow-right-from-bracket fa-fw mr-1.5"></i> {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Mobile hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                        style="display:inline-flex;align-items:center;justify-content:center;padding:.4rem;
                               border-radius:.5rem;color:var(--text-muted);background:transparent;border:none;cursor:pointer"
                        onmouseenter="this.style.background='var(--surface-hover)'"
                        onmouseleave="this.style.background='transparent'">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-gray-200 dark:border-gray-700">
        <div style="padding:.5rem 1rem 1rem">
            <a href="{{ route('dashboard') }}"
               style="display:block;padding:.5rem .75rem;border-radius:.5rem;font-size:.85rem;font-weight:500;
                      color:var(--text-secondary);text-decoration:none"
               onmouseenter="this.style.background='var(--surface-hover)'"
               onmouseleave="this.style.background='transparent'">
                Dashboard
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.users.index') }}"
               style="display:block;padding:.5rem .75rem;border-radius:.5rem;font-size:.85rem;font-weight:500;
                      color:var(--text-secondary);text-decoration:none"
               onmouseenter="this.style.background='var(--surface-hover)'"
               onmouseleave="this.style.background='transparent'">
                Usuarios
            </a>
            @endif
            <a href="{{ route('admin.sincronizar.index') }}"
               style="display:block;padding:.5rem .75rem;border-radius:.5rem;font-size:.85rem;font-weight:500;
                      color:var(--text-secondary);text-decoration:none"
               onmouseenter="this.style.background='var(--surface-hover)'"
               onmouseleave="this.style.background='transparent'">
                Sincronizar
            </a>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 px-4 pt-3 pb-4">
            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary)">{{ Auth::user()->name }}</div>
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:1px">{{ Auth::user()->email }}</div>

            <div style="margin-top:.75rem;display:flex;flex-direction:column;gap:.25rem">
                <a href="{{ route('profile.edit') }}"
                   style="padding:.5rem .75rem;border-radius:.5rem;font-size:.82rem;color:var(--text-secondary);
                          text-decoration:none;display:block"
                   onmouseenter="this.style.background='var(--surface-hover)'"
                   onmouseleave="this.style.background='transparent'">
                    Perfil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            style="width:100%;text-align:left;padding:.5rem .75rem;border-radius:.5rem;
                                   font-size:.82rem;color:#ef4444;background:transparent;border:none;cursor:pointer"
                            onmouseenter="this.style.background='rgba(239,68,68,.08)'"
                            onmouseleave="this.style.background='transparent'">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
