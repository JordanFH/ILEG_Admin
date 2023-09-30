<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*User::create([
            'name' => "Skac Corporation",
            'email' => "skacperu@gmail.com",
            'email_verified_at'=>now(),
                'password'=>bcrypt("BARCELONAskac")
        ])->assignRole("Admin");*/

        User::create([
            'name' => "Jordan",
            'email' => "jordan@gmail.com",
            'email_verified_at'=>now(),
            'password'=>bcrypt("12345678")
        ])->assignRole("Admin");

        User::create([
            'name' => "Jhuliño",
            'email' => "jhuli@gmail.com",
            'email_verified_at'=>now(),
            'password'=>bcrypt("12345678")
        ])->assignRole("Admin");

        User::create([
            'name' => "Test User",
            'email' => "test@gmail.com",
            'email_verified_at'=>now(),
            'password'=>bcrypt("12345678")
        ])->assignRole("User");
    }
}
