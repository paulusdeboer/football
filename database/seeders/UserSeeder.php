<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Helper\BaseSeeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends BaseSeeder
{
    private const USER_COUNT = 20;

    /**
     * Run the database seeds.
     */
    public function execute(): void
    {
        $lastUserId = User::max('id') ?? 0;

        for ($i = 0; $i < self::USER_COUNT; $i++) {
            $this->add([
                'id' => $lastUserId + $i + 1,
                'name' => $this->fakerService->name(),
                'email' => $this->fakerService->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'created_at' => $this->fakerService->dateTimeBetween('-1 year'),
                'updated_at' => $this->fakerService->dateTimeBetween('-1 year'),
            ], User::class);
        }
    }
}
