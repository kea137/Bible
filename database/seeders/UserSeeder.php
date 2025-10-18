<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'kea@bible.comm'],
            [
                'name' => 'Admin Kea',
                'password' => Hash::make('asdfasdf'),
            ]
        );

        // Attach admin role (role_id = 1) if not already attached
        if (! $user->roles()->where('role_number', 1)->exists()) {
            $user->roles()->attach(1);
        }
    }
}
