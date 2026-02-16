<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['phone_number' => '18496556113'],
            [
                'name' => 'Manuel',
                'telegram_id' => config('services.telegram.admin_chat_id'),
                'status' => 'active',
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['phone_number' => '18094022004'],
            [
                'name' => 'Maria',
                'status' => 'active',
                'is_admin' => false,
            ]
        );
    }
}
