<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '0123456789',
            'password' => bcrypt('password'),
        ]);
    }
    
}
