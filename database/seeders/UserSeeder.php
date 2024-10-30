<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'John Wick',
            'email' => 'jwick@email.com',
            'password' => Hash::make('password'),
        ])->assignRole('Admin');

        User::create([
            'name' => 'Arthur FLeck',
            'email' => 'afleck@email.com',
            'password' => Hash::make('password'),
        ])->assignRole('Collaborator');
    }
}
