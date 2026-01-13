<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $class = UserClass::firstOrCreate([
            'name' => '2AEC3',
        ]);

        User::firstOrCreate(
            ['nim' => '224443048'],
            [
                'name' => 'zena',
                'password' => Hash::make('admin'),
                'id_class' => $class->id,
                'role' => UserRole::ADMIN,
                'can_vote' => true,
                'status' => UserStatus::AKTIF,
            ]
        );
    }
}
