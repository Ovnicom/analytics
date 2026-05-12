<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'odoo_user_id',
        'two_factor_secret',
        'two_factor_confirmed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'two_factor_confirmed' => 'boolean',
        ];
    }

    // ── Relación con rol dinámico ─────────────────────────────────────────
    public function roleModel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // ── Verificar acceso a módulo dinámico ───────────────────────────────
    public function canAccessModule(string $slug): bool
    {
        if ($this->isAdmin()) return true;

        $this->loadMissing('roleModel');

        return $this->roleModel?->hasModule($slug) ?? false;
    }

    // ── Métodos de rol (basados en relación dinámica) ────────────────────
    public function isAdmin(): bool
    {
        return $this->roleModel?->slug === 'admin';
    }

    public function isEditor(): bool
    {
        return $this->roleModel?->slug === 'editor';
    }

    public function isVentas(): bool
    {
        return $this->roleModel?->slug === 'ventas';
    }

    public function hasRole(string $role): bool
    {
        return $this->roleModel?->slug === $role;
    }

    // Devuelve true si el usuario es admin o es el propio vendedor (por odoo_user_id)
    public function canViewVendedorCommissions(int $odooUserId): bool
    {
        return $this->isAdmin() || (int) $this->odoo_user_id === $odooUserId;
    }

    // ── Módulos accesibles según rol dinámico ────────────────────────────
    public function modulosAccesibles(): array
    {
        $this->loadMissing('roleModel');
        return $this->roleModel?->modulos ?? [];
    }

    protected static function booted(): void
    {
        static::saving(function ($user) {
            if ($user->isDirty('role_id')) {
                if ($user->role_id) {
                    $role = \App\Models\Role::find($user->role_id);
                    $user->role = $role?->slug;
                } else {
                    $user->role = null;
                }
            }
        });
    }
}