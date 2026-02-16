<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FamilyMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Manuel',
            'phone_number' => '849-655-6113',
        ]);

        User::create([
            'name' => 'Maria',
            'phone_number' => '809-402-2004',
        ]);
    }
}
