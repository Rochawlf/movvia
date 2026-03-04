<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            ['name' => 'João do Uber', 'email' => 'joao@movvia.com'],
            ['name' => 'Maria da 99', 'email' => 'maria@movvia.com'],
            ['name' => 'Carlos Condutor', 'email' => 'carlos@movvia.com'],
            ['name' => 'Ana Piloto', 'email' => 'ana@movvia.com'],
            ['name' => 'Pedro Veloz', 'email' => 'pedro@movvia.com'],
        ];

        foreach ($drivers as $driver) {
            User::create([
                'name' => $driver['name'],
                'email' => $driver['email'],
                'password' => Hash::make('password'), // Senha padrão para todos
                'role' => UserRole::Driver,
                'driver_status' => 'online',
                // Coordenadas aproximadas (ex: Camaçari/Salvador)
                'last_lat' => -12.6975 + (rand(-100, 100) / 1000), 
                'last_lng' => -38.3241 + (rand(-100, 100) / 1000),
            ]);
        }
    }
}