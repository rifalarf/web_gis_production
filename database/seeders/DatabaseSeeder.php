<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
        ['email' => 'admin@root.com'],
        [
            'name'              => 'Admin',
            'password'          => \Illuminate\Support\Facades\Hash::make('root'),
            'email_verified_at' => now(),
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]
    );

    \App\Models\User::updateOrCreate(
        ['email' => 'admin@mimin.com'],
        [
            'name'              => 'Mimin',
            'password'          => \Illuminate\Support\Facades\Hash::make('mimin'),
            'email_verified_at' => now(),
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]
    );
    }
}

