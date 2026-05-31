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
        $gerente = Role::where('name', 'gerente')->first();
        $supervisor = Role::where('name', 'supervisor')->first();

        User::updateOrCreate(
            ['email' => 'gerente@wh3.com'],
            [
                'name' => 'Gerente General',
                'password' => Hash::make('12345678'),
                'role_id' => $gerente->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor1@wh3.com'],
            [
                'name' => 'Supervisor 1',
                'password' => Hash::make('12345678'),
                'role_id' => $supervisor->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor2@wh3.com'],
            [
                'name' => 'Supervisor 2',
                'password' => Hash::make('12345678'),
                'role_id' => $supervisor->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor3@wh3.com'],
            [
                'name' => 'Supervisor 3',
                'password' => Hash::make('12345678'),
                'role_id' => $supervisor->id,
            ]
        );
    }
}