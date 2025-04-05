<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use ApproTickets\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'validator',
                'display_name' => 'Validador',
            ],
            [
                'name' => 'organizer',
                'display_name' => 'Organitzador',
            ]
        ]);
        User::create([
            'name' => 'lector',
            'email' => 'lector@entradessolsones.com',
            'password' => 'lector1234'
        ]);
        User::create([
            'name' => 'organitzador',
            'email' => 'organitzador@entradessolsones.com',
            'password' => 'lector1234'
        ]);
        User::create([
            'name' => 'client',
            'email' => 'client@gmail.com',
            'password' => 'client1234'
        ]);
        DB::table('role_user')->insert([
            [
                'user_id' => 1,
                'role_id' => 1
            ],
            [
                'user_id' => 2,
                'role_id' => 2
            ]
        ]);
        DB::table('products')->insert([
            'name' => Str::random(10),
            'title' => Str::random(10),
            'description' => Str::random(10),
            'validation_start' => 60,
            'validation_end' => 120
        ]);
        DB::table('rates')->insert([
            'title' => 'Adults',
            'order' => 0
        ]);
        DB::table('orders')->insert([
            'user_id' => 1,
            'session' => session()->getId(),
            'name' => 'test',
            'email' => 'vLq0K@example.com',
            'paid' => 1,
            'total' => 100
        ]);
        DB::table('bookings')->insert([
            'product_id' => 1,
            'order_id' => 1,
            'rate_id' => 1,
            'tickets' => 1,
            'price' => 100,
            'uid' => '1234',
            'day' => now()->toDateString(),
            'hour' => now()->addMinutes(30)->toTimeString()
        ]);
    }
}