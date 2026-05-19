{{-- resources/views/admin/roles/index.blade.php --}}
@extends('admin.reports.msp.layouts.app')
@section('title', 'Roles y Permisos')

@section('content')
<div class="space-y-6 fade-in">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl text-sm">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl text-sm">
        <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">Roles y Permisos</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Gestiona los roles y los módulos a los que tienen acceso</p>
        </div>
        <button onclick="openModal()"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition shadow-sm flex-shrink-0"
                style="background:var(--ovni-orange)">
            <i class="fa-solid fa-plus"></i> Nuevo rol
        </button>
    </div>

    {{-- Stats: fila 1 → 3 columnas --}}
    @php
        $totalRoles    = $roles->count();
        $totalUsuarios = $roles->sum('users_count');
        $totalModulos  = $roles->flatMap(fn($r) => $r->modulos ?? [])->unique()->count();
    @endphp

    <div class="rp-grid-3">

        <div class="rp-stat-card">
            <div class="rp-stat-icon" style="--ic-bg:rgba(232,97,10,.12); --ic-bg-dark:rgba(232,97,10,.2)">
                <i class="fa-solid fa-shield-halved" style="color:#ea7c2f;font-size:.95rem"></i>
            </div>
            <div>
                <p class="rp-stat-num">{{ $totalRoles }}</p>
                <p class="rp-stat-label">Roles creados</p>
            </div>
        </div>

        <div class="rp-stat-card">
            <div class="rp-stat-icon" style="--ic-bg:rgba(59,130,246,.1); --ic-bg-dark:rgba(59,130,246,.2)">
                <i class="fa-solid fa-users" style="color:#3b82f6;font-size:.95rem"></i>
            </div>
            <div>
                <p class="rp-stat-num">{{ $totalUsuarios }}</p>
                <p class="rp-stat-label">Usuarios asignados</p>
            </div>
        </div>

        <div class="rp-stat-card">
            <div class="rp-stat-icon" style="--ic-bg:rgba(168,85,247,.1); --ic-bg-dark:rgba(168,85,247,.2)">
                <i class="fa-solid fa-puzzle-piece" style="color:#a855f7;font-size:.95rem"></i>
            </div>
            <div>
                <p class="rp-stat-num">{{ $totalModulos }}</p>
                <p class="rp-stat-label">Módulos activos</p>
            </div>
        </div>

    </div>

    {{-- Roles: fila 2 → 3 columnas --}}

    <div class="rp-grid-3" style="align-items:start">

        @forelse($roles as $role)
        @php
            $roleModulos = $role->modulos ?? [];
            $maxVisible  = 3;
            $visible     = array_slice($roleModulos, 0, $maxVisible);
            $restantes   = count($roleModulos) - $maxVisible;
            $initials    = strtoupper(substr($role->nombre, 0, 2));
            $pct         = count($roleModulos) > 0 ? (count($roleModulos) / count($modulos)) * 100 : 0;
            $canDelete   = $role->users_count === 0;
        @endphp

        <div class="rp-role-card">

            {{-- Top --}}
            <div class="rp-role-top">
                <div class="rp-avatar">{{ $initials }}</div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap">
                        <span class="rp-role-name">{{ $role->nombre }}</span>
                        <span class="rp-slug">{{ $role->slug }}</span>
                    </div>
                    @if($role->descripcion)
                    <p class="rp-desc">{{ $role->descripcion }}</p>
                    @endif
                </div>
            </div>

            {{-- Chips módulos --}}
            <div class="rp-chips">
                @forelse($visible as $modSlug)
                    @if(isset($modulos[$modSlug]))
                    @php $mc = $modulos[$modSlug]; @endphp
                    <span class="rp-chip"
                          style="--chip-light-color:{{ $mc['light_color'] }};--chip-light-bg:{{ $mc['light_bg'] }};
                                 --chip-dark-color:{{ $mc['dark_color'] }};--chip-dark-bg:{{ $mc['dark_bg'] }}">
                        <i class="fa-solid {{ $mc['icon'] }}" style="font-size:.58rem"></i>
                        {{ $mc['nombre'] }}
                    </span>
                    @endif
                @empty
                    <span class="rp-no-modules">Sin módulos</span>
                @endforelse
                @if($restantes > 0)
                    <span class="rp-chip-more">+{{ $restantes }}</span>
                @endif
            </div>

            {{-- Barra progreso --}}
            <div class="rp-progress-track">
                <div class="rp-progress-bar" style="width:{{ $pct }}%"></div>
            </div>

            {{-- Footer --}}
            <div class="rp-role-footer">
                <div class="rp-user-count">
                    <i class="fa-solid fa-user" style="font-size:.62rem"></i>
                    <strong>{{ $role->users_count }}</strong>
                    <span>{{ $role->users_count === 1 ? 'usuario' : 'usuarios' }}</span>
                </div>
                <div style="display:flex;gap:.4rem">
                    <button class="rp-btn-edit" onclick="openModal({{ $role->id }})" title="Editar">
                        <i class="fa-solid fa-pen-to-square" style="font-size:.65rem"></i> Editar
                    </button>
                    <button class="rp-btn-delete {{ $canDelete ? '' : 'rp-btn-disabled' }}"
                            onclick="{{ $canDelete ? 'openDeleteModal('.$role->id.',\''.addslashes($role->nombre).'\',0)' : '' }}"
                            title="{{ $canDelete ? 'Eliminar' : 'Tiene usuarios asignados' }}"
                            {{ !$canDelete ? 'disabled' : '' }}>
                        <i class="fa-solid fa-trash-can" style="font-size:.65rem"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>

        @empty
        <div style="grid-column:1/-1" class="rp-empty">
            <i class="fa-solid fa-shield-halved" style="font-size:2.5rem"></i>
            <p style="font-size:.85rem;font-weight:600;margin-top:.75rem">No hay roles creados</p>
            <p style="font-size:.75rem;margin-top:.2rem">Crea tu primer rol para gestionar permisos</p>
            <button onclick="openModal()" class="rp-btn-new" style="margin-top:1rem">
                <i class="fa-solid fa-plus"></i> Crear primer rol
            </button>
        </div>
        @endforelse

    </div>

</div>

{{-- ══════════════════════════════════════════════
     MODAL CREAR / EDITAR ROL
══════════════════════════════════════════════ --}}
<div id="modal-rol" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg animate-modal">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm shadow-sm"
                     style="background: linear-gradient(135deg, var(--ovni-orange), #f97316)">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm" id="modal-title">Crear nuevo rol</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500" id="modal-subtitle">Define nombre, descripción y módulos</p>
                </div>
            </div>
            <button onclick="closeModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Form --}}
        <form id="form-rol" action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="role_id" id="form-role-id" value="">

            <div class="px-6 py-5 space-y-5 max-h-[65vh] overflow-y-auto">

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5 block">
                            Nombre del rol <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="nombre" id="input-nombre" required
                               placeholder="Ej: Supervisor, Analista, Editor…"
                               class="w-full border dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm
                                      bg-gray-50 dark:bg-gray-700/50 dark:text-white
                                      focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent
                                      placeholder-gray-300 dark:placeholder-gray-500 transition">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5 block">
                            Descripción <span class="text-gray-300 font-normal normal-case">(opcional)</span>
                        </label>
                        <input type="text" name="descripcion" id="input-descripcion"
                               placeholder="Describe brevemente este rol…"
                               class="w-full border dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm
                                      bg-gray-50 dark:bg-gray-700/50 dark:text-white
                                      focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent
                                      placeholder-gray-300 dark:placeholder-gray-500 transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Módulos con acceso
                        </label>
                        <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full" id="count-modulos">
                            0 seleccionados
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2" id="modulos-grid">
                        @foreach($modulos as $slug => $mod)
                        <label class="modulo-card relative flex items-center gap-3 p-3 border-2 border-gray-100 dark:border-gray-700 rounded-xl cursor-pointer
                                      hover:border-gray-200 dark:hover:border-gray-600 transition-all duration-150 select-none
                                      bg-white dark:bg-gray-800">
                            <input type="checkbox" name="modulos[]" value="{{ $slug }}"
                                   class="modulo-check sr-only"
                                   onchange="updateCardStyle(this)">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
                                 style="background: {{ $mod['bg'] }}">
                                <i class="fa-solid {{ $mod['icon'] }} text-sm" style="color: {{ $mod['color'] }}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="block text-xs font-semibold text-gray-800 dark:text-white">{{ $mod['nombre'] }}</span>
                                <span class="block text-xs text-gray-400 dark:text-gray-500 truncate">{{ $mod['descripcion'] }}</span>
                            </div>
                            <div class="modulo-indicator w-5 h-5 rounded-full border-2 border-gray-200 dark:border-gray-600
                                        flex items-center justify-center flex-shrink-0 transition-all duration-150">
                                <i class="fa-solid fa-check text-[9px] text-white hidden check-icon"></i>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t dark:border-gray-700 flex justify-between items-center">
                <button type="button" onclick="closeModal()"
                        class="px-4 py-2 rounded-xl border dark:border-gray-600 text-sm font-medium text-gray-600 dark:text-gray-300
                               hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition shadow-sm"
                        style="background: linear-gradient(135deg, var(--ovni-orange), #f97316)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span id="btn-submit-text">Crear rol</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     MODAL CONFIRMAR ELIMINACIÓN
══════════════════════════════════════════════ --}}
<div id="modal-delete" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm animate-modal">

        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash-can text-2xl text-red-500"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white mb-1">¿Eliminar este rol?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Vas a eliminar el rol</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-4" id="delete-role-name">—</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 rounded-xl px-4 py-2.5">
                Esta acción no se puede deshacer.
            </p>
        </div>

        <div class="flex gap-3 px-6 pb-6">
            <button onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2.5 rounded-xl border dark:border-gray-600 text-sm font-semibold text-gray-600 dark:text-gray-300
                           hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Cancelar
            </button>
            <form id="form-delete" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition shadow-sm">
                    <i class="fa-solid fa-trash-can mr-1.5"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
/* ── Animations ── */
@keyframes modalIn {
    from { opacity:0; transform:scale(.95) translateY(8px); }
    to   { opacity:1; transform:scale(1)   translateY(0);   }
}
.animate-modal { animation: modalIn .18s ease-out; }

/* ── Grid helper ── */
.rp-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}

/* ══ STAT CARDS ══ */
.rp-stat-card {
    display: flex; align-items: center; gap: 1rem;
    padding: 1.1rem 1.25rem;
    border-radius: 1rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.rp-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.dark .rp-stat-card {
    background: #1e2535;
    border-color: #2d3748;
    box-shadow: none;
}
.rp-stat-icon {
    width: 2.5rem; height: 2.5rem; border-radius: .625rem; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--ic-bg);
}
.dark .rp-stat-icon { background: var(--ic-bg-dark); }
.rp-stat-num {
    font-size: 1.6rem; font-weight: 800; line-height: 1;
    color: #111827;
}
.dark .rp-stat-num { color: #f1f5f9; }
.rp-stat-label {
    font-size: .72rem; margin-top: 2px;
    color: #6b7280;
}
.dark .rp-stat-label { color: #94a3b8; }

/* ══ ROLE CARDS ══ */
.rp-role-card {
    border-radius: 1rem; overflow: hidden;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.rp-role-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.1); }
.dark .rp-role-card {
    background: #1e2535;
    border-color: #2d3748;
    box-shadow: none;
}
.dark .rp-role-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.4); }

/* Top section */
.rp-role-top {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: 1.1rem 1.25rem .8rem;
}
.rp-avatar {
    width: 2.5rem; height: 2.5rem; border-radius: .625rem; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .8rem; font-weight: 800;
    background: linear-gradient(135deg, var(--ovni-orange), #f97316);
    box-shadow: 0 2px 8px rgba(232,97,10,.3);
}
.rp-role-name { font-size: .85rem; font-weight: 700; color: #111827; }
.dark .rp-role-name { color: #f1f5f9; }
.rp-slug {
    font-size: .65rem; font-family: monospace;
    padding: 1px 7px; border-radius: 999px;
    background: #f1f5f9; color: #6b7280;
}
.dark .rp-slug { background: #263045; color: #94a3b8; }
.rp-desc {
    font-size: .7rem; margin-top: 2px;
    color: #9ca3af;
    overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
}
.dark .rp-desc { color: #64748b; }

/* Chips */
.rp-chips {
    padding: 0 1.25rem .85rem;
    display: flex; flex-wrap: wrap; gap: .35rem; min-height: 2rem;
}
.rp-chip {
    display: inline-flex; align-items: center; gap: .25rem;
    font-size: .65rem; font-weight: 600;
    padding: 2px 8px; border-radius: .4rem;
    background: var(--chip-light-bg);
    color: var(--chip-light-color);
}
.dark .rp-chip {
    background: var(--chip-dark-bg);
    color: var(--chip-dark-color);
}
.rp-chip-more {
    font-size: .65rem; font-weight: 600;
    padding: 2px 8px; border-radius: .4rem;
    background: #f1f5f9; color: #6b7280;
}
.dark .rp-chip-more { background: #263045; color: #64748b; }
.rp-no-modules { font-size: .7rem; font-style: italic; color: #9ca3af; }
.dark .rp-no-modules { color: #4b5563; }

/* Progress bar */
.rp-progress-track { height: 3px; background: #f1f5f9; }
.dark .rp-progress-track { background: #263045; }
.rp-progress-bar { height: 3px; background: var(--ovni-orange); transition: width .3s; }

/* Footer */
.rp-role-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: .75rem 1.25rem;
    border-top: 1px solid #f1f5f9;
}
.dark .rp-role-footer { border-top-color: #263045; }
.rp-user-count {
    display: flex; align-items: center; gap: .35rem;
    font-size: .72rem;
    color: #9ca3af;
}
.dark .rp-user-count { color: #475569; }
.rp-user-count strong { font-weight: 700; color: #374151; }
.dark .rp-user-count strong { color: #cbd5e1; }

/* Buttons */
.rp-btn-edit {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .7rem; border-radius: .5rem;
    font-size: .72rem; font-weight: 600; cursor: pointer;
    background: #f8fafc; color: #374151;
    border: 1px solid #d1d5db;
    transition: all .15s;
}
.rp-btn-edit:hover {
    background: #fff7ed; color: #ea580c;
    border-color: #fed7aa;
}
.dark .rp-btn-edit {
    background: #263045; color: #cbd5e1;
    border-color: #334155;
}
.dark .rp-btn-edit:hover {
    background: rgba(232,97,10,.15); color: #fb923c;
    border-color: rgba(232,97,10,.35);
}
.rp-btn-delete {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .7rem; border-radius: .5rem;
    font-size: .72rem; font-weight: 600; cursor: pointer;
    background: #fff1f2; color: #e11d48;
    border: 1px solid #fecdd3;
    transition: all .15s;
}
.rp-btn-delete:hover {
    background: #e11d48; color: #fff;
    border-color: #e11d48;
}
.dark .rp-btn-delete {
    background: rgba(239,68,68,.08); color: #f87171;
    border-color: rgba(239,68,68,.2);
}
.dark .rp-btn-delete:hover {
    background: rgba(239,68,68,.25);
    border-color: rgba(239,68,68,.5);
}
.rp-btn-disabled {
    opacity: .35; cursor: not-allowed !important;
    pointer-events: none;
}

/* Nuevo rol button header */
.rp-btn-new {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1rem; border-radius: .625rem;
    background: var(--ovni-orange); color: #fff;
    font-size: .8rem; font-weight: 600; border: none; cursor: pointer;
    transition: opacity .15s;
}
.rp-btn-new:hover { opacity: .88; }

/* Empty state */
.rp-empty {
    padding: 4rem; text-align: center;
    background: #ffffff; border: 1px solid #e2e8f0;
    border-radius: 1rem; color: #9ca3af;
}
.dark .rp-empty { background: #1e2535; border-color: #2d3748; color: #4b5563; }
</style>

<script>
const ROLES_DATA = @json($roles->keyBy('id'));

/* ── Modal crear/editar ── */
function openModal(roleId = null) {
    const modal    = document.getElementById('modal-rol');
    const title    = document.getElementById('modal-title');
    const subtitle = document.getElementById('modal-subtitle');
    const btnText  = document.getElementById('btn-submit-text');
    const form     = document.getElementById('form-rol');

    document.getElementById('input-nombre').value      = '';
    document.getElementById('input-descripcion').value = '';
    document.querySelectorAll('.modulo-check').forEach(cb => {
        cb.checked = false;
        updateCardStyle(cb);
    });

    if (roleId) {
        const role = ROLES_DATA[roleId];
        if (!role) return;

        title.textContent    = 'Editar rol';
        subtitle.textContent = 'Modifica nombre, descripción o módulos';
        btnText.textContent  = 'Guardar cambios';
        form.action          = `/admin/roles/${roleId}`;
        document.getElementById('form-method').value   = 'PUT';
        document.getElementById('form-role-id').value  = roleId;

        document.getElementById('input-nombre').value      = role.nombre;
        document.getElementById('input-descripcion').value = role.descripcion ?? '';

        const modulos = role.modulos ?? [];
        document.querySelectorAll('.modulo-check').forEach(cb => {
            cb.checked = modulos.includes(cb.value);
            updateCardStyle(cb);
        });
    } else {
        title.textContent    = 'Crear nuevo rol';
        subtitle.textContent = 'Define nombre, descripción y módulos';
        btnText.textContent  = 'Crear rol';
        form.action          = '{{ route('admin.roles.store') }}';
        document.getElementById('form-method').value   = 'POST';
        document.getElementById('form-role-id').value  = '';
    }

    updateCount();
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-rol').classList.add('hidden');
}

/* ── Modal eliminar ── */
function openDeleteModal(roleId, roleName, usersCount) {
    if (usersCount > 0) return;

    document.getElementById('delete-role-name').textContent = roleName;
    document.getElementById('form-delete').action = `/admin/roles/${roleId}`;

    document.getElementById('modal-delete').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('modal-delete').classList.add('hidden');
}

/* ── Estilos de módulo-card ── */
function updateCardStyle(checkbox) {
    const card      = checkbox.closest('.modulo-card');
    const indicator = card.querySelector('.modulo-indicator');
    const icon      = card.querySelector('.check-icon');

    if (checkbox.checked) {
        card.style.borderColor     = 'var(--ovni-orange, #e8610a)';
        card.style.backgroundColor = '#fff8f4';
        indicator.style.background  = '#e8610a';
        indicator.style.borderColor = '#e8610a';
        icon.classList.remove('hidden');
    } else {
        card.style.borderColor     = '';
        card.style.backgroundColor = '';
        indicator.style.background  = '';
        indicator.style.borderColor = '';
        icon.classList.add('hidden');
    }

    updateCount();
}

function updateCount() {
    const count = document.querySelectorAll('.modulo-check:checked').length;
    const el    = document.getElementById('count-modulos');
    if (el) el.textContent = count + (count === 1 ? ' seleccionado' : ' seleccionados');
}

/* ── Cerrar modales al click fuera ── */
['modal-rol', 'modal-delete'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) {
            id === 'modal-rol' ? closeModal() : closeDeleteModal();
        }
    });
});
</script>
@endpush
