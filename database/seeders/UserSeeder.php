<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'username' => 'admin', 'role' => 'admin', 'email' => 'admin@pemilu.id', 'password' => Hash::make('admin123')],
            ['name' => 'Operator PPK',  'username' => 'ppk',   'role' => 'ppk',  'email' => 'ppk@pemilu.id',   'password' => Hash::make('ppk123')],
            ['name' => 'Operator PPS',  'username' => 'pps',   'role' => 'pps',  'email' => 'pps@pemilu.id',   'password' => Hash::make('pps123')],
            ['name' => 'Operator KPPS', 'username' => 'kpps',  'role' => 'kpps', 'email' => 'kpps@pemilu.id',  'password' => Hash::make('kpps123')],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['username' => $u['username']], $u);
        }
    }
}
