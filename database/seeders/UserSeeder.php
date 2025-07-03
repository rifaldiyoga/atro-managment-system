<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat pengguna admin default
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin .com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'), // Ganti dengan password yang aman
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        // Membuat 10 pengguna acak lainnya menggunakan factory
        User::factory()->count(10)->create();
    }
}
