<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        User::firstOrCreate(
            ['email' => 'ivillarreal@ovni.com'],
            [
                'name'     => 'Irving Villarreal',
                'password' => Hash::make('Irving09*.'),
                'role_id'  => $adminRole->id,
            ]
        );
    }
}
