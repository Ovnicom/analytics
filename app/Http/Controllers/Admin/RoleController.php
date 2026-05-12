<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{

    public function index()
    {
        $roles   = Role::withCount('users')->orderBy('nombre')->get();
        $modulos = config('modules');
        return view('admin.roles.index', compact('roles', 'modulos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'modulos'     => 'nullable|array',
            'modulos.*'   => 'string|in:' . implode(',', array_keys(config('modules'))),
        ]);

        Role::create([
            'nombre'      => $request->nombre,
            'slug'        => Str::slug($request->nombre),
            'descripcion' => $request->descripcion,
            'modulos'     => $request->modulos ?? [],
        ]);

        return back()->with('success', '✅ Rol creado correctamente.');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'modulos'     => 'nullable|array',
            'modulos.*'   => 'string|in:' . implode(',', array_keys(config('modules'))),
        ]);

        $role->update([
            'nombre'      => $request->nombre,
            'slug'        => Str::slug($request->nombre),
            'descripcion' => $request->descripcion,
            'modulos'     => $request->modulos ?? [],
        ]);

        return back()->with('success', '✅ Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return back()->with('error', '❌ No puedes eliminar un rol con usuarios asignados.');
        }

        $role->delete();
        return back()->with('success', '✅ Rol eliminado correctamente.');
    }

    public function modulosDisponibles(): array
    {
        return config('modules');
    }
}