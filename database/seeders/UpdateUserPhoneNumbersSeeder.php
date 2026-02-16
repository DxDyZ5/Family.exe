<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateUserPhoneNumbersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update Manuel's phone number: 849-655-6113 -> 18496556113
        User::where('name', 'Manuel')->update([
            'phone_number' => '18496556113'
        ]);

        // Update Maria's phone number: 809-402-2004 -> 18094022004
        User::where('name', 'Maria')->update([
            'phone_number' => '18094022004'
        ]);

        $this->command->info('Phone numbers updated to clean numeric format with country code.');
    }
}
