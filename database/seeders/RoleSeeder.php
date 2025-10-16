<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Role::create([
            'name' => 'Admin',
            'role_number' => 1,
            'description' => 'Administrator role with full access',
        ]);

        \App\Models\Role::create([
            'name' => 'Editor',
            'role_number' => 2,
            'description' => 'Editor role with limited access',
        ]);

        \App\Models\Role::create([
            'name' => 'Viewer',
            'role_number' => 3,
            'description' => 'Viewer role with read-only access',
        ]);
    }
}
