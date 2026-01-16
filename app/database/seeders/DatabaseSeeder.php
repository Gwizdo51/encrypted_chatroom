<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Alice',
            'email' => 'alice@mail.local',
            'password' => Hash::make('alice123'),
        ]);

        User::create([
            'name' => 'Bob',
            'email' => 'bob@mail.local',
            'password' => Hash::make('bob123'),
        ]);
    }
}
